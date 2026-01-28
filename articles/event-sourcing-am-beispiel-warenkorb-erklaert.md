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

Event-Sourcing ist eine Alternative zu CRUD. Dabei werden nicht die aktuellen Zustände von Objekten gespeichert, sondern alle Änderungen (Events), die zu diesem Zustand geführt haben. Dies ermöglicht eine vollständige Nachverfolgbarkeit und Wiederherstellung des Systemzustands zu jedem beliebigen Zeitpunkt.

In einem Event-Sourcing-System gibt es kein UPDATE und es gibt auch kein DELETE. Genaugenommen gibt es auch kein CREATE, stattdessen gibt es nur "Write/Add Event". Selbst das Lesen (READ) funktioniert anders als bei CRUD: Anstatt den aktuellen Zustand eines Objekts direkt aus einer Datenbanktabelle abzurufen, werden alle Events zu diesem Objekt gelesen und der aktuelle Zustand durch das Anwenden dieser Events rekonstruiert.

In diesem Artikel vergleiche ich Event-Sourcing mit dem traditionellen CRUD-Ansatz anhand des Beispiels Warenkorb in einem Online-Shop.

> **Disclaimer:** Event-Sourcing hat — wie alles — seine Vor- und Nachteile und sollte nur angewendet werden, wenn es gute Gründe dafür gibt. Auch wenn dieser Artikel den Warenkorb als Beispiel für Event-Sourcing verwendet, kommt man in den meisten Fällen sicher auch gut ohne Event-Sourcing aus.

## Warenkorb mit CRUD

Für die CRUD-Implementierung nehmen wir eine relationale Datenbank und ein REST-like Backend, um CRUD-Operationen durchzuführen.

Stellen wir uns vor, es gibt eine Tabelle `warenkorb` mit den Spalten `benutzer_id`, `produkt_id` und `menge`. Wenn ein Benutzer ein Produkt zu seinem Warenkorb hinzufügt, wird ein neuer Eintrag in der Tabelle erstellt (CREATE). Wenn der Benutzer die Menge eines Produkts ändert, wird der entsprechende Eintrag aktualisiert (UPDATE). Wenn der Benutzer ein Produkt entfernt, wird der Eintrag gelöscht (DELETE). Wir speichern also immer den aktuellen Zustand des Warenkorbs. Um den aktuellen Zustand des Warenkorbs zu ermitteln, müssen wir einfach die Einträge in der Tabelle für den jeweiligen Benutzer abfragen (READ). Das Datenbankmodell könnte also so aussehen:

```sql
CREATE TABLE benutzer (
    id INT PRIMARY KEY,
    name VARCHAR(100)
);

CREATE TABLE produkt (
    id INT PRIMARY KEY,
    name VARCHAR(100),
    preis DECIMAL(10, 2)
);

CREATE TABLE warenkorb (
    benutzer_id INT,
    produkt_id INT,
    menge INT,
    PRIMARY KEY (benutzer_id, produkt_id),
    FOREIGN KEY (produkt_id) REFERENCES produkt(id),
    FOREIGN KEY (benutzer_id) REFERENCES benutzer(id)
);
```

Das Backend könnte dann folgende Endpunkte bereitstellen:

| Methode  | Endpunkt                  | Beschreibung                |
| -------- | ------------------------- | --------------------------- |
| `POST`   | `/warenkorb`              | Produkt hinzufügen (CREATE) |
| `PATCH`  | `/warenkorb/produkt/{id}` | Menge ändern (UPDATE)       |
| `DELETE` | `/warenkorb/produkt/{id}` | Produkt entfernen (DELETE)  |
| `GET`    | `/warenkorb`              | Warenkorb abrufen (READ)    |

Dieses System würde gut funktionieren und die oberflächlichen Anforderungen eines Warenkorbs erfüllen. Allerdings gibt es auch einige Fragen, die man mit diesem Ansatz nicht beantworten kann, wie z.B.:

- Welche Produkte werden oft in den Warenkorb gelegt aber dann doch nicht bestellt (wieder entfernt)?
- Wie oft wird die Menge eines Produktes reduziert oder erhöht?
- Wie oft versuchen Benutzer ein Produkt zu bestellen, das nicht mehr verfügbar ist (und deshalb wieder aus dem Warenkorb entfernt wurde)?
- Welche Produkte verweilen am längsten im Warenkorb, bevor sie gekauft oder entfernt werden?
- Wird der Warenkorb als Merkliste verwendet? Sollten wir eine separate Merkliste-Funktion anbieten?
- Wie sieht der Warenkorb-Verlauf von Benutzern aus, die viel bei uns bestellen?
- ... und wie unterscheidet dieser sich von dem Verlauf von Benutzern, die wenig bestellen?

Wenn solche Fragen gestellt werden und ein CRUD-System im Einsatz ist, wird oft (nachträglich) versucht, diese Informationen durch zusätzliche Statistik-Tabellen, Historie-Tabellen, Log-Tabellen oder Audit-Logs zu erfassen. Dies führt jedoch oft zu komplexen und schwer wartbaren Systemen.

Außerdem geht durch die Kombination von CRUD-System und REST-API fast immer der Kontext der Änderung, die Fachlichkeit hinter dem CREATE/UPDATE/DELETE oder die Absicht des Benutzers verloren. Wenn das System komplexer wird, gibt es möglicherweise viele verschiedene Gründe, die zu einem `POST /warenkorb` Aufruf führen können (z.B. "Produkt hinzufügen um direkt zu kaufen", "Produkt wieder hinzufügen weil versehentlich entfernt", "Produkt für später vormerken", etc.). Diese verschiedenen Gründe können jedoch nicht mehr unterschieden werden, wenn nur der aktuelle Zustand des Warenkorbs gespeichert wird und selbst das Backend nur CREATE, UPDATE und DELETE kennt.

Genau hier setzt Event-Sourcing an.

## Warenkorb mit Event-Sourcing

In der Event-Sourcing-Implementierung speichern wir alle Änderungen am Warenkorb als Events. Jedes Event beschreibt eine einzelne Änderung am Warenkorb und enthält alle notwendigen Informationen, um diese Änderung zu verstehen.

Wir verwenden wieder eine relationale Datenbank, die Backend-API soll diesmal jedoch im Command-Style gebaut werden (ähnlich zu RPC und CQRS).

An den Tabellen `benutzer` und `produkt` ändern wir nichts. Diese bleiben gleich wie bei der CRUD-Implementierung. Das Event-Sourcing wird in diesem Beispiel nur auf den Warenkorb angewendet. Dafür lösen wir uns von der `warenkorb`-Tabelle und erstellen stattdessen eine Tabelle `event`. Dort speichern wir alle Events inklusive dem Benutzer, dem Zeitstempel, der Art des Events und den dazugehörigen Daten.

> **Was ist ein Event?**
> Ein Event beschreibt ein Ereignis, das im System stattgefunden hat. Events sind unveränderliche Fakten, die das beschreiben, was passiert ist. Bei deinem Bankkonto könnte es zum Beispiel diese Events geben: "Überweisung wurde durchgeführt" oder "Bargeld wurde abgehoben" sein. Im Fall des Warenkorbs könnten Events z.B. "Produkt wurde dem Warenkorb hinzugefügt", "Produkt wurde aus dem Warenkorb entfernt" oder "Menge eines Produkts wurde geändert" sein. Ein Event beinhaltet neben dem Event-Typ (z.B. "produkt-hinzugefuegt") auch immer einen Zeitstempel und alle relevanten Informationen, die dieses Ereignis beschreiben (z.B. Produkt-ID, Menge, Benutzer-ID etc.).

Das Datenbankmodell könnte so aussehen:

```sql
CREATE TABLE benutzer (
    id INT PRIMARY KEY,
    name VARCHAR(100)
);

CREATE TABLE produkt (
    id INT PRIMARY KEY,
    name VARCHAR(100),
    preis DECIMAL(10, 2)
);

CREATE TABLE event (
    id INT PRIMARY KEY,
    type VARCHAR(100),
    data JSON,
    timestamp TIMESTAMP
);
```

Jedes Mal, wenn ein Benutzer ein Produkt zu seinem Warenkorb hinzufügt, wird ein neues Event in die Tabelle geschrieben. Wenn der Benutzer die Menge eines Produkts ändert, wird ebenfalls ein neues Event geschrieben. Wenn der Benutzer ein Produkt entfernt, wird auch dafür ein neues Event geschrieben.

Das Backend könnte dann folgende Endpunkte bereitstellen:

| Methode | Endpunkt                         | Event                     |
| ------- | -------------------------------- | ------------------------- |
| `POST`  | `/warenkorb/produkt-hinzufuegen` | `produkt-hinzugefuegt`    |
| `POST`  | `/warenkorb/produkt-entfernen`   | `produkt-entfernt`        |
| `POST`  | `/warenkorb/menge-aendern`       | `menge-geaendert`         |
| `GET`   | `/warenkorb`                     | _(rekonstruiert Zustand)_ |

Die Events in der Tabelle könnten z.B. so aussehen:

| id  | type                 | data                                                  | timestamp           |
| --- | -------------------- | ----------------------------------------------------- | ------------------- |
| 1   | produkt-hinzugefuegt | { "benutzerId": 1, "produktId": 123, "menge": 2 }     | 2025-01-23 10:00:00 |
| 2   | produkt-entfernt     | { "benutzerId": 2, "produktId": 456 }                 | 2025-01-23 10:02:00 |
| 3   | menge-geaendert      | { "benutzerId": 1, "produktId": 123, "neueMenge": 1 } | 2025-01-23 10:03:00 |
| 4   | produkt-hinzugefuegt | { "benutzerId": 1, "produktId": 456, "menge": 2 }     | 2025-01-23 10:03:20 |
| 5   | produkt-entfernt     | { "benutzerId": 1, "produktId": 123 }                 | 2025-01-23 10:04:00 |
| 6   | produkt-hinzugefuegt | { "benutzerId": 3, "produktId": 789, "menge": 3 }     | 2025-01-23 10:05:00 |
| 7   | menge-geaendert      | { "benutzerId": 1, "produktId": 456, "neueMenge": 3 } | 2025-01-23 10:09:30 |

Um den aktuellen Zustand des Warenkorbs zu ermitteln, müssen wir alle Events für den jeweiligen Benutzer lesen und diese der Reihe nach anwenden. Was passiert also, wenn wir `GET /warenkorb` für Benutzer 1 aufrufen?

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
{ "benutzerId": 1, "warenkorb": [{ "produktId": 456, "menge": 3 }] }
```

Wir können mit Event-Sourcing also die gleiche Funktionalität liefern, wie mit dem CRUD-Ansatz. Aber zusätzlich können wir jetzt auch alle zuvor gestellten Fragen beantworten, da wir alle Events gespeichert haben.

- Welche Produkte werden oft in den Warenkorb gelegt aber dann doch nicht bestellt?
  - Wir können alle "produkt-hinzugefuegt" Events zählen und die entsprechenden "produkt-entfernt" Events dagegenstellen, um diese Information zu erhalten.
  - Bei Benutzer 1 sehen wir, dass Produkt 123 hinzugefügt und später entfernt wurde.
- Wie oft wird die Menge eines Produktes reduziert oder erhöht?
  - Wir können alle "menge-geaendert" Events analysieren, um zu sehen, wie oft die Menge geändert wurde und in welche Richtung.
- Welche Produkte verweilen am längsten im Warenkorb, bevor sie gekauft oder entfernt werden?
  - Wenn wir ein Zeitstempel für die Bestellung oder sogar ein Bestell-Event haben, können die Zeitstempel der "produkt-hinzugefuegt" und "warenkorb-bestellt" Events vergleichen, um die Verweildauer zu berechnen.

Da wir alle Events speichern, können wir Analysen durchführen, um das Verhalten der Benutzer besser zu verstehen. Ebenso eröffnen sich viele weitere Möglichkeiten: Sagen wir die Marketing-Abteilung kommt auf die Idee, Benutzern personalisierte Angebote zu machen, basierend auf den Produkten, die sie häufig in den Warenkorb legen, aber nicht kaufen. Mit Event-Sourcing können wir diese Funktion schnell bauen und sofort allen Benutzern personalisierte Angebote zusenden. Mit der CRUD-Implementierung wäre das nicht so einfach möglich. Man müsste das System erst erweitern und kann selbst dann nur mit den zukünftigen Daten arbeiten, nicht aber mit den historischen Daten.

## Wird das System nicht langsam?

Vielleicht fragst du dich jetzt, ob das System nicht langsam wird, wenn bei jeder Abfrage erst alle Events aus der Datenbank geladen und der Zustand rekonstruiert werden muss. Zumindest war das meine erste Reaktion, als ich das Konzept von Event-Sourcing zum ersten Mal gehört habe. Theoretisch kann das auch passieren, wenn man bei dieser einfachen Implementierung bleibt und das System und die Datenmenge sehr groß werden. In der Praxis stellt Performance jedoch selten ein Problem dar, da es verschiedene Techniken gibt, dieses Problem zu lösen.

1. **Event-Modellierung** Durch die richtige Modellierung der Events kann die Anzahl der zu verarbeitenden Events reduziert werden. Zum Beispiel könnte man in unserem Beispielsystem jedesmal einen neuen Warenkorb "aufmachen", sobald der Benutzer den Warenkorb abschickt (z.B. durch eine Bestellung). Dadurch werden die Events für jeden abgeschlossenen Warenkorb getrennt gespeichert und es müssen nicht jedesmal alle Events eines Benutzers verarbeitet werden, sondern nur die Events des aktuellen Warenkorbs. In diesem Modell würde jeder Warenkorb eine eigene ID bekommen und die Events würden diese ID referenzieren. Ich würde mal behaupten, dass dadurch maximal 30-50 Events pro Warenkorb anfallen. Diese Anzahl von Events sollten problemlos vom Backend in Echtzeit verarbeitet werden können.

2. **Snapshots** Eine weitere Technik ist die Verwendung von Snapshots. Dabei wird in regelmäßigen Abständen der aktuelle Zustand des Objekts gespeichert (z.B. alle 100 Events oder nach bestimmten Ereignissen). Wenn der Zustand abgerufen werden muss, wird zuerst der letzte Snapshot geladen und dann nur die Events seit diesem Snapshot angewendet. Dadurch reduziert sich die Anzahl der zu verarbeitenden Events erheblich. Die historischen Events bleiben jedoch weiterhin erhalten und können für Analysen verwendet werden. In unserem Beispiel könnte ein Cronjob jede Nacht einen Snapshot erzeugen, in dem eine Liste aller Produkte aufgeführt ist, die von Benutzern in ihren Warenkorb hinzugefügt und danach wieder entfernt wurden (inklusive der Häufigkeit). Mit diesem Snapshot könnte dann die Analyse deutlich schneller durchgeführt werden.

3. **Optimierte Datenbanken** In unserem Beispiel haben wir eine relationale Datenbank für die Speicherung verwendet. Ich selbst habe dafür in einem Projekt PostgreSQL verwendet. Ebenso kann man bei kleinen Anwendungen NoSQL-Datenbanken wie MongoDB oder AWS DynamoDB für Event-Sourcing verwenden. Es gibt jedoch auch spezialisierte Datenbanken (sog. Event-Stores), die für Event-Sourcing optimiert sind. Diese bieten oft bessere Performance und Skalierbarkeit für das Speichern und Abrufen von Events. In unserem Beispiel könnten dann die Produkte und Benutzer in einer relationalen Datenbank bleiben, während die Events in einem speziellen Event-Store gespeichert werden.

4. **CQRS und Caching** In vielen Event-Sourcing-Systemen wird das CQRS-Muster (Command Query Responsibility Segregation) verwendet. Dabei werden die Schreib- und Leseoperationen auf unterschiedliche Modelle und Datenbanken aufgeteilt. Für das Schreiben werden die Events in den Event-Store geschrieben, während für das Lesen ein optimiertes Lese-Modell (z.B. eine denormalisierte Ansicht) verwendet wird, das regelmäßig aus den Events generiert und aktualisiert wird. Dadurch können Leseoperationen sehr schnell durchgeführt werden, ohne dass alle Events (nochmal) verarbeitet werden müssen. Zusätzlich kann Caching auf diesen Lese-Modelle anwenden, um die Performance für häufig abgefragte Daten weiter zu verbessern. In unserem Beispiel könnte das Lese-Modell eine denormalisierte Tabelle sein, die den aktuellen Zustand des Warenkorbs für jeden Benutzer speichert. Diese Warenkorb-Tabelle wird bei jedem Event aktualisiert. Sodass beim Aufruf von `GET /warenkorb` schon alle Events verarbeitet wurden und der Datenbankeintrag sehr schnell abgerufen und an den Client zurückgegeben werden kann.

Durch diese Techniken kann Event-Sourcing auch in großen Systemen mit vielen Benutzern und einer großen Anzahl von Events performant umgesetzt werden.

## Fazit

Event-Sourcing bietet viele Vorteile gegenüber dem traditionellen CRUD-Ansatz, insbesondere wenn es darum geht, komplexe Geschäftslogiken zu modellieren und historische Daten für Analysen zu nutzen. Durch das Speichern aller Änderungen als Events können wir den Kontext und die Absicht hinter jeder Änderung bewahren und so ein tieferes Verständnis für das Verhalten der Benutzer gewinnen.

Natürlich ist Event-Sourcing nicht für jedes System die beste Wahl. Es bringt zusätzliche Komplexität mit sich und erfordert ein Umdenken in der Art und Weise, wie wir Daten modellieren und Systeme entwerfen. Dennoch lohnt es sich, Event-Sourcing als Alternative zu CRUD in Betracht zu ziehen, insbesondere wenn die Anforderungen an Nachverfolgbarkeit, Analyse und Flexibilität steigen.

Wenn du mehr über Event-Sourcing erfahren möchtest, empfehle ich dir, dich mit den Konzepten von Domain-Driven Design (DDD) und Command Query Responsibility Segregation (CQRS) auseinanderzusetzen, da diese oft in Kombination mit Event-Sourcing verwendet werden.

Du möchtest sehen, wie Event-Sourcing in einem echten Projekt eingesetzt wird? [jotti](https://github.com/nicograef/jotti) ist ein einfaches Bestellsystem für kleine gastronomische Betriebe. Bestellungen, Stornierungen und Bezahlungen wurden in diesem Projekt mittels Event-Sourcing implementiert. Sieh dir den Sourcecode auf Github an: [github.com/nicograef/jotti](https://github.com/nicograef/jotti)

## Weiterführende Links

- [CQRS, Event-Sourcing und DDD erklärt](https://cqrs.com/)
- [CQRS - Command Query Responsibility Segregation](https://martinfowler.com/bliki/CQRS.html)
- [Event Sourcing - Martin Fowler](https://martinfowler.com/eaaDev/EventSourcing.html)
- [Event-Sourcing - thenativeweb](https://eventsourcingdatabase.com/)
- [EventSourcingDB](https://thenativeweb.io/products/eventsourcingdb)
- [KurrentDB](https://eventstore.com/)
