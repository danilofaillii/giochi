<?php
/*
 * Gorillas: API delle partite online per hosting condiviso (PHP + MySQL).
 *
 * Niente WebSocket, niente processi in ascolto: il browser chiede ogni secondo e mezzo
 * "è cambiato qualcosa dalla versione N?" e il server risponde con la partita intera
 * solo quando serve. Una partita è un seme più la lista dei tiri: il server non simula
 * nulla, conserva e distribuisce.
 *
 * Azioni (parametro a=):
 *   GET  ping                      → {ok, store}
 *   GET  list&me=ID                → {games:[...]} partite in attesa
 *   POST create {me,name,seed,ptw,gravity,difficulty} → {game}
 *   POST join   {me,name,id}       → {game}   (409 taken se qualcuno è entrato prima)
 *   GET  get&id=&me=&v=            → {game} oppure {unchanged:true, v}
 *   POST shot   {me,id,n,a,v}      → {game}   (409 stale se n non è il prossimo tiro)
 *   POST leave  {me,id}            → {ok}     (in attesa: cancella; in corso: abbandono)
 *   POST finish {me,id,winner}     → {ok}     (solo l'host, chiude la partita)
 *
 * Gli id dei giocatori sono token casuali che restano nel browser: non si mostrano mai
 * agli altri, fanno da "password" del posto in partita.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$cfg = __DIR__ . '/config.php';
if (!is_file($cfg)) {
    fail(500, 'no_config', 'Manca config.php: copia config.sample.php e inserisci i dati del database.');
}
require $cfg;

if (!isset($DB_DSN)) {
    fail(500, 'bad_config', 'config.php non definisce $DB_DSN.');
}

try {
    $pdo = new PDO($DB_DSN, $DB_USER ?? null, $DB_PASS ?? null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (Throwable $e) {
    fail(500, 'db_connect', 'Connessione al database fallita.');
}

$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
ensureSchema($pdo, $driver);

$action = $_GET['a'] ?? '';
$body = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $body = json_decode($raw ?: '[]', true);
    if (!is_array($body)) $body = [];
}
$me = cleanId($body['me'] ?? ($_GET['me'] ?? ''));

try {
    switch ($action) {
        case 'ping':
            out(['ok' => true, 'store' => $driver]);

        case 'list':
            cleanup($pdo);
            $st = $pdo->prepare('SELECT * FROM gorilla_games WHERE status = ? ORDER BY created_at DESC LIMIT 30');
            $st->execute(['waiting']);
            $games = [];
            foreach ($st->fetchAll() as $row) $games[] = publicGame($row, $me);
            out(['games' => $games]);

        case 'create':
            needPost();
            if ($me === '') fail(400, 'bad_request', 'Manca me.');
            cleanup($pdo);
            // Una sola partita in attesa per giocatore: se ne apre un'altra, la precedente sparisce.
            $pdo->prepare('DELETE FROM gorilla_games WHERE status = ? AND host_id = ?')->execute(['waiting', $me]);
            $id = newId();
            $now = time();
            $st = $pdo->prepare('INSERT INTO gorilla_games
                (id, status, host_id, host_name, guest_id, guest_name, seed, ptw, gravity, difficulty, shots, winner, left_by, version, created_at, updated_at)
                VALUES (?, ?, ?, ?, NULL, NULL, ?, ?, ?, ?, ?, NULL, NULL, 1, ?, ?)');
            $st->execute([
                $id, 'waiting', $me, cleanName($body['name'] ?? ''),
                (int)($body['seed'] ?? 0) & 0xFFFFFFFF,
                max(1, min(9, (int)($body['ptw'] ?? 3))),
                max(0.1, min(100, (float)($body['gravity'] ?? 9.8))),
                in_array($body['difficulty'] ?? '', ['facile', 'normale', 'difficile'], true) ? $body['difficulty'] : 'normale',
                '[]', $now, $now,
            ]);
            out(['game' => publicGame(loadGame($pdo, $id), $me)]);

        case 'join':
            needPost();
            $id = cleanId($body['id'] ?? '');
            if ($me === '' || $id === '') fail(400, 'bad_request', 'Mancano me o id.');
            // L'UPDATE condizionato è atomico: se due entrano insieme, uno solo trova la riga ancora libera.
            $st = $pdo->prepare('UPDATE gorilla_games SET guest_id = ?, guest_name = ?, status = ?, version = version + 1, updated_at = ?
                WHERE id = ? AND status = ? AND guest_id IS NULL AND host_id <> ?');
            $st->execute([$me, cleanName($body['name'] ?? ''), 'playing', time(), $id, 'waiting', $me]);
            if ($st->rowCount() !== 1) {
                $row = loadGame($pdo, $id);
                if (!$row) fail(404, 'gone', 'Quella partita non c\'è più.');
                if ($row['host_id'] === $me) fail(409, 'own', 'È la tua partita: non puoi sfidare te stesso.');
                fail(409, 'taken', 'Troppo tardi: qualcuno è entrato prima di te.');
            }
            out(['game' => publicGame(loadGame($pdo, $id), $me)]);

        case 'get':
            $id = cleanId($_GET['id'] ?? '');
            $row = loadGame($pdo, $id);
            if (!$row) fail(404, 'gone', 'Partita non trovata.');
            $v = (int)($_GET['v'] ?? 0);
            if ($v > 0 && (int)$row['version'] === $v) out(['unchanged' => true, 'v' => $v]);
            out(['game' => publicGame($row, $me)]);

        case 'shot':
            needPost();
            $id = cleanId($body['id'] ?? '');
            $row = loadGame($pdo, $id);
            if (!$row) fail(404, 'gone', 'Partita non trovata.');
            if ($row['status'] !== 'playing') fail(409, 'not_playing', 'La partita non è in corso.');
            if ($me === '' || ($row['host_id'] !== $me && $row['guest_id'] !== $me)) fail(403, 'not_player', 'Non sei in questa partita.');
            $shots = json_decode($row['shots'], true) ?: [];
            $n = (int)($body['n'] ?? -1);
            if ($n !== count($shots)) fail(409, 'stale', 'Il tiro non è il prossimo: ricarico lo stato.');
            $a = max(0, min(90, (int)($body['a'] ?? -1)));
            $vel = max(1, min(200, (int)($body['v'] ?? 0)));
            if (count($shots) >= 2000) fail(409, 'too_long', 'Partita troppo lunga.');
            $shots[] = [$a, $vel];
            $st = $pdo->prepare('UPDATE gorilla_games SET shots = ?, version = version + 1, updated_at = ? WHERE id = ? AND version = ?');
            $st->execute([json_encode($shots), time(), $id, (int)$row['version']]);
            if ($st->rowCount() !== 1) fail(409, 'stale', 'Qualcuno ha scritto prima: ricarico lo stato.');
            out(['game' => publicGame(loadGame($pdo, $id), $me)]);

        case 'leave':
            needPost();
            $id = cleanId($body['id'] ?? '');
            $row = loadGame($pdo, $id);
            if (!$row) out(['ok' => true]);
            $role = $row['host_id'] === $me ? 'host' : ($row['guest_id'] === $me ? 'guest' : null);
            if ($role === null) fail(403, 'not_player', 'Non sei in questa partita.');
            if ($row['status'] === 'waiting') {
                $pdo->prepare('DELETE FROM gorilla_games WHERE id = ?')->execute([$id]);
            } elseif ($row['status'] === 'playing') {
                $pdo->prepare('UPDATE gorilla_games SET status = ?, left_by = ?, winner = ?, version = version + 1, updated_at = ? WHERE id = ? AND status = ?')
                    ->execute(['done', $role, $role === 'host' ? 1 : 0, time(), $id, 'playing']);
            }
            out(['ok' => true]);

        case 'finish':
            needPost();
            $id = cleanId($body['id'] ?? '');
            $row = loadGame($pdo, $id);
            if (!$row) out(['ok' => true]);
            if ($row['host_id'] !== $me) fail(403, 'not_host', 'Solo chi ha aperto la partita la chiude.');
            $w = (int)($body['winner'] ?? 0) === 1 ? 1 : 0;
            $pdo->prepare('UPDATE gorilla_games SET status = ?, winner = ?, version = version + 1, updated_at = ? WHERE id = ? AND status = ?')
                ->execute(['done', $w, time(), $id, 'playing']);
            out(['ok' => true]);

        default:
            fail(404, 'no_action', 'Azione sconosciuta.');
    }
} catch (PDOException $e) {
    fail(500, 'db_error', 'Errore del database.');
}

/* ---------- Funzioni ---------- */

function out(array $data): void {
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function fail(int $status, string $code, string $message): void {
    http_response_code($status);
    echo json_encode(['error' => $code, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

function needPost(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail(405, 'method', 'Serve una POST.');
}

function cleanId(mixed $s): string {
    $s = is_string($s) ? $s : '';
    return preg_match('/^[A-Za-z0-9_\-]{4,40}$/', $s) ? $s : '';
}

function cleanName(mixed $s): string {
    $s = is_string($s) ? trim($s) : '';
    $s = preg_replace('/[\x00-\x1F\x7F]/u', '', $s) ?? '';
    if (function_exists('mb_substr')) $s = mb_substr($s, 0, 12);
    else $s = substr($s, 0, 12);
    return $s === '' ? 'Giocatore' : $s;
}

function newId(): string {
    return substr(strtr(base64_encode(random_bytes(9)), '+/', '-_'), 0, 12);
}

function loadGame(PDO $pdo, string $id): ?array {
    if ($id === '') return null;
    $st = $pdo->prepare('SELECT * FROM gorilla_games WHERE id = ?');
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
}

// Quello che il browser vede. Gli id dei giocatori non escono mai: solo "role" per chi chiede.
function publicGame(array $r, string $me): array {
    return [
        'id' => $r['id'],
        'status' => $r['status'],
        'hostName' => $r['host_name'],
        'guestName' => $r['guest_name'],
        'role' => $r['host_id'] === $me ? 'host' : ($r['guest_id'] !== null && $r['guest_id'] === $me ? 'guest' : null),
        'seed' => (int)$r['seed'],
        'ptw' => (int)$r['ptw'],
        'gravity' => (float)$r['gravity'],
        'difficulty' => $r['difficulty'],
        'shots' => json_decode($r['shots'], true) ?: [],
        'winner' => $r['winner'] === null ? null : (int)$r['winner'],
        'left' => $r['left_by'],
        'v' => (int)$r['version'],
        'created' => gmdate('c', (int)$r['created_at']),
    ];
}

// Partite in attesa da più di due ore e partite vecchie di un giorno: via.
function cleanup(PDO $pdo): void {
    static $done = false;
    if ($done) return;
    $done = true;
    $now = time();
    $pdo->prepare('DELETE FROM gorilla_games WHERE status = ? AND created_at < ?')->execute(['waiting', $now - 2 * 3600]);
    $pdo->prepare('DELETE FROM gorilla_games WHERE status <> ? AND updated_at < ?')->execute(['waiting', $now - 24 * 3600]);
}

function ensureSchema(PDO $pdo, string $driver): void {
    $table = 'CREATE TABLE IF NOT EXISTS gorilla_games (
        id VARCHAR(16) NOT NULL PRIMARY KEY,
        status VARCHAR(10) NOT NULL,
        host_id VARCHAR(40) NOT NULL,
        host_name VARCHAR(20) NOT NULL,
        guest_id VARCHAR(40) NULL,
        guest_name VARCHAR(20) NULL,
        seed BIGINT NOT NULL,
        ptw INT NOT NULL,
        gravity DOUBLE NOT NULL,
        difficulty VARCHAR(10) NOT NULL,
        shots TEXT NOT NULL,
        winner INT NULL,
        left_by VARCHAR(5) NULL,
        version INT NOT NULL DEFAULT 1,
        created_at INT NOT NULL,
        updated_at INT NOT NULL';
    if ($driver === 'mysql') {
        $table .= ', KEY idx_status_created (status, created_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
        $pdo->exec($table);
    } else {
        $pdo->exec($table . ')');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_status_created ON gorilla_games (status, created_at)');
    }
}
