---
title: Was ist Event-Sourcing?
description: Eine kurze Erklärung zu Event-Soucing.
author: Nico Gräf
author_url: https://nicograef.com
date: 2025-02-06
draft: true
tags:
  - Event
  - Event-Sourcing
  - CQRS
  - Software Architecture
---

## Was ist ein Event?

Ein Event beschreibt ein Ereignis, das im System stattgefunden hat. Events sind unveränderliche Fakten, die das beschreiben, was passiert ist. Bei deinem Bankkonto könnte es zum Beispiel diese Events geben: "Überweisung wurde durchgeführt" oder "Bargeld wurde abgehoben". Im Fall des Warenkorbs könnten Events z.B. "Produkt wurde dem Warenkorb hinzugefügt", "Produkt wurde aus dem Warenkorb entfernt" oder "Menge eines Produkts wurde geändert" sein.

Ein Event besteht aus:

- **Type**: Der Event-Typ (z.B. `cart.product-added`)
- **Time**: Zeitstempel, wann das Event aufgetreten ist
- **Subject**: Referenz zum betroffenen Objekt/Aggregat (z.B. `user:456`)
- **Data**: Alle relevanten Informationen (z.B. Produkt-ID, Menge)

Die <abbr title="Cloud Native Computing Foundation">CNCF</abbr> hat mit [CloudEvents](https://cloudevents.io/) einen Standard für Event-Formate definiert. Ein Event könnte so aussehen:

```json
{
  "id": "8875",
  "type": "cart.product-added",
  "time": "2026-01-15T10:30:00Z",
  "subject": "user:456",
  "data": {
    "productId": 123,
    "quantity": 2
  }
}
```

Das `subject`-Feld ist besonders wichtig: Es ermöglicht uns, alle Events zu einem bestimmten Benutzer (oder Warenkorb) effizient abzufragen.

## Was ist Event-Sourcing?

**Stell dir Event-Sourcing wie Git vor:** Git speichert nicht einfach den aktuellen Zustand deines Codes, sondern jeden einzelnen Commit – jede Änderung, die jemals gemacht wurde. Du kannst jederzeit zu einem früheren Stand zurückkehren, sehen wer wann was geändert hat, und verstehen warum bestimmte Entscheidungen getroffen wurden. Event-Sourcing verfolgt einen ähnlichen Ansatz für Anwendungsdaten – allerdings mit deutlich mehr Komplexität in der Umsetzung.

In einem Event-Sourcing-System gibt es kein UPDATE und es gibt auch kein DELETE. Genaugenommen gibt es auch kein CREATE, stattdessen gibt es nur "Write/Add Event". Selbst das Lesen (READ) funktioniert anders als bei CRUD: Anstatt den aktuellen Zustand eines Objekts direkt aus einer Datenbanktabelle abzurufen, werden alle Events zu diesem Objekt gelesen und der aktuelle Zustand durch das Anwenden dieser Events rekonstruiert.

## Event Store

Ein Event Store ist eine Datenbank, die speziell für Events optimiert ist. Die wichtigste Eigenschaft: Er ist **append-only** – Events werden nur hinzugefügt, niemals geändert oder gelöscht.

Für unser Beispiel verwenden wir eine einfache relationale Datenbank als Event Store. In der Praxis gibt es auch spezialisierte Event-Store-Datenbanken wie [KurrentDB](https://www.kurrent.io/) (ehemals EventStoreDB) oder [EventSourcingDB](https://www.eventsourcingdb.io/), die zusätzliche Features wie Event-Streams, Subscriptions und optimierte Abfragen bieten.

## Event-Driven Architecture (EDA)

Event-Driven Architecture (EDA) ist ein Architekturstil, bei dem die Kommunikation zwischen verschiedenen Komponenten oder Diensten hauptsächlich durch Events erfolgt. In einer EDA reagieren Komponenten auf Events, die von anderen Komponenten ausgelöst werden, anstatt direkt miteinander zu kommunizieren.
