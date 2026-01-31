---
title: Was sind DDD, CQRS, Event-Sourcing und Event-Driven Architecture?
description: asd
author: Nico Gräf
author_url: https://nicograef.com
date: 2025-01-31
draft: true
tags:
  - Domain-Driven Design
  - Events
  - Event-Sourcing
  - CQRS
  - Event-Driven Architecture
  - Software Architecture
---

# Was sind DDD, CQRS, Event-Sourcing und Event-Driven Architecture?

In diesem Artikel erkläre ich den unterschied zwischen Domain-Driven Design (DDD), Command Query Responsibility Segregation (CQRS), Event-Sourcing und Event-Driven Architecture.

## Domain Driven Design (DDD)

> DDD ist ein umfangreiches Thema. Dieser Artikel vermittelt die Grundidee. Vertiefende Themen wie Bounded Context, Subdomain, Aggregate und Event-Storming habe ich hier bewusst ausgelasen.

**Domain** ist ein englischer Fachbegriff aus der Softwareentwicklung und bezeichnet den Fachbereich oder das Anwendungsgebiet, für das eine Software entwickelt wird (z.B. Buchhaltung, Gastronomie). Die Domain umfasst die Geschäftslogik, Regeln, Prozesse und Konzepte, die für das jeweilige Anwendungsgebiet relevant bzw. spezifisch sind. Andere Wörter dafür sind Fachlichkeit, Geschäftslogik, Geschäftsprozesse, Fachdomäne oder Problemraum.

Wie der Name schon sagt, wird bei DDD die Softwareentwicklung von der Fachlichkeit "getrieben". Das bedeutet, wir lösen uns erstmal von technischen Details, CRUD-Operationen oder Frontend-Framework-Diskussionen, und konzentrieren uns stattdessen darauf, die Geschäftsprozesse und -regeln zu verstehen und ein gemeinsames Verständnis, sowie eine gemeinsame Sprache mit den Fachexperten aufzubauen (vgl. Ubiquitous Language).

Im Code soll diese Fachlichkeit dann möglichts unabhängig von der jeweiligen Technik umgesetzt werden. Im bestenfall findet man eine Klasse / ein Modul / ein Paket / eine Funktion mit dem selben Namen wie das Fachkonzept (also so, wie es der Kunde oder die Fachabteilung auch benennen oder beschreiben würde). Das kann auch mal bedeuten, dass ein deutscher Fachbegriff zwischen dem (meist) englischsprachigem Code auftauch. Dieser Teil der Software sollte keine Abhängigkeit auf eine Datenbank, einen HTTP-Controller oder ein Frontend-Framework haben &mdash; das hat die Fachlichkeit in der "echten" Welt auch nicht. So wie man bei Test-driven Development (TDD) zuerst die Tests schreibt, würde man bei DDD diesen Teil der Software zuerst modellieren und implementieren. Vorteil: Man braucht keine technische Infrastruktur, um die Fachlichkeit zu entwickeln und zum Testen muss man auch keine Datenbank hochfahren, einen Webserver starten oder einen DOM rendern.

Der Rest der Software (Datenbank, API, Frontend etc.) wird dann um diese Fachlichkeit herum gebaut. Dadurch wird die Fachlichkeit von technischen Details entkoppelt und kann leichter verstanden, getestet und weiterentwickelt werden. Ebenso können sich Geschäftsprozesse oder -regeln ändern, ohne dass die technische Infrastruktur angepasst werden muss und technische Änderungen (z.B. Wechsel der Datenbank oder des Frontend-Frameworks) haben keinen Einfluss auf die Fachlichkeit.

![Vereinfachte Onion-Architektur nach Domain-Driven Design](/assets/img/articles/ddd-onion-architecture.png)
_Vereinfachte Onion-Architektur nach Domain-Driven Design_

## CQRS (Command Query Responsibility Segregation)

CQRS trennt die Lese- und Schreiboperationen in einem System. Das bedeutet, dass es unterschiedliche Modelle und manchmal sogar unterschiedliche Datenbanken für das Schreiben (Commands) und das Lesen (Queries) gibt.

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

In diesem Artikel vergleiche ich Event-Sourcing mit dem traditionellen CRUD-Ansatz anhand des Beispiels Warenkorb in einem Online-Shop.

> **Disclaimer:** Event-Sourcing hat — wie alles — seine Vor- und Nachteile und sollte nur angewendet werden, wenn es wirklich gute Gründe dafür gibt. Die zusätzliche Komplexität ist nicht zu unterschätzen. Auch wenn dieser Artikel den Warenkorb als Beispiel für Event-Sourcing verwendet, ist ein einfacher Warenkorb in der Praxis kein guter Kandidat für Event-Sourcing – CRUD reicht hier meist völlig aus.

## Event Store

Ein Event Store ist eine Datenbank, die speziell für Events optimiert ist. Die wichtigste Eigenschaft: Er ist **append-only** – Events werden nur hinzugefügt, niemals geändert oder gelöscht.

Für unser Beispiel verwenden wir eine einfache relationale Datenbank als Event Store. In der Praxis gibt es auch spezialisierte Event-Store-Datenbanken wie [KurrentDB](https://www.kurrent.io/) (ehemals EventStoreDB) oder [EventSourcingDB](https://www.eventsourcingdb.io/), die zusätzliche Features wie Event-Streams, Subscriptions und optimierte Abfragen bieten.

## Event-Driven Architecture (EDA)

Event-Driven Architecture (EDA) ist ein Architekturstil, bei dem die Kommunikation zwischen verschiedenen Komponenten oder Diensten hauptsächlich durch Events erfolgt. In einer EDA reagieren Komponenten auf Events, die von anderen Komponenten ausgelöst werden, anstatt direkt miteinander zu kommunizieren.
