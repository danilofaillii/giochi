# Giochi

Remake di classici arcade in HTML e JavaScript puro. Ogni gioco è un singolo
file `index.html` nella sua cartella: nessun framework, nessuna dipendenza,
nessun build step. Si apre nel browser e si gioca.

| Gioco | Cartella | Stato |
|---|---|---|
| QIX (Taito, 1981) | `qix/` | giocabile, in sviluppo sul ramo `qix` |
| Gorillas (Microsoft, QBasic, 1991) | `gorilla/` | giocabile contro il computer, in due, o online (lobby con partite in attesa), con le musichette PLAY originali in Web Audio, sul ramo `Gorilla` |

## Gorillas online

La partita online è un seme più la lista dei tiri: ogni browser ricostruisce la
stessa città (collisioni su una bitmap, non sui pixel del canvas) e arriva allo
stesso esito da solo. Il server conserva e distribuisce, non arbitra. La pagina
prova due trasporti, nell'ordine, e se nessuno risponde spiega perché l'online è
spento e offre computer e due giocatori sullo stesso schermo:

1. **claude.ai**: aperta dal link pubblicato come Artifact usa lo store condiviso
   del runtime (`claude.use('db')`). Gioca chi è invitato almeno come Contributor.
2. **Hosting proprio (PHP + MySQL)**: `gorilla/api.php` interrogato ogni secondo
   e mezzo con un numero di versione. Va bene un hosting condiviso qualsiasi.

### Mettere Gorillas su Aruba (hosting condiviso Linux con MySQL)

1. Nel pannello Aruba crea un database MySQL (sezione *Database MySQL*). Segna
   host, nome del database, utente e password.
2. Copia `gorilla/config.sample.php` in `gorilla/config.php` e inserisci quei dati
   in `$DB_DSN`, `$DB_USER`, `$DB_PASS`. Il file è nel `.gitignore`: non finisce nel
   repository.
3. Carica via FTP la cartella `gorilla/` con dentro `index.html`, `api.php` e
   `config.php` (per esempio in `www.tuodominio.it/gorilla/`).
4. Apri `https://www.tuodominio.it/gorilla/api.php?a=ping`: deve rispondere
   `{"ok":true,"store":"mysql"}`. La tabella `gorilla_games` si crea da sola alla
   prima chiamata.
5. Apri `index.html`: la voce *Online* è attiva e sotto il form compare la lista
   delle partite in attesa.

Se `ping` risponde `no_config` manca `config.php`; se risponde `db_connect` i dati
in `config.php` non sono giusti. Richiede PHP 8.0 o successivo con estensione
`pdo_mysql` (su Aruba si sceglie la versione di PHP dal pannello). Le partite in
attesa da più di due ore e quelle finite da più di un giorno si cancellano da sole.
