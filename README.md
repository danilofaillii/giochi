# Giochi

Remake di classici arcade in HTML e JavaScript puro. Ogni gioco è un singolo
file `index.html` nella sua cartella: nessun framework, nessuna dipendenza,
nessun build step. Si apre nel browser e si gioca.

| Gioco | Cartella | Stato |
|---|---|---|
| QIX (Taito, 1981) | `qix/` | giocabile, in sviluppo sul ramo `qix` |
| Breakout (Atari, 1976) | `breakout/` | giocabile, in sviluppo sul ramo `breakout` |

## Breakout

Abbatti tutti i mattoni con la palla senza farla cadere.

- **Frecce / A D** o il **mouse** muovono la racchetta, **Spazio** (o un tocco) lancia la palla.
- L'angolo di rimbalzo dipende da dove colpisci la racchetta: ai bordi la palla parte più inclinata.
- Regole Atari originali: le file rosse valgono 7 punti e fanno correre la palla, il quarto e il dodicesimo colpo la accelerano, e se la palla tocca il soffitto la racchetta si restringe fino alla prossima vita.
- Mattoni **argento** resistono a due colpi. Dal livello 3 ne compaiono sempre di più.
- Power-up in caduta, da prendere con la racchetta: **E** racchetta larga, **T** tripla palla, **S** palla lenta, **V** vita extra.
- Sei schemi di muro che ruotano a ogni livello, con la palla sempre più veloce.
- **P** pausa, **M** audio on/off. Record e audio restano salvati nel browser.
