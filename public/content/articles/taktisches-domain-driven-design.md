# Taktisches Domain Driven Design: Fachlogik im Code abbilden

> **Entwurf** — Dieser Artikel ist ein Draft und noch nicht veröffentlicht.

Du hast die Ubiquitous Language definiert und weißt, welche Bounded Contexts dein System hat. Jetzt stellt sich die Frage: Wie sieht der Code innerhalb eines Kontexts aus? Taktisches DDD liefert dafür fünf Bausteine.

## Entity: Identität über die Zeit

Eine Entity ist ein Objekt, das durch seine Identität definiert wird, nicht durch seine aktuellen Daten. Eine Bestellung bleibt dieselbe Bestellung, auch wenn sich ihr Status von "offen" auf "versandt" ändert. Das macht die Bestell-ID, nicht der aktuelle Zustand.

## Value Object: Wert ohne Identität

Ein Value Object hat keine eigene Identität. Es ist vollständig durch seinen Wert definiert und unveränderlich. Zwei Geldbeträge von 100 EUR sind identisch, egal wo sie im Speicher liegen. Wer den Wert ändern will, erstellt ein neues Objekt. Typische Value Objects: Geldbeträge, E-Mail-Adressen, Adressen, Datumsintervalle.

## Aggregate: Die Konsistenzgrenze

Ein Aggregate ist ein Cluster aus Entities und Value Objects, der als Einheit behandelt wird. Im Zentrum steht der Aggregate Root: der einzige Zugriffspunkt von außen.

Beispiel: Eine `Bestellung` (Aggregate Root) enthält `Bestellpositionen` (Entities) und einen `Gesamtbetrag` (Value Object). Die Geschäftsregel "bestätigte Bestellungen dürfen nicht mehr geändert werden" wird direkt im Aggregate Root durchgesetzt, nicht in einem Service oder Controller.

```java
public class Bestellung {
    private final BestellungId id;
    private List<Bestellposition> positionen;
    private BestellStatus status;

    public void hinzufuegen(Bestellposition position) {
        if (status.istBestaetigt()) {
            throw new DomainException(
                "Bestätigte Bestellungen können nicht geändert werden."
            );
        }
        positionen.add(position);
    }
}
```

Von außen greifst du nur auf den Aggregate Root zu. Die interne Struktur (welche Positionen, welcher Status) bleibt seine Sache.

## Repository: Domäne trifft Datenbank

Ein Repository versteckt die Datenschicht hinter domänensprachlichen Methoden. Statt `SELECT * FROM orders WHERE customer_id = ?` schreibst du `bestellungRepository.findeNachKunde(kundenId)`. Das Domänenmodell weiß nicht, ob dahinter SQL, NoSQL oder eine In-Memory-Struktur steckt.

## Domain Event: Was ist passiert?

Ein Domain Event beschreibt, dass etwas fachlich Relevantes geschehen ist. `BestellungAufgegeben`, `ZahlungEingegangen`, `LieferungVersandt`. Events entkoppeln Systemteile: Wenn eine Bestellung aufgegeben wird, muss die Bestelllogik nicht wissen, dass danach eine E-Mail verschickt und der Lagerbestand aktualisiert wird.

## Wann lohnen sich taktische Bausteine?

Taktisches DDD lohnt sich, wenn die Fachlogik komplex genug ist, dass einfache CRUD-Operationen sie nicht mehr abbilden. Wenn Geschäftsregeln über mehrere Objekte hinweg gelten und konsistent durchgesetzt werden müssen. Für eine einfache Datenanwendung sind Entities und Aggregates zu viel Architektur.
