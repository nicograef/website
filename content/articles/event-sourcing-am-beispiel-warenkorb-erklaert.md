---
title: Event-Sourcing am Beispiel Warenkorb erklärt
description: Event-Sourcing ist eine Alternative zu CRUD. Lerne anhand eines Warenkorb-Beispiels, wie Events statt Zuständen gespeichert werden und welche Vorteile das bringt.
author: Nico Gräf
author_url: https://nicograef.com
date: 2025-01-23
tags:
  - Software Architecture
  - Event-Sourcing
  - CQRS
  - Event-Driven Architecture
  - Domain-Driven Design
---

# Event-Sourcing am Beispiel Warenkorb erklärt

Event-Sourcing ist eine Alternative zu CRUD. Dabei werden nicht die aktuellen Zustände von Objekten gespeichert, sondern alle Änderungen (Events), die zu diesem Zustand geführt haben. Dies kann – zumindest theoretisch – eine vollständige Nachverfolgbarkeit und Wiederherstellung des Systemzustands zu jedem beliebigen Zeitpunkt ermöglichen.

**Stell dir Event-Sourcing wie Git vor:** Git speichert nicht einfach den aktuellen Zustand deines Codes, sondern jeden einzelnen Commit – jede Änderung, die jemals gemacht wurde. Du kannst jederzeit zu einem früheren Stand zurückkehren, sehen wer wann was geändert hat, und verstehen warum bestimmte Entscheidungen getroffen wurden. Event-Sourcing verfolgt einen ähnlichen Ansatz für Anwendungsdaten – allerdings mit deutlich mehr Komplexität in der Umsetzung.

In einem Event-Sourcing-System gibt es kein UPDATE und es gibt auch kein DELETE. Genaugenommen gibt es auch kein CREATE, stattdessen gibt es nur "Write/Add Event". Selbst das Lesen (READ) funktioniert anders als bei CRUD: Anstatt den aktuellen Zustand eines Objekts direkt aus einer Datenbanktabelle abzurufen, werden alle Events zu diesem Objekt gelesen und der aktuelle Zustand durch das Anwenden dieser Events rekonstruiert.

In diesem Artikel vergleiche ich Event-Sourcing mit dem traditionellen CRUD-Ansatz anhand des Beispiels Warenkorb in einem Online-Shop.

> **Disclaimer:** Event-Sourcing hat — wie alles — seine Vor- und Nachteile und sollte nur angewendet werden, wenn es wirklich gute Gründe dafür gibt. Die zusätzliche Komplexität ist nicht zu unterschätzen. Auch wenn dieser Artikel den Warenkorb als Beispiel für Event-Sourcing verwendet, ist ein einfacher Warenkorb in der Praxis kein guter Kandidat für Event-Sourcing – CRUD reicht hier meist völlig aus.

## Warenkorb mit CRUD

Für die CRUD-Implementierung nehmen wir eine relationale Datenbank und ein REST-like Backend, um CRUD-Operationen durchzuführen.

Stellen wir uns vor, es gibt eine Tabelle `shopping_carts` mit den Spalten `user_id`, `product_id` und `quantity`. Wenn ein Benutzer ein Produkt zu seinem Warenkorb hinzufügt, wird ein neuer Eintrag in der Tabelle erstellt (CREATE). Wenn der Benutzer die Menge eines Produkts ändert, wird der entsprechende Eintrag aktualisiert (UPDATE). Wenn der Benutzer ein Produkt entfernt, wird der Eintrag gelöscht (DELETE). Wir speichern also immer den aktuellen Zustand des Warenkorbs. Um den aktuellen Zustand des Warenkorbs zu ermitteln, müssen wir einfach die Einträge in der Tabelle für den jeweiligen Benutzer abfragen (READ). Das Datenbankmodell könnte also so aussehen:

```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    name VARCHAR(100)
);

CREATE TABLE products (
    id INT PRIMARY KEY,
    name VARCHAR(100),
    price DECIMAL(10, 2)
);

CREATE TABLE shopping_carts (
    user_id INT,
    product_id INT,
    quantity INT,
    PRIMARY KEY (user_id, product_id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

Das Backend könnte dann folgende Endpunkte bereitstellen:

| Methode  | Endpunkt             | Beschreibung                |
| -------- | -------------------- | --------------------------- |
| `POST`   | `/cart/products`     | Produkt hinzufügen (CREATE) |
| `PATCH`  | `/cart/product/{id}` | Menge ändern (UPDATE)       |
| `DELETE` | `/cart/product/{id}` | Produkt entfernen (DELETE)  |
| `GET`    | `/cart`              | Warenkorb abrufen (READ)    |

Dieses System würde gut funktionieren und die oberflächlichen Anforderungen eines Warenkorbs erfüllen. Allerdings gibt es auch einige Fragen, die man mit diesem Ansatz nicht beantworten kann.

### Feature Request: Personalisierte Rabattcodes

Die Marketing-Abteilung möchte Benutzern personalisierte Rabattcodes schicken, die Produkte in ihren Warenkorb gelegt, aber nie gekauft haben. Mit CRUD können wir diese Information nicht liefern – wir wissen nur, was _jetzt_ im Warenkorb liegt, nicht was _früher_ drin war.

### UX-Frage: Plus/Minus-Buttons oder Eingabefeld?

Das UX-Team fragt, ob Benutzer lieber +/- Buttons oder ein Eingabefeld zum Ändern der Produktmenge nutzen würden. Mit CRUD sehen wir nur die finale Menge, nicht wie oft und in welche Richtung Benutzer die Menge anpassen.

### Issue Debugging: Warum wurde das Produkt entfernt?

Ein Kunde beschwert sich, dass ein Produkt aus seinem Warenkorb verschwunden ist. War es der Benutzer selbst? Oder hat das System das Produkt entfernt, weil es nicht mehr verfügbar war? Mit CRUD können wir das nicht unterscheiden.

### Weitere unbeantwortbare Fragen

- Welche Produkte werden oft in den Warenkorb gelegt aber dann doch nicht bestellt (wieder entfernt)?
- Wie oft wird die Menge eines Produktes reduziert oder erhöht?
- Wie oft versuchen Benutzer ein Produkt zu bestellen, das nicht mehr verfügbar ist (und deshalb wieder aus dem Warenkorb entfernt wurde)?
- Welche Produkte verweilen am längsten im Warenkorb, bevor sie gekauft oder entfernt werden?
- Wird der Warenkorb als Merkliste verwendet? Sollten wir eine separate Merkliste-Funktion anbieten?
- Wie sieht der Warenkorb-Verlauf von Benutzern aus, die viel bei uns bestellen?
- ... und wie unterscheidet dieser sich von dem Verlauf von Benutzern, die wenig bestellen?

Wenn solche Fragen gestellt werden und ein CRUD-System im Einsatz ist, wird oft (nachträglich) versucht, diese Informationen durch zusätzliche Statistik-Tabellen, Historie-Tabellen, Log-Tabellen oder Audit-Logs zu erfassen. Dies führt jedoch oft zu komplexen und schwer wartbaren Systemen.

Außerdem geht durch die Kombination von CRUD-System und REST-API fast immer der Kontext der Änderung, die Fachlichkeit hinter dem CREATE/UPDATE/DELETE oder die Absicht des Benutzers verloren. Wenn das System komplexer wird, gibt es möglicherweise viele verschiedene Gründe, die zu einem `POST /cart/products` Aufruf führen können (z.B. "Produkt hinzufügen um direkt zu kaufen", "Produkt wieder hinzufügen weil versehentlich entfernt", "Produkt für später vormerken", etc.). Diese verschiedenen Gründe können jedoch nicht mehr unterschieden werden, wenn nur der aktuelle Zustand des Warenkorbs gespeichert wird und selbst das Backend nur CREATE, UPDATE und DELETE kennt.

Genau hier setzt Event-Sourcing an.

## Was ist ein Event?

Bevor wir in die Implementierung einsteigen, klären wir kurz, was ein Event eigentlich ist.

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

## Event Store

Ein Event Store ist eine Datenbank, die speziell für Events optimiert ist. Die wichtigste Eigenschaft: Er ist **append-only** – Events werden nur hinzugefügt, niemals geändert oder gelöscht.

Für unser Beispiel verwenden wir eine einfache relationale Datenbank als Event Store. In der Praxis gibt es auch spezialisierte Event-Store-Datenbanken wie [KurrentDB](https://www.kurrent.io/) (ehemals EventStoreDB) oder [EventSourcingDB](https://www.eventsourcingdb.io/), die zusätzliche Features wie Event-Streams, Subscriptions und optimierte Abfragen bieten.

## Warenkorb mit Event-Sourcing

In der Event-Sourcing-Implementierung speichern wir alle Änderungen am Warenkorb als Events. Jedes Event beschreibt eine einzelne Änderung am Warenkorb und enthält alle notwendigen Informationen, um diese Änderung zu verstehen.

Wir verwenden wieder eine relationale Datenbank, die Backend-API soll diesmal jedoch im Command-Style gebaut werden (ähnlich zu <abbr title="Remote Procedure Call">RPC</abbr> und <abbr title="Command Query Responsibility Segregation">CQRS</abbr>).

An den Tabellen `users` und `products` ändern wir nichts. Diese bleiben gleich wie bei der CRUD-Implementierung. Das Event-Sourcing wird in diesem Beispiel nur auf den Warenkorb angewendet. Dafür lösen wir uns von der `shopping_carts`-Tabelle und erstellen stattdessen eine Tabelle `events`. Dort speichern wir alle Events inklusive dem Subject (Benutzer-Referenz), dem Zeitstempel, der Art des Events und den dazugehörigen Daten.

Das Datenbankmodell könnte so aussehen:

```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    name VARCHAR(100)
);

CREATE TABLE products (
    id INT PRIMARY KEY,
    name VARCHAR(100),
    price DECIMAL(10, 2)
);

CREATE TABLE events (
    id INT PRIMARY KEY,
    type VARCHAR(100),
    subject VARCHAR(100),
    data JSON,
    time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Jedes Mal, wenn ein Benutzer ein Produkt zu seinem Warenkorb hinzufügt, wird ein neues Event in die Tabelle geschrieben. Wenn der Benutzer die Menge eines Produkts ändert, wird ebenfalls ein neues Event geschrieben. Wenn der Benutzer ein Produkt entfernt, wird auch dafür ein neues Event geschrieben.

Das Backend könnte dann folgende Endpunkte bereitstellen:

| Methode | Endpunkt                | Event                     |
| ------- | ----------------------- | ------------------------- |
| `POST`  | `/cart/add-product`     | `product-added`           |
| `POST`  | `/cart/remove-product`  | `product-removed`         |
| `POST`  | `/cart/change-quantity` | `quantity-changed`        |
| `GET`   | `/cart`                 | _(rekonstruiert Zustand)_ |

Beachte den Unterschied zur REST-API: Statt `POST /cart/products` mit einer generischen Payload verwenden wir sprechende Endpunkte wie `/cart/add-product`. Das macht die Absicht des Aufrufs explizit.

Die Events in der Tabelle könnten z.B. so aussehen:

| id  | type             | subject | data                                  | time                |
| --- | ---------------- | ------- | ------------------------------------- | ------------------- |
| 1   | product-added    | user:1  | `{ "productId": 123, "quantity": 2 }` | 2025-01-23 10:00:00 |
| 2   | product-removed  | user:2  | `{ "productId": 456 }`                | 2025-01-23 10:02:00 |
| 3   | quantity-changed | user:1  | `{ "productId": 123, "quantity": 1 }` | 2025-01-23 10:03:00 |
| 4   | product-added    | user:1  | `{ "productId": 456, "quantity": 2 }` | 2025-01-23 10:03:20 |
| 5   | product-removed  | user:1  | `{ "productId": 123 }`                | 2025-01-23 10:04:00 |
| 6   | product-added    | user:3  | `{ "productId": 789, "quantity": 3 }` | 2025-01-23 10:05:00 |
| 7   | quantity-changed | user:1  | `{ "productId": 456, "quantity": 3 }` | 2025-01-23 10:09:30 |

Um den aktuellen Zustand des Warenkorbs zu ermitteln, müssen wir alle Events für den jeweiligen Benutzer lesen und diese der Reihe nach anwenden. Was passiert also, wenn wir `GET /cart` für Benutzer 1 aufrufen?

```sql
SELECT * FROM events WHERE subject = 'user:1' ORDER BY time;
```

### Schritt 1: Events lesen

Wir lesen alle Events für Benutzer 1 aus der Tabelle:

- Event 1: Produkt 123 mit Menge 2 hinzugefügt
- Event 3: Menge von Produkt 123 auf 1 geändert
- Event 4: Produkt 456 mit Menge 2 hinzugefügt
- Event 5: Produkt 123 entfernt
- Event 7: Menge von Produkt 456 auf 3 geändert

### Schritt 2: Zustand rekonstruieren

Wir wenden die Events der Reihe nach an:

| Nach Event | Warenkorb-Inhalt                               |
| ---------- | ---------------------------------------------- |
| Event 1    | Produkt 123 (Menge: 2)                         |
| Event 3    | Produkt 123 (Menge: 1)                         |
| Event 4    | Produkt 123 (Menge: 1), Produkt 456 (Menge: 2) |
| Event 5    | Produkt 456 (Menge: 2)                         |
| Event 7    | Produkt 456 (Menge: 3)                         |

### Schritt 3: Ergebnis zurückgeben

```json
{ "userId": 1, "cart": [{ "productId": 456, "quantity": 3 }] }
```

Wir können mit Event-Sourcing also die gleiche Funktionalität liefern, wie mit dem CRUD-Ansatz. Aber zusätzlich können wir jetzt auch alle zuvor gestellten Fragen beantworten, da wir alle Events gespeichert haben.

- Welche Produkte werden oft in den Warenkorb gelegt aber dann doch nicht bestellt?
  - Wir können alle `product-added` Events zählen und die entsprechenden `product-removed` Events dagegenstellen, um diese Information zu erhalten.
  - Bei Benutzer 1 sehen wir, dass Produkt 123 hinzugefügt und später entfernt wurde.
- Wie oft wird die Menge eines Produktes reduziert oder erhöht?
  - Wir können alle `quantity-changed` Events analysieren, um zu sehen, wie oft die Menge geändert wurde und in welche Richtung.
- Welche Produkte verweilen am längsten im Warenkorb, bevor sie gekauft oder entfernt werden?
  - Wenn wir ein Zeitstempel für die Bestellung oder sogar ein Bestell-Event haben, können die Zeitstempel der `product-added` und `cart-ordered` Events vergleichen, um die Verweildauer zu berechnen.

Da wir alle Events speichern, können wir theoretisch Analysen durchführen, um das Verhalten der Benutzer besser zu verstehen. Allerdings erfordert das zusätzliche Infrastruktur und Entwicklungsaufwand. Sagen wir die Marketing-Abteilung kommt auf die Idee, Benutzern personalisierte Angebote zu machen, basierend auf den Produkten, die sie häufig in den Warenkorb legen, aber nicht kaufen. Mit Event-Sourcing haben wir die Daten dafür – aber wir müssen trotzdem die Analyse-Logik implementieren und die Daten aufbereiten. Mit der CRUD-Implementierung wäre das schwieriger, da wir die historischen Daten nicht haben. Man müsste das System erst erweitern und kann selbst dann nur mit den zukünftigen Daten arbeiten.

## Wird das System nicht langsam?

Vielleicht fragst du dich jetzt, ob das System nicht langsam wird, wenn bei jeder Abfrage erst alle Events aus der Datenbank geladen und der Zustand rekonstruiert werden muss. Zumindest war das meine erste Reaktion, als ich das Konzept von Event-Sourcing zum ersten Mal gehört habe. Und ja – das ist ein echtes Problem. Bei einer naiven Implementierung kann das System schnell an seine Grenzen stoßen. Es gibt verschiedene Techniken, um dieses Problem zu mildern, aber keine davon ist kostenlos:

1. **Event-Modellierung** Durch die richtige Modellierung der Events kann die Anzahl der zu verarbeitenden Events reduziert werden. Zum Beispiel könnte man in unserem Beispielsystem jedesmal einen neuen Warenkorb "aufmachen", sobald der Benutzer den Warenkorb abschickt (z.B. durch eine Bestellung). Dadurch werden die Events für jeden abgeschlossenen Warenkorb getrennt gespeichert und es müssen nicht jedesmal alle Events eines Benutzers verarbeitet werden, sondern nur die Events des aktuellen Warenkorbs. In diesem Modell würde jeder Warenkorb eine eigene ID bekommen und die Events würden diese ID referenzieren. Ich würde mal behaupten, dass dadurch maximal 30-50 Events pro Warenkorb anfallen. Diese Anzahl von Events sollten problemlos vom Backend in Echtzeit verarbeitet werden können.

2. **Snapshots** Eine weitere Technik ist die Verwendung von Snapshots. Dabei wird in regelmäßigen Abständen der aktuelle Zustand des Objekts gespeichert (z.B. alle 100 Events oder nach bestimmten Ereignissen). Wenn der Zustand abgerufen werden muss, wird zuerst der letzte Snapshot geladen und dann nur die Events seit diesem Snapshot angewendet. Dadurch reduziert sich die Anzahl der zu verarbeitenden Events erheblich. Die historischen Events bleiben jedoch weiterhin erhalten und können für Analysen verwendet werden. In unserem Beispiel könnte ein Cronjob jede Nacht einen Snapshot erzeugen, in dem eine Liste aller Produkte aufgeführt ist, die von Benutzern in ihren Warenkorb hinzugefügt und danach wieder entfernt wurden (inklusive der Häufigkeit). Mit diesem Snapshot könnte dann die Analyse deutlich schneller durchgeführt werden.

3. **Optimierte Datenbanken** In unserem Beispiel haben wir eine relationale Datenbank für die Speicherung verwendet. Ich selbst habe dafür in einem Projekt PostgreSQL verwendet. Ebenso kann man bei kleinen Anwendungen NoSQL-Datenbanken wie MongoDB oder AWS DynamoDB für Event-Sourcing verwenden. Es gibt jedoch auch spezialisierte Datenbanken (sog. Event-Stores), die für Event-Sourcing optimiert sind. Diese bieten oft bessere Performance und Skalierbarkeit für das Speichern und Abrufen von Events. In unserem Beispiel könnten dann die Produkte und Benutzer in einer relationalen Datenbank bleiben, während die Events in einem speziellen Event-Store gespeichert werden.

4. **CQRS und Caching** In vielen Event-Sourcing-Systemen wird das CQRS-Muster (Command Query Responsibility Segregation) verwendet. Dabei werden die Schreib- und Leseoperationen auf unterschiedliche Modelle und Datenbanken aufgeteilt. Für das Schreiben werden die Events in den Event-Store geschrieben, während für das Lesen ein optimiertes Lese-Modell (z.B. eine denormalisierte Ansicht) verwendet wird, das regelmäßig aus den Events generiert und aktualisiert wird. Dadurch können Leseoperationen sehr schnell durchgeführt werden, ohne dass alle Events (nochmal) verarbeitet werden müssen. Zusätzlich kann Caching auf diesen Lese-Modelle anwenden, um die Performance für häufig abgefragte Daten weiter zu verbessern. In unserem Beispiel könnte das Lese-Modell eine denormalisierte Tabelle sein, die den aktuellen Zustand des Warenkorbs für jeden Benutzer speichert. Diese Warenkorb-Tabelle wird bei jedem Event aktualisiert. Sodass beim Aufruf von `GET /cart` schon alle Events verarbeitet wurden und der Datenbankeintrag sehr schnell abgerufen und an den Client zurückgegeben werden kann.

Diese Techniken können helfen, Event-Sourcing auch in größeren Systemen einzusetzen. Aber sie bringen zusätzliche Komplexität mit sich und müssen sorgfältig implementiert werden. Für viele Anwendungsfälle ist CRUD einfach die bessere Wahl.

## Event-Sourcing mit AWS

Wenn du Event-Sourcing mit AWS umsetzen möchtest, habe ich einen separaten Artikel geschrieben, der eine konkrete Implementierung mit DynamoDB, Lambda und SNS zeigt: [Event-Sourcing mit AWS DynamoDB](/articles/event-sourcing-mit-aws-dynamodb). Dort stelle ich auch meine Open-Source-Bibliothek [dynamo-eventdb](https://github.com/nicograef/dynamo-eventdb) vor, die DynamoDB als Event Store kapselt und ein CDK-Setup für das Deployment mitbringt.

## Vor- und Nachteile

### Vorteile

- **Näher an der Fachdomäne**: Events können beschreiben was im Business passiert ist, nicht nur technische Zustandsänderungen. Das kann Event-Sourcing zu einer Ergänzung für Domain-Driven Design (DDD) und Event Storming machen – wenn das Team diese Konzepte bereits versteht.

- **Audit Trail**: Jede Änderung ist dokumentiert – wer hat wann was gemacht? Das kann für Compliance-Anforderungen nützlich sein, allerdings gibt es auch einfachere Wege, Audit-Logs zu implementieren.

- **Zeitreisen möglich**: Der Zustand kann für jeden beliebigen Zeitpunkt rekonstruiert werden. Das kann beim Debugging helfen – wenn man die entsprechende Tooling-Infrastruktur aufgebaut hat.

- **Keine Information geht verloren**: Der Kontext und die Absicht hinter jeder Änderung bleiben erhalten – vorausgesetzt, die Events wurden von Anfang an richtig modelliert.

- **Passt zu Event-Driven Architectures**: Event-Sourcing kann gut mit CQRS, Microservices und asynchroner Kommunikation kombiniert werden. Das bedeutet aber auch: mehr bewegliche Teile, mehr Komplexität.

- **Potenzial für Analytics**: Mit den historischen Daten lassen sich Analysen durchführen – aber das erfordert zusätzliche Entwicklung und Infrastruktur.

### Nachteile

- **Deutlich mehr Komplexität**: Event-Sourcing erfordert ein fundamentales Umdenken in der Architektur. Entwickler, die mit CRUD vertraut sind, brauchen Zeit zum Umlernen. Bugs sind schwerer zu debuggen, und die kognitive Last für das Team steigt.

- **Performance-Probleme**: Ohne zusätzliche Maßnahmen wie Snapshots oder CQRS kann das System bei vielen Events sehr langsam werden. Diese Maßnahmen wiederum bringen eigene Komplexität mit.

- **Speicherplatz-Explosion**: Events akkumulieren sich über die Zeit. Je nach Anwendungsfall können das Terabytes an Daten werden. Alte Events können nicht einfach gelöscht werden – das würde das Konzept ad absurdum führen.

- **Schema-Evolution ist schmerzhaft**: Alte Event-Schemas bleiben für immer erhalten. Änderungen müssen rückwärtskompatibel sein oder durch Upcaster migriert werden. Das kann bei lange laufenden Systemen zum echten Problem werden.

- **Eventual Consistency**: Bei CQRS-basierten Systemen ist das Lese-Modell nicht sofort aktuell. Das kann zu verwirrenden User Experiences führen und erfordert sorgfältiges UI-Design.

- **Tooling-Lücken**: Im Vergleich zu relationalen Datenbanken ist das Tooling für Event-Sourcing weniger ausgereift. Debugging, Monitoring und Operations sind aufwändiger.

- **Overkill für die meisten Anwendungen**: Seien wir ehrlich – die meisten CRUD-Anwendungen brauchen kein Event-Sourcing. Der Overhead lohnt sich nur, wenn die Vorteile wirklich benötigt werden.

## Fazit

Event-Sourcing ist ein interessantes Architekturmuster, das in bestimmten Szenarien Vorteile bieten kann – insbesondere wenn vollständige Nachverfolgbarkeit, komplexe Analysen oder die Integration mit Event-Driven Architectures wichtig sind.

Aber: Event-Sourcing ist kein Wundermittel. Die zusätzliche Komplexität ist erheblich, die Lernkurve steil, und für die meisten Anwendungen ist CRUD schlicht die bessere Wahl. Bevor du Event-Sourcing einführst, solltest du ehrlich prüfen, ob du die Vorteile wirklich brauchst – oder ob ein einfaches Audit-Log nicht denselben Zweck erfüllt.

Wenn du mehr über Event-Sourcing erfahren möchtest, empfehle ich dir, dich mit den Konzepten von Domain-Driven Design (DDD) und Command Query Responsibility Segregation (CQRS) auseinanderzusetzen, da diese oft in Kombination mit Event-Sourcing verwendet werden. Aber geh skeptisch an die Sache heran – viele Projekte haben sich mit Event-Sourcing übernommen.

Du möchtest sehen, wie Event-Sourcing in einem echten Projekt eingesetzt wird? [jotti](https://github.com/nicograef/jotti) ist ein einfaches Bestellsystem für kleine gastronomische Betriebe. Bestellungen, Stornierungen und Bezahlungen wurden in diesem Projekt mittels Event-Sourcing implementiert. Sieh dir den Sourcecode auf Github an: [github.com/nicograef/jotti](https://github.com/nicograef/jotti)

## Weiterführende Links

- [CQRS - Command Query Responsibility Segregation](https://martinfowler.com/bliki/CQRS.html)
- [Event Sourcing - Martin Fowler](https://martinfowler.com/eaaDev/EventSourcing.html)
- [EventSourcingDB - the native web](https://www.eventsourcingdb.io/)
- [KurrentDB](https://www.kurrent.io/) (ehemals EventStoreDB)
