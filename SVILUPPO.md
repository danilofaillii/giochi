# Sviluppo Giochi

Quaderno di lavoro del ramo `sviluppo-giochi`. Qui si decide cosa fare, prima di aprire
un ramo per ogni gioco.

Regole della casa (dal README): un solo `index.html` per gioco, JavaScript puro,
niente framework, niente build. Audio sintetizzato in Web Audio, record salvati in
`localStorage`.

## Cosa esiste già

| Gioco | Ramo | Righe | Note |
|---|---|---|---|
| QIX (Taito, 1981) | `qix` | ~750 | timer, Super Sparx, audio sintetizzato |
| Gorillas (QBasic, 1991) | `Gorilla` | ~630 | musichette PLAY rifatte in Web Audio |
| Campo minato | `campo-minato` | ~550 | prima versione giocabile |

Nessuno dei tre è ancora unito su `main`: il README di `main` elenca solo QIX.

## Candidati

Ogni riga: cosa lo rende interessante, quanto costa, cosa ha di nuovo rispetto a quelli fatti.

### Fascia 1: un pomeriggio, alto ritorno

| Gioco | Perché | Novità tecnica |
|---|---|---|
| **Snake** (Nokia, 1997) | tutti lo conoscono, si gioca anche da telefono con swipe | griglia + input touch |
| **Breakout / Arkanoid** (Atari, 1976) | fisica pallina già vista in Gorillas, si riusa | collisioni con angolo, power-up |
| **Tetris** (1984) | il classico dei classici, mancava | rotazione pezzi, wall kick, livelli a velocità crescente |
| **Frogger** (Konami, 1981) | corsie a scorrimento, ritmo | tronchi che trasportano, timer per rana |

### Fascia 2: una settimana, gioco "vero"

| Gioco | Perché | Novità tecnica |
|---|---|---|
| **Pac-Man** (Namco, 1980) | labirinto + 4 fantasmi con personalità diverse | pathfinding, stati (chase/scatter/frightened) |
| **Asteroids** (Atari, 1979) | grafica vettoriale, inerzia | fisica con attrito zero, wrap-around, poligoni |
| **Space Invaders** (Taito, 1978) | tema Taito come QIX, alieni che accelerano | sprite in griglia, ripari distruttibili |
| **Lunar Lander** (Atari, 1979) | seguito naturale di Gorillas (gravità + traiettorie) | terreno generato, carburante, atterraggio morbido |
| **Bomberman** (Hudson, 1983) | il più divertente in due sullo stesso tastiero | esplosioni a croce, blocchi distruttibili, 2 giocatori |

### Fascia 3: ambiziosi

| Gioco | Perché | Novità tecnica |
|---|---|---|
| **Nibbles** (QBasic, 1991) | fratello di Gorillas, chiude la coppia QBasic | livelli con muri, 2 giocatori |
| **Prince of Persia**-like | animazione fluida, piattaforme | macchina a stati per il personaggio, sprite disegnati a mano |
| **Elite**-like | wireframe 3D nello spazio | proiezione 3D senza librerie |

## Proposta di ordine

1. **Tetris**: manca, è atteso, e sblocca il tema "griglia + pezzi" riusabile.
2. **Nibbles**: piccolo, chiude la coppia QBasic (Gorillas + Nibbles), 2 giocatori.
3. **Asteroids**: nuova strada tecnica (vettoriale) che serve poi a Lunar Lander ed Elite.
4. **Pac-Man**: il più impegnativo della fascia 2, ma quello che insegna di più (IA dei fantasmi).

## Decisioni prese

_(vuoto: si compila man mano)_

## Da fare a livello di repository

- [ ] Unire `qix`, `Gorilla`, `campo-minato` su `main` e aggiornare la tabella del README.
- [ ] Una pagina `index.html` alla radice che elenca i giochi (menu di avvio).
- [ ] Convenzione comune: `P` pausa, `M` audio, record in `localStorage` con chiave `giochi.<nome>.record`.
