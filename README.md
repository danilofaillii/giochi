# Giochi

Remake di classici arcade in HTML e JavaScript puro. Ogni gioco è un singolo
file `index.html` nella sua cartella: nessun framework, nessuna dipendenza,
nessun build step. Si apre nel browser e si gioca.

| Gioco | Cartella | Stato |
|---|---|---|
| QIX (Taito, 1981) | `qix/` | giocabile, in sviluppo sul ramo `qix` |
| Campo minato | `campo-minato/` | giocabile, in sviluppo sul ramo `campo-minato` |

## Campo minato

Scopri tutte le caselle libere senza far esplodere una mina. I numeri dicono
quante mine ci sono nelle otto caselle intorno.

- **Clic sinistro** scopre una casella, **clic destro** mette o toglie la bandierina.
- **Clic su un numero** già scoperto: se le bandierine intorno bastano, scopre tutte le altre caselle vicine.
- Il primo clic è sempre sicuro: le mine vengono piazzate dopo, lontano dalla casella scelta e dalle sue vicine.
- Tre livelli: **Principiante** 9×9 con 10 mine, **Intermedio** 16×16 con 40, **Esperto** 30×16 con 99. Tasti **1**, **2**, **3** per cambiare.
- Da tastiera: **frecce** per muoversi, **Spazio/Invio** per scoprire, **F** per la bandierina.
- Su telefono: pressione lunga per la bandierina, oppure il pulsante **Modalità bandierina**.
- **R** o la faccina per una nuova partita, **M** audio on/off. Record per livello, livello scelto e audio restano salvati nel browser.
