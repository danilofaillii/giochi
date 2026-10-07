# Giochi

Remake di classici arcade in HTML e JavaScript puro. Ogni gioco è un singolo
file `index.html` nella sua cartella: nessun framework, nessuna dipendenza,
nessun build step. Si apre nel browser e si gioca.

| Gioco | Cartella | Stato |
|---|---|---|
| QIX (Taito, 1981) | `qix/` | giocabile, in sviluppo sul ramo `qix` |
| Gorillas (Microsoft, QBasic, 1991) | `gorilla/` | giocabile contro il computer, in due, o online (lobby con partite in attesa), con le musichette PLAY originali in Web Audio, sul ramo `Gorilla` |

## Nota sull'online di Gorillas

La modalità online usa lo store condiviso del runtime degli Artifact di claude.ai
(`claude.use('db')`): funziona solo quando la pagina è aperta dal link pubblicato
su claude.ai, e può giocare chi è invitato almeno come Contributor. Aperta da un
file locale o da un altro hosting, la pagina lo rileva e offre solo computer e due
giocatori sullo stesso schermo. La partita è un seme più la lista dei tiri: ogni
browser ricostruisce la stessa città (collisioni su una bitmap, non sui pixel del
canvas) e arriva allo stesso esito da solo. Per portarla su un altro backend basta
sostituire il blocco `Online` in `gorilla/index.html`, una cinquantina di righe.
