---
title: Was ist Event-Sourcing?
description: Eine kurze Erklärung zu Event-Sourcing.
author: Nico Gräf
author_url: https://nicograef.com
date: 2025-02-06
tags:
  - Event
  - Event-Sourcing
  - CQRS
  - Software Architecture
---

# Was ist Event-Sourcing?

Event-Sourcing ist eine Alternative zu CRUD. Dabei werden nicht die aktuellen Zustände von Objekten gespeichert, sondern alle Änderungen (Events), die zu diesem Zustand geführt haben. Dies kann eine vollständige Nachverfolgbarkeit und Wiederherstellung des Systemzustands zu jedem beliebigen Zeitpunkt ermöglichen.

**Stell dir Event-Sourcing wie Git vor:** Git speichert nicht einfach den aktuellen Zustand deines Codes, sondern jeden einzelnen Commit – jede Änderung, die jemals gemacht wurde. Du kannst jederzeit zu einem früheren Stand zurückkehren, sehen wer wann was geändert hat, und verstehen warum bestimmte Entscheidungen getroffen wurden. Event-Sourcing verfolgt einen ähnlichen Ansatz für Anwendungsdaten.

In einem Event-Sourcing-System gibt es kein UPDATE und es gibt auch kein DELETE. Genaugenommen gibt es auch kein CREATE, stattdessen gibt es nur "Write/Add Event". Selbst das Lesen (READ) funktioniert anders als bei CRUD: Anstatt den aktuellen Zustand eines Objekts direkt aus einer Datenbanktabelle abzurufen, werden alle Events zu diesem Objekt gelesen und der aktuelle Zustand durch das Anwenden dieser Events rekonstruiert.

## Was ist ein Event?

Ein Event beschreibt ein Ereignis, das im System stattgefunden hat. Events sind unveränderliche Fakten, die das beschreiben, was passiert ist. Bei deinem Bankkonto könnte es zum Beispiel diese Events geben: "Überweisung wurde durchgeführt" oder "Bargeld wurde abgehoben".

Ein Event besteht aus:

- **Type**: Der Event-Typ (z.B. `cart.product-added`)
- **Time**: Zeitstempel, wann das Event aufgetreten ist
- **Subject**: Referenz zum betroffenen Objekt/Aggregat (z.B. `user:456`)
- **Data**: Alle relevanten Informationen (z.B. Produkt-ID, Menge)

## Event Store

_Event Store_ ist die Bezeichnung für eine Datenbank, die Events speichert. Als Tabelle in einer relationalen Datenbank, als Collection in einer NoSQL-Datenbank oder in einer speziell für Events optimierten Datenbank. Die wichtigste Eigenschaft: Der Event Store ist **append-only** – Events werden nur hinzugefügt, niemals geändert oder gelöscht.

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

Das Ergebnis: Im Warenkorb liegt Produkt 456 mit Menge 2. Wir können aber auch nachvollziehen, dass Produkt 123 einmal hinzugefügt, in der Menge geändert und dann wieder entfernt wurde – diese Information wäre bei CRUD verloren.

> Mehr Details und eine vollständige Implementierung findest du im Artikel [Event-Sourcing am Beispiel Warenkorb erklärt](/articles/event-sourcing-am-beispiel-warenkorb-erklaert).

## Vorteile

- **Näher an der Fachdomäne**: Events beschreiben, was im Business passiert ist, nicht nur technische Zustandsänderungen. Das macht Event-Sourcing zu einer guten Ergänzung für Domain-Driven Design (DDD).
- **Audit Trail**: Jede Änderung ist dokumentiert – wer hat wann was gemacht?
- **Zeitreisen möglich**: Der Zustand kann für jeden beliebigen Zeitpunkt rekonstruiert werden.
- **Keine Information geht verloren**: Der Kontext und die Absicht hinter jeder Änderung bleiben erhalten.
- **Potenzial für Analytics**: Mit den historischen Daten lassen sich Analysen durchführen, die mit CRUD nicht möglich wären.

## Nachteile

- **Deutlich mehr Komplexität**: Event-Sourcing erfordert ein fundamentales Umdenken in der Architektur.
- **Performance-Probleme**: Ohne zusätzliche Maßnahmen wie Snapshots oder CQRS kann das System bei vielen Events langsam werden.
- **Schema-Evolution ist schmerzhaft**: Alte Event-Schemas bleiben für immer erhalten. Änderungen müssen rückwärtskompatibel sein.
- **Eventual Consistency**: Bei CQRS-basierten Systemen ist das Lese-Modell nicht sofort aktuell.
- **Overkill für die meisten Anwendungen**: Die meisten CRUD-Anwendungen brauchen kein Event-Sourcing. Der Overhead lohnt sich nur, wenn die Vorteile wirklich benötigt werden.
