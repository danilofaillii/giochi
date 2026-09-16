# Giochi

Remake di classici arcade in HTML e JavaScript puro. Ogni gioco è un singolo
file `index.html` nella sua cartella: nessun framework, nessuna dipendenza,
nessun build step. Si apre nel browser e si gioca.

| Gioco | Cartella | Stato |
|---|---|---|
| QIX (Taito, 1981) | `qix/` | giocabile, in sviluppo sul ramo `qix` |

## QIX

Conquista almeno il 75% del campo tracciando linee nell'area vuota.

- **Frecce / WASD** muovono il cursore lungo i bordi.
- **Spazio** traccia veloce, **Shift** traccia lento (punti doppi).
- Il **QIX** non deve toccare la linea mentre la disegni; gli **Sparx** pattugliano i bordi.
- Se ti fermi a metà tracciato, il **Fuse** brucia la linea alle tue spalle.
- A tempo scaduto gli Sparx diventano **Super Sparx**: più veloci, ti inseguono anche sulla linea e ne arriva uno nuovo ogni dieci secondi.
- Dal livello 3 ci sono due QIX: separarli con una linea vale un bonus di 20.000 punti.
- **P** pausa, **M** audio on/off. Record e audio restano salvati nel browser.

