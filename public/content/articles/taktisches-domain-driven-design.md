# Taktisches Domain Driven Design: Fachlogik im Code abbilden

Du hast die Ubiquitous Language definiert und weißt, welche Bounded Contexts dein System hat. Jetzt stellt sich die Frage: Wie sieht der Code innerhalb eines Kontexts aus? Taktisches DDD liefert dafür fünf Bausteine. Sie sind keine Erfindung, um Klassen zu benennen, sondern Antworten auf wiederkehrende Fragen: Woran erkenne ich ein Objekt wieder? Wo lebt eine Geschäftsregel? Wie halte ich zusammen, was zusammengehört?

## Entity: Identität über die Zeit

Eine **Entity** ist ein Objekt, das durch seine Identität definiert wird, nicht durch seine aktuellen Daten. Eine Bestellung bleibt dieselbe Bestellung, auch wenn sich ihr Status von „offen“ auf „versandt“ ändert. Das macht die Bestell-ID, nicht der aktuelle Zustand.

Praktisch heißt das: Zwei Entities sind gleich, wenn ihre ID gleich ist, selbst wenn sich alle anderen Felder unterscheiden. Und umgekehrt sind sie verschieden, sobald die ID verschieden ist, auch wenn alle Felder zufällig übereinstimmen. Ein Kunde, der von „Meier“ zu „Müller“ heiratet, ist derselbe Kunde. Zwei Kunden namens „Anna Schmidt“ sind zwei verschiedene Kunden. Wenn ich in einer Klasse `equals` oder einen Vergleich schreibe und dabei auf Feldwerte statt auf die ID schaue, ist das fast immer ein Modellierungsfehler.

## Value Object: Wert ohne Identität

Ein **Value Object** hat keine eigene Identität. Es ist vollständig durch seinen Wert definiert und unveränderlich. Zwei Geldbeträge von 100 EUR sind identisch, egal wo sie im Speicher liegen. Typische Value Objects: Geldbeträge, E-Mail-Adressen, Adressen, Datumsintervalle.

Die Unveränderlichkeit ist kein Detail, sondern der ganze Punkt. Wer einen Betrag ändern will, erstellt ein neues Objekt, statt das alte zu mutieren. Das klingt umständlich, verhindert aber eine ganze Fehlerklasse: Ein Value Object, das ich irgendwo hinreiche, kann mir niemand hinter meinem Rücken verändern. Ich muss mir keine Sorgen machen, ob ein `Geldbetrag`, den ich in zwei Bestellungen gesteckt habe, plötzlich in beiden anders aussieht. Bei einem 100-EUR-Schein interessiert mich auch nicht, welches konkrete Exemplar ich in der Hand halte, sondern nur, dass es 100 EUR sind. Genau diese Denkweise überträgt das Value Object in den Code.

## Aggregate: Die Konsistenzgrenze

Ein **Aggregate** ist ein Cluster aus Entities und Value Objects, der als Einheit behandelt wird. Im Zentrum steht der **Aggregate Root**: der einzige Zugriffspunkt von außen. Er schützt die Invarianten der gesamten Einheit, also die Regeln, die immer gelten müssen, egal was von außen passiert.

Beispiel: Eine `Bestellung` (Aggregate Root) enthält `Bestellpositionen` (Entities) und einen `Gesamtbetrag` (Value Object). Die Geschäftsregel „bestätigte Bestellungen dürfen nicht mehr geändert werden“ wird direkt im Aggregate Root durchgesetzt, nicht in einem Service oder Controller.

```typescript
class Bestellung {
  private readonly id: BestellungId;
  private positionen: Bestellposition[];
  private status: BestellStatus;

  hinzufuegen(position: Bestellposition): void {
    if (this.status.istBestaetigt()) {
      throw new DomainError(
        "Bestätigte Bestellungen können nicht geändert werden."
      );
    }
    this.positionen.push(position);
  }
}
```

Von außen greifst du nur auf den Aggregate Root zu. Die interne Struktur (welche Positionen, welcher Status) bleibt seine Sache. Niemand fasst eine `Bestellposition` direkt an, ohne über die `Bestellung` zu gehen. Nur so kann der Root garantieren, dass die Regel greift.

Das Aggregate ist außerdem die Transaktionsgrenze. Was innerhalb eines Aggregates liegt, wird zusammen gespeichert und ist nach jeder Operation konsistent. Über mehrere Aggregate hinweg gilt das bewusst nicht: Zwischen zwei Bestellungen erzwinge ich keine sofortige Konsistenz in einer gemeinsamen Transaktion. Das ist keine Nachlässigkeit, sondern eine Design-Entscheidung. Je kleiner ich das Aggregate schneide, desto weniger sperrt eine einzelne Operation und desto besser skaliert das System. Die Kunst beim Aggregate-Schnitt ist genau diese Frage: Was muss wirklich in einem Atemzug konsistent sein, und was darf kurz auseinanderlaufen?

## Repository: Domäne trifft Datenbank

Ein **Repository** versteckt die Datenschicht hinter domänensprachlichen Methoden. Statt `SELECT * FROM orders WHERE customer_id = ?` schreibst du `bestellungRepository.findeNachKunde(kundenId)`. Das Domänenmodell weiß nicht, ob dahinter SQL, NoSQL oder eine In-Memory-Struktur steckt.

Ein Repository arbeitet dabei üblicherweise auf der Ebene der Aggregate: Es lädt und speichert ganze Bestellungen, nicht einzelne Bestellpositionen. Das passt zur Transaktionsgrenze von oben. Der praktische Gewinn ist doppelt: Der fachliche Code liest sich in der Sprache der Domäne statt in SQL, und die Datenbank lässt sich in Tests durch eine In-Memory-Variante ersetzen, ohne dass die Fachlogik davon etwas merkt.

## Domain Event: Was ist passiert?

Ein **Domain Event** beschreibt, dass etwas fachlich Relevantes geschehen ist. `BestellungAufgegeben`, `ZahlungEingegangen`, `LieferungVersandt`. Die Vergangenheitsform ist Absicht: Ein Event ist ein Fakt, der bereits eingetreten ist und sich nicht mehr zurücknehmen lässt. Events entkoppeln Systemteile: Wenn eine Bestellung aufgegeben wird, muss die Bestelllogik nicht wissen, dass danach eine E-Mail verschickt und der Lagerbestand aktualisiert wird. Sie meldet nur, was passiert ist, und andere Teile reagieren darauf.

Genau solche Events bilden bei jotti, meinem Kassensystem für Vereine, das Rückgrat der Kasse. Bestätigt eine Servicekraft die Ausgabe am Tisch oder storniert die Serviceleitung eine Bestellung mit Pflichtkommentar, wird daraus jeweils ein unveränderliches Domain Event im Kassenjournal. Nichts wird nachträglich überschrieben, jede Korrektur ist selbst wieder ein Event.

Wenn diese Events nicht nur die Domäne entkoppeln, sondern zur eigentlichen Datenhaltung werden, landet man bei [Event-Sourcing](/articles/was-ist-event-sourcing). Domain Events sind der gedankliche Einstieg dorthin, auch wenn man sie längst nicht in jedem Projekt bis zum Event-Sourcing weitertreibt.

## Wann lohnen sich taktische Bausteine?

Taktisches DDD lohnt sich, wenn die Fachlogik komplex genug ist, dass einfache CRUD-Operationen sie nicht mehr abbilden. Wenn Geschäftsregeln über mehrere Objekte hinweg gelten und konsistent durchgesetzt werden müssen. Für eine einfache Datenanwendung sind Entities und Aggregates zu viel Architektur. Man muss auch nicht alle fünf Bausteine auf einmal einführen: Oft reicht es, mit sauber geschnittenen Aggregates anzufangen, in denen die Geschäftsregeln zentral liegen, und den Rest zu ergänzen, sobald die Domäne es verlangt.
