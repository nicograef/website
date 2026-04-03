# Domain Driven Design — Die Brücke zwischen Fachwissen und Code

Du bist Entwickler in einem E-Commerce-Unternehmen. Die Fachabteilung sagt: "Eine Buchungsanfrage muss storniert werden." Du implementierst: `DELETE /booking-request/:id`. Die Fachabteilung testet, meldet einen Bug. Du schaust ins Log — 204 No Content, alles in Ordnung. Was ist passiert? Ihr redet aneinander vorbei.

Genau dieses Kommunikationsproblem adressiert **Domain Driven Design** — kurz **<abbr title="Domain Driven Design">DDD</abbr>**. Es ist kein Framework, keine Bibliothek und kein Architekturmuster, das man installiert. DDD ist eine Sammlung von Prinzipien, Patterns und einer Sprachphilosophie. Sie gestaltet Software so, dass sie die Fachlichkeit widerspiegelt — nicht nur verwaltet.

> Diese Einführung gibt einen Überblick über die wichtigsten DDD-Konzepte — von strategischem bis taktischem Design. Den Bounded Context und Context Mapping behandelt der Artikel [Bounded Context — Klare Grenzen für komplexe Domänen](/articles/bounded-context-erklaert) ausführlicher.

## Das Kommunikationsproblem

In vielen Projekten sprechen Fachabteilung und Entwickler zwei verschiedene Sprachen. Die Fachabteilung sagt "stornieren", der Code sagt `DELETE`. Die Fachabteilung sagt "Bestellung aufgeben", der Code sagt `POST /orders`. Die Fachabteilung sagt "Genehmigung erteilen", der Code sagt `PATCH /requests/:id { "status": "approved" }`.

Das klingt harmlos, wird aber zur Fehlerquelle. Was genau passiert beim "Stornieren"? Wird ein Eintrag gelöscht, oder bleibt er mit dem Status "storniert" erhalten? Werden offene Posten rückgängig gemacht? Bekommt der Kunde eine E-Mail? Im Code steht nur `DELETE` — die Antwort bleibt offen.

Typische Symptome, wenn dieses Problem ignoriert wird:

- Derselbe Begriff bedeutet in verschiedenen Teams etwas anderes
- Geschäftsregeln sind über Controller, Services und Datenbankschichten verstreut
- Änderungen im Fachbereich lösen unerwartete Nebeneffekte im Code aus
- Niemand kann mehr erklären, warum das System sich so verhält, wie es tut

Domain Driven Design macht das Problem sichtbar — und lösbar.

## Die Kernidee: Ubiquitous Language

Die Antwort von DDD auf das Kommunikationsproblem ist die **Ubiquitous Language** — die allgegenwärtige Sprache. Sie entsteht aus Gesprächen zwischen Fachexperten und Entwicklern und wird zur einzigen Sprache, die beide Seiten verwenden.

Die Regeln sind einfach, aber strikt:

- Begriffe aus der Ubiquitous Language tauchen im Code auf — in Klassen, Methoden, Modulen
- Wenn sich die Sprache ändert, ändert sich das Modell — und damit der Code
- Synonyme sind verboten: entweder heißt es "Buchungsanfrage" oder "Reservierungsanfrage", nicht beides

In der Praxis bedeutet das: `DELETE /booking-request` wird zu `POST /buchungsanfrage-stornieren`. Die Fachabteilung sagt "stornieren", nicht "löschen" — also macht es der Code auch so.

![Ubiquitous Language im API Design: Links technisch, rechts domänenorientiert](/assets/img/articles/ddd-api-example.png)
_Ubiquitous Language im API Design: Links eine technische, rechts eine domänenorientierte API_

Die **Domain** — das Fachgebiet, für das wir Software entwickeln — umfasst all diese Begriffe, Prozesse und Regeln. Wenn die Domain die Softwareentwicklung treibt, sprechen wir von Domain Driven Design.

## Strategisches Design: Klare Grenzen ziehen

Sobald eine Domain wächst, braucht sie Struktur. DDD teilt sie in überschaubare Einheiten auf.

Eine **Subdomain** ist ein Teilbereich des Fachgebiets mit eigenen Zielen und Verantwortlichkeiten. In einem E-Commerce-Unternehmen wären das zum Beispiel: Bestellverwaltung, Zahlungsabwicklung, Lagerverwaltung, Kundenkommunikation. Nicht alle Subdomains sind gleich wichtig:

| Typ | Bedeutung | Empfehlung |
|---|---|---|
| **Core Domain** | Kerngeschäft, Wettbewerbsvorteil | Höchste Sorgfalt, eigene Entwicklung |
| **Supporting Domain** | Unterstützt die Core Domain | Solide Umsetzung, kein Overengineering |
| **Generic Domain** | Standardlösungen (Auth, E-Mail) | Eingekaufte Software oder Open Source |

![Domain, Subdomains und Bounded Contexts im Überblick](/assets/img/articles/was-ist-domain-driven-design-strategic.png)
_Domain, Subdomains und Bounded Contexts: Vom Fachgebiet zu abgegrenzten Modellen_

Der Schritt vom Problem- in den Lösungsraum ist der **Bounded Context** — eine explizite Grenze, innerhalb derer ein bestimmtes Domänenmodell gilt. Innerhalb dieser Grenze hat jeder Begriff eine präzise Bedeutung. "Kunde" im Rechnungswesen-Kontext hat andere Attribute als "Kunde" im Marketing-Kontext — und das ist genau richtig so.

Wenn Bounded Contexts miteinander kommunizieren müssen, beschreibt eine **Context Map** die Beziehungen: Wer ist Upstream (Lieferant), wer Downstream (Konsument)? Welches Integrations-Pattern wird verwendet — [Anti-Corruption Layer](/articles/anti-corruption-layer-erklaert), Conformist, Open Host Service? Diese strategischen Entscheidungen kommen vor der Technik, nicht danach.

> Bounded Contexts, Context Mapping und Integrations-Patterns sind im Artikel [Bounded Context — Klare Grenzen für komplexe Domänen](/articles/bounded-context-erklaert) ausführlich erklärt.

## Taktisches Design: Die Bausteine der Umsetzung

Taktisches Design beschreibt, wie die Fachlichkeit innerhalb eines Bounded Context in Code übersetzt wird. DDD bietet dafür konkrete Bausteine.

### Entity

Eine **Entity** ist ein Objekt mit einer stabilen Identität über die Zeit. Ihr Zustand kann sich ändern — sie bleibt dieselbe Einheit. Eine Bestellung, ein Kunde, ein Produkt: Sie werden erkannt, weil sie eine ID haben, nicht wegen ihrer aktuellen Daten.

### Value Object

Ein **Value Object** ist ein Objekt, das nur durch seinen Wert definiert wird — ohne eigene Identität. Value Objects sind unveränderlich: Wer den Wert ändert, erstellt ein neues Objekt. Typische Beispiele sind Geldbeträge, E-Mail-Adressen oder Datumsintervalle. Zwei `Money`-Objekte mit dem Wert `100 EUR` sind identisch — unabhängig davon, welches Objekt sie im Speicher sind.

### Aggregate

Ein **Aggregate** ist ein Cluster von Entities und Value Objects, der als konsistente Einheit behandelt wird. Im Zentrum steht der **Aggregate Root** — die einzige Einheit, über die von außen auf das Aggregate zugegriffen wird. Der Aggregate Root schützt die Invarianten der gesamten Einheit.

![Aggregate-Struktur: Aggregate Root, Entities und Value Objects](/assets/img/articles/was-ist-domain-driven-design-aggregate.png)
_Aggregate-Struktur: Zugriff von außen nur über den Aggregate Root_

Ein Beispiel: `Bestellung` ist der Aggregate Root. Sie enthält `Bestellpositionen` (Entities) und einen `Gesamtbetrag` (Value Object). Die Geschäftsregel "eine bestätigte Bestellung kann nicht mehr verändert werden" wird im Aggregate Root erzwungen:

```java
public class Bestellung { // Aggregate Root
    private final BestellungId id;
    private List<Bestellposition> positionen;
    private BestellStatus status;

    public void hinzufuegen(Bestellposition position) {
        if (status.istBestaetigt()) {
            throw new DomainException("Bestätigte Bestellungen können nicht geändert werden.");
        }
        positionen.add(position);
    }

    public void bestaetigen() {
        this.status = BestellStatus.bestaetigt();
    }
}
```

### Repositories und Domain Events

Ein **Repository** ist die Schnittstelle zwischen Domänenmodell und Datenschicht. Es versteckt den Datenbankzugriff hinter domänensprachlichen Methoden: `bestellungRepository.findeNachKunde(kundenId)` statt `SELECT * FROM orders WHERE customer_id = ?`. Das Domänenmodell weiß nicht, ob dahinter SQL, NoSQL oder eine In-Memory-Struktur steckt.

**Domain Events** beschreiben, dass etwas Fachlich-Bedeutsames passiert ist: `BestellungAufgegeben`, `ZahlungEingegangen`, `LieferungVersandt`. Sie entkoppeln Teile des Systems voneinander und sind die Grundlage für [Event-Sourcing](/articles/was-ist-event-sourcing).

![Vereinfachte Onion-Architektur nach Domain-Driven Design](/assets/img/articles/ddd-onion-architecture.png)
_Die Onion-Architektur: Domänenlogik im Kern, Infrastruktur außen_

Das Domänenmodell — Entities, Value Objects, Aggregates — steht im Kern. Datenbank, API und Frontend werden darum herum gebaut. Technische Änderungen, zum Beispiel ein Datenbankwechsel, haben keinen Einfluss auf die Fachlichkeit. Und die Fachlichkeit lässt sich entwickeln und testen, ohne Infrastruktur hochzufahren.

## Fazit: Wann lohnt sich DDD?

DDD ist kein Allheilmittel. Der Aufwand für Modellierung, Abstimmung und Strukturierung lohnt sich erst ab einer gewissen Komplexität.

**DDD lohnt sich, wenn:**

- Die Domain viele Geschäftsregeln enthält, die Entwickler ohne Fachexperten nicht vollständig verstehen können
- Mehrere Teams an derselben Codebasis arbeiten
- Das System über Jahre wächst und sich verändert
- Branchen wie Finanzen, Gesundheit, Logistik oder Recht mit komplexen Prozessen beteiligt sind

**DDD lohnt sich nicht, wenn:**

- Es sich um eine einfache Datenanwendung handelt (Formular → Datenbank → Liste)
- Das Team klein ist und alle Beteiligten die Domain gut kennen
- Schnelle Lieferung wichtiger ist als langfristige Wartbarkeit

Ein guter Einstieg ist es, mit der Ubiquitous Language anzufangen — auch ohne sofort Aggregates und Bounded Contexts einzuführen. Die Fachsprache in den Code zu bringen kostet wenig, bringt aber sofort Klarheit.

## Glossar

- **Domain**: Das Fachgebiet, für das eine Software entwickelt wird — Gesamtheit aller Geschäftsprozesse, Regeln und Konzepte.
- **Ubiquitous Language**: Die gemeinsame, verbindliche Sprache von Entwicklern und Fachexperten, die sich exakt im Code widerspiegelt.
- **Subdomain**: Ein Teilbereich der Domain mit eigenen Zielen und Verantwortlichkeiten.
- **Bounded Context**: Eine explizite Grenze, innerhalb derer ein bestimmtes Domänenmodell und eine einheitliche Sprache gelten.
- **Entity**: Ein Domänenobjekt mit stabiler Identität über die Zeit.
- **Value Object**: Ein unveränderliches Objekt, das nur durch seinen Wert — nicht durch eine Identität — definiert wird.
- **Aggregate**: Ein Cluster von Entities und Value Objects, der über einen Aggregate Root als konsistente Einheit verwaltet wird.
- **Domain Event**: Ein Ereignis, das eine fachlich bedeutsame Zustandsänderung im System beschreibt.
