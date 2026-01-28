---
title: Event-Sourcing mit AWS DynamoDB
description: Eine praktische Implementierung von Event-Sourcing mit AWS DynamoDB, Lambda und SNS. Mit CDK-Setup und TypeScript Service-Klasse.
author: Nico Gräf
author_url: https://nicograef.com
date: 2025-01-28
tags:
  - Software Architecture
  - Event-Sourcing
  - AWS
  - DynamoDB
  - CDK
  - TypeScript
---

# Event-Sourcing mit AWS DynamoDB

> Dieser Artikel baut auf meinem vorherigen Artikel [Event-Sourcing am Beispiel Warenkorb erklärt](/articles/event-sourcing-am-beispiel-warenkorb-erklaert) auf. Dort habe ich die Grundlagen von Event-Sourcing erklärt und gezeigt, wie Events statt Zuständen gespeichert werden. In diesem Artikel zeige ich eine konkrete Implementierung mit AWS DynamoDB.

## Warum DynamoDB als Event Store?

Für Cloud-native Anwendungen auf AWS bietet DynamoDB einige interessante Eigenschaften für Event-Sourcing:

- **Serverless und skalierbar**: Kein Infrastruktur-Management notwendig, Pay-per-Request-Abrechnung möglich
- **DynamoDB Streams**: Ermöglicht reaktive Architekturen durch automatische Benachrichtigungen bei neuen Events
- **Hohe Schreibgeschwindigkeit**: Optimiert für append-only Workloads
- **Integration mit Lambda und SNS**: Events können automatisch an andere Services weitergeleitet werden

Allerdings gibt es auch Einschränkungen: DynamoDB ist keine spezialisierte Event-Store-Datenbank. Komplexe Queries über mehrere Partitions hinweg sind schwierig, und das Kostenmodell kann bei hohem Durchsatz teuer werden. Für kleinere bis mittlere Anwendungen oder wenn bereits eine AWS-Infrastruktur vorhanden ist, kann DynamoDB aber eine pragmatische Wahl sein.

## Das Dynamo-EventDB Projekt

Ich habe eine TypeScript-Bibliothek entwickelt, die DynamoDB als Event Store kapselt: [dynamo-eventdb](https://github.com/nicograef/dynamo-eventdb). Das Projekt besteht aus:

- **Service-Klasse (`EventDB`)**: Zum Schreiben und Lesen von Events
- **CDK-Konstrukte**: Für das automatische Deployment der AWS-Infrastruktur
- **CloudEvents-Kompatibilität**: Events folgen dem [CloudEvents-Standard](https://cloudevents.io/) der CNCF

## Architektur-Überblick

Die Architektur besteht aus folgenden Komponenten:

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  Backend    │────▶│  DynamoDB   │────▶│   Lambda    │────▶│    SNS      │
│  Service    │     │  (Events)   │     │  (Publish)  │     │   Topic     │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
                           │                                       │
                           │                                       ▼
                           │                               ┌─────────────┐
                           │                               │ Subscriber  │
                           │                               │  Services   │
                           ▼                               └─────────────┘
                    ┌─────────────┐
                    │   Query     │
                    │  (Read)     │
                    └─────────────┘
```

1. **Backend Service**: Schreibt Events in DynamoDB
2. **DynamoDB**: Speichert die Events, triggert Stream bei INSERT
3. **Lambda (Publisher)**: Liest neue Events aus dem Stream und publiziert sie auf SNS
4. **SNS Topic**: Verteilt Events an interessierte Subscriber
5. **Subscriber Services**: Reagieren auf Events (z.B. Read-Models aktualisieren)

## Das DynamoDB-Tabellendesign

Ein Event Store mit DynamoDB erfordert ein durchdachtes Tabellendesign. Das Schema sieht so aus:

| Attribut    | Typ    | Beschreibung                                  |
| ----------- | ------ | --------------------------------------------- |
| `subject`   | String | Partition Key – die betroffene Entität        |
| `time_type` | String | Sort Key – Kombination aus Timestamp und Type |
| `id`        | String | Eindeutige Event-ID (UUID)                    |
| `type`      | String | Event-Typ (z.B. `book.borrowed`)              |
| `source`    | String | Herkunft des Events (URI)                     |
| `time`      | Number | Timestamp als Unix-Millisekunden              |
| `data`      | Map    | Event-Payload                                 |
| `pk_all`    | String | Konstanter Wert "all" für globale Queries     |

### Warum dieser Sort Key?

Der Sort Key `time_type` ist eine Kombination aus Timestamp und Event-Type: `{timestamp}_{type}`. Das löst ein wichtiges Problem: Wenn zwei Events für dasselbe Subject zur exakt gleichen Millisekunde auftreten, würden sie sich bei einem reinen Timestamp-Sort-Key überschreiben. Die Kombination macht den Sort Key eindeutig.

### Indizes

Die Tabelle verwendet mehrere Indizes für verschiedene Abfragemuster:

```typescript
globalSecondaryIndexes: [
  {
    // Alle Events chronologisch sortiert
    indexName: 'AllEventsByTime',
    partitionKey: { name: 'pk_all', type: AttributeType.STRING },
    sortKey: { name: 'time', type: AttributeType.NUMBER },
  },
  {
    // Ein Event per ID abrufen
    indexName: 'AllEventsById',
    partitionKey: { name: 'id', type: AttributeType.STRING },
  },
],
localSecondaryIndexes: [
  {
    // Alle Events eines Typs für ein Subject
    indexName: 'SubjectEventsByType',
    sortKey: { name: 'type', type: AttributeType.STRING },
  },
],
```

## Die EventDB Service-Klasse

Die `EventDB`-Klasse kapselt alle Operationen auf dem Event Store:

### Initialisierung

```typescript
import { DynamoDBClient } from "@aws-sdk/client-dynamodb";
import { EventDB } from "@nicograef/dynamo-eventdb";

const dynamoClient = new DynamoDBClient({});
const eventDB = EventDB.instance(console, dynamoClient, "BookEvents");
```

### Events schreiben

Es gibt zwei Methoden zum Schreiben von Events:

```typescript
// Option 1: Event mit automatisch generierter ID und Timestamp
const events = await eventDB.addNewEvents([
  {
    source: "https://library.example.com",
    type: "book.borrowed",
    subject: "book:123",
    data: { userId: "user:456", dueDate: "2025-02-15" },
  },
]);

// Option 2: Bereits vollständige Events hinzufügen
await eventDB.addEvents([
  {
    id: "evt-abc-123",
    time: new Date(),
    source: "https://library.example.com",
    type: "book.returned",
    subject: "book:123",
    data: { userId: "user:456" },
  },
]);
```

### Events lesen

Die wichtigste Query ist das Abrufen aller Events für ein Subject:

```typescript
// Alle Events für ein Buch abrufen
const { validItems, invalidItems } =
  await eventDB.fetchEventsForSubject("book:123");

// Events chronologisch anwenden, um aktuellen Zustand zu rekonstruieren
let bookState = { available: true, borrower: null };
for (const event of validItems) {
  switch (event.type) {
    case "book.borrowed":
      bookState = { available: false, borrower: event.data.userId };
      break;
    case "book.returned":
      bookState = { available: true, borrower: null };
      break;
  }
}
```

Weitere Query-Methoden:

```typescript
// Alle Events (global)
const allEvents = await eventDB.fetchAllEvents();

// Events für Subject gefiltert nach Typ
const borrowEvents = await eventDB.fetchEventsForSubjectAndType(
  "book:123",
  "book.borrowed",
);

// Ein einzelnes Event per ID
const event = await eventDB.fetchEvent("evt-abc-123");
```

## CloudEvents-Format

Events folgen dem CloudEvents-Standard der CNCF. Die Validierung erfolgt mit Zod:

```typescript
const EventSchema = z.object({
  id: z.string().min(3),
  type: z.string().min(3),
  subject: z.string().min(3),
  source: z.string().min(3),
  time: z.date(),
  data: z.record(z.string(), z.unknown()),
});
```

Ein Event sieht dann so aus:

```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "type": "book.borrowed",
  "subject": "book:123",
  "source": "https://library.example.com",
  "time": "2025-01-28T14:30:00.000Z",
  "data": {
    "userId": "user:456",
    "dueDate": "2025-02-15"
  }
}
```

## Die Publisher-Lambda

Wenn ein Event in DynamoDB geschrieben wird, triggert DynamoDB Streams eine Lambda-Funktion. Diese publiziert das Event auf ein SNS Topic:

```typescript
export class Publisher {
  public async processRecord(record: DynamoDBRecord): Promise<void> {
    // Nur INSERT-Events verarbeiten (append-only!)
    if (record.eventName !== "INSERT") {
      this.logger.error(`Unexpected event name ${record.eventName}`);
      return;
    }

    // DynamoDB-Record in Event umwandeln
    const dynamoEvent = unmarshall(record.dynamodb.NewImage);
    const { event, error } = Cloudevent.fromDynamo(dynamoEvent);
    if (error) {
      this.logger.error("Failed to parse event", { error });
      return;
    }

    // Auf SNS publizieren
    await this.publish(event);
  }

  private async publish(event: Event): Promise<void> {
    await this.sns.send(
      new PublishCommand({
        TopicArn: this.config.topicArn,
        Message: JSON.stringify(event),
        MessageAttributes: {
          id: { DataType: "String", StringValue: event.id },
          type: { DataType: "String", StringValue: event.type },
          time: { DataType: "String", StringValue: event.time.toISOString() },
        },
      }),
    );
  }
}
```

Die `MessageAttributes` ermöglichen es Subscribern, nach Event-Typ zu filtern, ohne die Message zu parsen.

## CDK-Deployment

Das gesamte Setup kann mit AWS CDK deployed werden:

```typescript
import { EventDB } from "@nicograef/dynamo-eventdb/cdk";

const app = new App();

new EventDB(app, "LibraryEvents", {
  isProdEnv: true,
  subscriptionAccounts: ["123456789012"], // Andere AWS-Accounts können subscriben
  encryptionKey: myKmsKey, // Optional: KMS-Verschlüsselung
});
```

Das CDK-Construct erstellt automatisch:

- **DynamoDB-Tabelle** mit allen Indizes und DynamoDB Streams
- **SNS Topic** für Event-Distribution
- **Lambda-Funktion** als Stream-Handler
- **IAM-Policies** für alle Berechtigungen
- **Log-Groups** mit konfigurierbarer Retention

### Prod vs. Non-Prod Konfiguration

```typescript
// Non-Prod: Ressourcen werden bei Stack-Löschung entfernt
new EventDB(app, 'Events', { isProdEnv: false, ... });

// Prod: DynamoDB-Tabelle bleibt erhalten, längere Log-Retention
new EventDB(app, 'Events', { isProdEnv: true, ... });
```

## Praktisches Beispiel: Bibliothekssystem

Hier ein vollständiges Beispiel für ein Bibliothekssystem:

```typescript
import { DynamoDBClient } from "@aws-sdk/client-dynamodb";
import { EventDB, type EventCandidate } from "@nicograef/dynamo-eventdb";

// Event-Typen definieren
type BookBorrowed = EventCandidate & {
  type: "book.borrowed";
  data: { userId: string; dueDate: string };
};

type BookReturned = EventCandidate & {
  type: "book.returned";
  data: { userId: string };
};

type BookEvent = BookBorrowed | BookReturned;

// Service initialisieren
const eventDB = EventDB.instance(
  console,
  new DynamoDBClient({}),
  "LibraryEvents",
);

// Buch ausleihen
async function borrowBook(bookId: string, userId: string, dueDate: Date) {
  const event: BookBorrowed = {
    source: "https://library.example.com",
    type: "book.borrowed",
    subject: `book:${bookId}`,
    data: { userId, dueDate: dueDate.toISOString() },
  };
  await eventDB.addNewEvents([event]);
}

// Buch zurückgeben
async function returnBook(bookId: string, userId: string) {
  const event: BookReturned = {
    source: "https://library.example.com",
    type: "book.returned",
    subject: `book:${bookId}`,
    data: { userId },
  };
  await eventDB.addNewEvents([event]);
}

// Aktuellen Status eines Buchs ermitteln
async function getBookStatus(bookId: string) {
  const { validItems } = await eventDB.fetchEventsForSubject(`book:${bookId}`);

  let status = {
    available: true,
    borrower: null as string | null,
    dueDate: null as string | null,
  };

  for (const event of validItems) {
    if (event.type === "book.borrowed") {
      status = {
        available: false,
        borrower: event.data.userId as string,
        dueDate: event.data.dueDate as string,
      };
    } else if (event.type === "book.returned") {
      status = { available: true, borrower: null, dueDate: null };
    }
  }

  return status;
}

// Ausleihhistorie eines Buchs
async function getBorrowHistory(bookId: string) {
  const { validItems } = await eventDB.fetchEventsForSubjectAndType(
    `book:${bookId}`,
    "book.borrowed",
  );

  return validItems.map((event) => ({
    userId: event.data.userId,
    borrowedAt: event.time,
    dueDate: event.data.dueDate,
  }));
}
```

## Lokale Entwicklung

Für lokale Tests kann DynamoDB Local verwendet werden:

```bash
docker run -p 8000:8000 amazon/dynamodb-local
```

```typescript
const dynamoClient = new DynamoDBClient({
  endpoint: "http://localhost:8000",
});

// Tabelle erstellen (nur für lokale Entwicklung)
await EventDB.createTable(dynamoClient, "TestEvents");
```

## Grenzen dieser Implementierung

Diese Implementierung ist für einfache bis mittlere Anwendungsfälle gedacht. Folgende Punkte sollte man beachten:

- **Keine Snapshots**: Bei sehr langen Event-Streams wird das Replay langsam
- **Keine optimistic concurrency**: Parallele Writes könnten zu Race Conditions führen
- **Partition-Limits**: DynamoDB hat Limits für Schreiboperationen pro Partition (1000 WCU/s)
- **Kosten**: Bei vielen Events können die DynamoDB- und Lambda-Kosten steigen
- **Vendor Lock-in**: Die Lösung ist stark an AWS gebunden

Für komplexere Anforderungen sollte man spezialisierte Event Stores wie [KurrentDB](https://www.kurrent.io/) (ehemals EventStoreDB) oder [EventSourcingDB](https://www.eventsourcingdb.io/) in Betracht ziehen.

## Fazit

AWS DynamoDB kann als pragmatischer Event Store für kleinere bis mittlere Event-Sourcing-Anwendungen dienen – besonders wenn bereits eine AWS-Infrastruktur vorhanden ist. Die Kombination mit DynamoDB Streams, Lambda und SNS ermöglicht reaktive, event-driven Architekturen.

Die hier vorgestellte Bibliothek [dynamo-eventdb](https://github.com/nicograef/dynamo-eventdb) bietet eine solide Grundlage mit CloudEvents-Kompatibilität und CDK-Support. Für produktionskritische Systeme mit hohen Anforderungen an Konsistenz und Performance sollte man aber spezialisierte Event-Store-Datenbanken evaluieren.

## Links

- [GitHub: nicograef/dynamo-eventdb](https://github.com/nicograef/dynamo-eventdb)
- [CloudEvents Specification](https://cloudevents.io/)
- [AWS CDK Documentation](https://docs.aws.amazon.com/cdk/v2/guide/home.html)
- [Grundlagen: Event-Sourcing am Beispiel Warenkorb erklärt](/articles/event-sourcing-am-beispiel-warenkorb-erklaert)
