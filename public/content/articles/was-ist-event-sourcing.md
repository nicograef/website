# Was ist Event-Sourcing?

Event-Sourcing ist eine Alternative zu CRUD: Statt den aktuellen Zustand eines Objekts zu speichern, speichert man alle Änderungen (Events), die zu diesem Zustand geführt haben. Damit lässt sich der Systemzustand für jeden beliebigen Zeitpunkt rekonstruieren, zumindest in der Theorie.

**Stell dir Event-Sourcing wie Git vor:** Git speichert nicht den aktuellen Stand deines Codes, sondern jeden einzelnen Commit. Du kannst jederzeit zu einem früheren Stand zurückkehren und nachvollziehen, wer wann was geändert hat. Event-Sourcing macht dasselbe mit Anwendungsdaten.

In einem Event-Sourcing-System gibt es kein UPDATE und kein DELETE, nur das Anhängen neuer Events. Selbst das Lesen funktioniert anders als bei CRUD: Der aktuelle Zustand wird nicht direkt aus einer Datenbanktabelle abgerufen, sondern aus allen Events zu diesem Objekt rekonstruiert.

## Was ist ein Event?

Ein Event beschreibt ein Ereignis, das im System stattgefunden hat: ein unveränderlicher Fakt. Bei deinem Bankkonto könnte es zum Beispiel diese Events geben: „Überweisung wurde durchgeführt“ oder „Bargeld wurde abgehoben“.

Ein Event besteht aus:

- **Type**: Der Event-Typ (z.B. `cart.product-added`)
- **Time**: Zeitstempel, wann das Event aufgetreten ist
- **Subject**: Referenz zum betroffenen Objekt/Aggregat (z.B. `user:456`)
- **Data**: Alle relevanten Informationen (z.B. Produkt-ID, Menge)

## Event Store

**Event Store** ist die Bezeichnung für eine Datenbank, die Events speichert. Als Tabelle in einer relationalen Datenbank, als Collection in einer NoSQL-Datenbank oder in einer speziell für Events optimierten Datenbank. Die wichtigste Eigenschaft: Der Event Store ist **append-only**. Events werden nur hinzugefügt, niemals geändert oder gelöscht.

## Kurzes Beispiel: Warenkorb

Stell dir einen Warenkorb in einem Online-Shop vor. Mit CRUD würde man den aktuellen Zustand des Warenkorbs in einer Tabelle speichern. Mit Event-Sourcing speichern wir stattdessen jede Änderung als Event:

| Event            | Daten                    |
| ---------------- | ------------------------ |
| product-added    | Produkt 123, Menge: 2   |
| quantity-changed | Produkt 123, Menge: 1   |
| product-added    | Produkt 456, Menge: 2   |
| product-removed  | Produkt 123              |

Um den aktuellen Zustand des Warenkorbs zu ermitteln, werden alle Events der Reihe nach angewendet:

| Nach Event       | Warenkorb-Inhalt                                |
| ---------------- | ----------------------------------------------- |
| product-added    | Produkt 123 (Menge: 2)                          |
| quantity-changed | Produkt 123 (Menge: 1)                          |
| product-added    | Produkt 123 (Menge: 1), Produkt 456 (Menge: 2)  |
| product-removed  | Produkt 456 (Menge: 2)                          |

Das Ergebnis: Im Warenkorb liegt Produkt 456 mit Menge 2. Wir können aber auch nachvollziehen, dass Produkt 123 einmal hinzugefügt, in der Menge geändert und dann wieder entfernt wurde. Diese Information wäre bei CRUD verloren.

## Vorteile

- **Näher an der Fachdomäne**: Events beschreiben, was im Business passiert ist, nicht nur technische Zustandsänderungen. Das macht Event-Sourcing zu einer guten Ergänzung für Domain Driven Design (DDD).
- **Audit Trail**: Wer hat wann was gemacht? Jede Änderung ist dokumentiert.
- **Zeitreisen möglich**: Der Zustand kann für jeden beliebigen Zeitpunkt rekonstruiert werden.
- **Keine Information geht verloren**: Der Kontext und die Absicht hinter jeder Änderung bleiben erhalten.
- **Potenzial für Analytics**: Mit den historischen Daten lassen sich Analysen durchführen, die mit CRUD nicht möglich wären.

## Nachteile

- **Deutlich mehr Komplexität**: Event-Sourcing erfordert ein fundamentales Umdenken in der Architektur.
- **Performance-Probleme**: Ohne zusätzliche Maßnahmen wie Snapshots oder CQRS kann das System bei vielen Events langsam werden.
- **Schema-Evolution ist schmerzhaft**: Alte Event-Schemas bleiben für immer erhalten. Änderungen müssen rückwärtskompatibel sein.
- **Eventual Consistency**: Bei CQRS-basierten Systemen ist das Lese-Modell nicht sofort aktuell.
- **Overkill für die meisten Anwendungen**: Die meisten CRUD-Anwendungen brauchen kein Event-Sourcing. Der Overhead lohnt sich nur, wenn die Vorteile wirklich benötigt werden.

Diese Abwägung habe ich für mein eigenes Projekt getroffen: Bei [jotti](https://jotti.rocks), meinem Kassensystem für Vereine, ist das Kassenjournal ein append-only Event Store. Ob eine Servicekraft eine Bestellung auf einen anderen Tisch umbucht oder der Kassensturz am Abend eine Soll-Ist-Differenz verbucht: Alles landet als unveränderliches Event im Journal. Für ein Kassensystem, ausgelegt auf die <abbr title="Kassensicherungsverordnung">KassenSichV</abbr>, ist das eine solide Basis. Der Code ist source-available und liegt auf [GitHub](https://github.com/nicograef/jotti).
