# Vom Gesetzestext zur Architektur: ein Kassensystem, ausgelegt auf die KassenSichV

> **Entwurf** — Dieser Artikel ist ein Draft und noch nicht veröffentlicht.

Ein Kassenbuch für ein Vereinsfest sieht harmlos aus: ein paar Excel-Zeilen, Bargeld rein, Bargeld raus, am Ende ein Kassensturz. Als ehemaliges Vereinsvorstandsmitglied kenne ich dieses Kassenbuch von innen — und ich weiß auch, wie schnell aus "harmlos" ein Problem mit dem Finanzamt wird, sobald elektronisch statt auf Papier kassiert wird.

Denn sobald ein Smartphone oder ein PC das Kassenbuch führt, ist es rechtlich ein "elektronisches Aufzeichnungssystem" — und damit an strenge Vorgaben gebunden. Für [jotti](https://jotti.rocks), das Kassensystem, das ich für Vereine und gemeinnützige Organisationen baue, war die zentrale Frage nicht "wie baue ich eine App zum Bestellungen-Erfassen", sondern: Wie übersetze ich Gesetzestext in Architekturentscheidungen? Dieser Artikel geht die wichtigsten davon durch.

Falls dir Event-Sourcing noch nicht vertraut ist, lohnt sich vorher ein Blick in [Event-Sourcing am Beispiel Warenkorb erklärt](/articles/event-sourcing-am-beispiel-warenkorb-erklaert) — die Grundidee (Zustände nicht speichern, sondern aus Events rekonstruieren) ist hier die Basis für alles Weitere.

## Die Rechtslage: was ein Kassensystem heute leisten muss

Vier Regelwerke bestimmen, was ein deutsches Kassensystem können muss:

- **<abbr title="Abgabenordnung">AO</abbr> § 146a**: Verlangt die Einzelaufzeichnungspflicht — jeder Geschäftsvorfall muss einzeln, vollständig, richtig, zeitgerecht und geordnet erfasst werden, geschützt durch eine zertifizierte technische Sicherheitseinrichtung (<abbr title="Technische Sicherheitseinrichtung">TSE</abbr>).
- **<abbr title="Kassensicherungsverordnung">KassenSichV</abbr>**: Konkretisiert, was als "elektronisches Aufzeichnungssystem" gilt (auch browserbasierte Systeme fallen darunter) und wie die TSE technisch angebunden werden muss.
- **<abbr title="Grundsätze zur ordnungsmäßigen Führung und Aufbewahrung von Büchern, Aufzeichnungen und Unterlagen in elektronischer Form sowie zum Datenzugriff">GoBD</abbr>**: Fordert Nachvollziehbarkeit und Unveränderbarkeit gebuchter Daten — das sogenannte Radierverbot. Einmal erfasst, darf ein Vorgang nicht mehr verändert werden, nur noch durch einen neuen, gegenläufigen Vorgang korrigiert werden.
- **<abbr title="Digitale Schnittstelle der Finanzverwaltung für Kassensysteme">DSFinV-K</abbr>**: Legt das Exportformat fest, in dem die Finanzverwaltung bei einer Prüfung die Kassendaten sehen will.

Manche dieser Konzepte wurden für Registrierkassen mit Typenschild und Herstellerplakette entworfen — nicht für selbst gehostete Software. Ohne physisches Gehäuse gibt es kein Typenschild, also generiert jotti bei der ersten Einrichtung eine UUID als dauerhafte Kassen-Seriennummer. Ein kleines Detail, das zeigt, worum es in diesem Artikel eigentlich geht: Gesetzestext, der für Hardware geschrieben wurde, muss für Software neu interpretiert werden.

## Das Kassenjournal: append-only als Source of Truth

Die Einzelaufzeichnungspflicht aus § 146a AO plus das GoBD-Radierverbot ergeben zusammen fast zwangsläufig Event-Sourcing: Jeder Geschäftsvorfall wird als eigenes, unveränderliches Event in ein Kassenjournal geschrieben. Nichts wird nachträglich verändert oder gelöscht.

```json
{
  "id": "9f2e-...",
  "type": "bestellung-aufgegeben",
  "subject": "tisch:12",
  "time": "2026-07-04T19:32:00Z",
  "data": {
    "positionen": [{ "produktId": 7, "menge": 2, "einzelpreisCent": 350 }]
  }
}
```

Das Kassenjournal ist damit nicht ein Audit-Log neben der eigentlichen Datenhaltung — es *ist* die Datenhaltung. Jeder andere Blick auf die Kassendaten (aktueller Tischstand, Tagesabschluss, DSFinV-K-Export) lässt sich daraus rekonstruieren. Damit das append-only auch wirklich gilt und nicht nur eine Konvention im Anwendungscode bleibt, verhindert ein Datenbank-Trigger UPDATE und DELETE auf Ebene der Datenbank selbst:

```sql
CREATE OR REPLACE FUNCTION kassenjournal_verhindert_aenderung()
RETURNS TRIGGER AS $$
BEGIN
    RAISE EXCEPTION 'Kassenjournal ist append-only: UPDATE/DELETE nicht erlaubt';
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER kassenjournal_immutable
BEFORE UPDATE OR DELETE ON kassenjournal
FOR EACH ROW EXECUTE FUNCTION kassenjournal_verhindert_aenderung();
```

Eine Stornierung ist deshalb niemals ein Löschen. Sie ist ein neues Event (`stornierung-erteilt`), das den ursprünglichen Vorgang gegenbucht, ohne ihn anzufassen. Genau das verlangt das Radierverbot: Die Historie bleibt vollständig sichtbar, inklusive der Korrektur.

## TSE-Signatur: ein Vorgang, eine Transaktion

Die KassenSichV verlangt, dass jeder Geschäftsvorfall von einer zertifizierten TSE signiert wird. jotti nutzt dafür eine Cloud-TSE statt eines lokalen USB-Sticks — praktikabel für ein System, das auf Smartphones im Browser läuft, ohne dass jedes Gerät eigene Hardware bräuchte.

Damit die Wahl des TSE-Anbieters keine architektonische Sackgasse wird, sitzt die Anbindung hinter einem Interface:

```go
type TSEClient interface {
    SignVorgang(ctx context.Context, vorgang Vorgang) (Signatur, error)
}
```

Aktuell implementiert jotti dieses Interface gegen [fiskaly](https://fiskaly.com) als ersten Zielanbieter — API-first und nach BSI TR-03153 zertifiziert. Der Anbieter ist damit eine austauschbare Implementierung, nicht die Architektur selbst.

Jeder Vorgang am Tisch — eine Bestellung, eine Stornierung, eine Zahlung — ist eine eigene, sofort abgeschlossene TSE-Transaktion. Keine lang offene Transaktion über eine ganze Tischsitzung hinweg, sondern viele kurze, in sich abgeschlossene Signaturen. Das hält die Fehlerfläche klein: Fällt die TSE für einen Moment aus, betrifft das genau einen Vorgang, für den es einen asynchronen Nachsignier-Mechanismus gibt — nicht die ganze Sitzung.

## Synchrone Projektion statt Eventual Consistency

Der aktuelle Stand eines Tisches — welche Produkte liegen offen, was wurde schon bezahlt — ließe sich bei jedem Aufruf aus dem kompletten Kassenjournal neu berechnen. Für die Anzeige an der Theke wäre das aber zu langsam. Deshalb pflegt jotti eine Projektion (`tisch_sessions`), die den aktuellen Stand vorhält.

Der Punkt, der hier zählt: Diese Projektion wird nicht asynchron im Hintergrund nachgezogen, sondern synchron, in derselben Datenbank-Transaktion wie das Event selbst geschrieben (Write-Through). Es gibt also kein Zeitfenster, in dem Journal und Projektion auseinanderlaufen könnten — anders als bei vielen CQRS-Systemen, die genau dieses Zeitfenster (Eventual Consistency) bewusst in Kauf nehmen.

Der Grund liegt im Charakter der Projektion: Sie ist reiner Convenience-Cache über dem Kassenjournal, jederzeit vollständig daraus neu berechenbar, kein eigenständiges System mit eigenem Konsistenzanspruch. Ein Zeitfenster einzuführen, in dem eine Servicekraft am Tisch einen veralteten Stand sehen könnte, hätte hier keinen Vorteil erkauft — nur ein Klasse von Bugs, die es nicht geben muss.

## Bewusst CRUD für Stammdaten

Event-Sourcing überall anzuwenden, nur weil man einmal angefangen hat, ist ein verbreiteter Fehler. jotti behandelt deshalb bewusst nicht alles als Event: Produkte, Tische und Benutzer sind klassische CRUD-Entitäten mit UPDATE und DELETE. Es gibt keine GoBD-Anforderung, die verlangt, dass die Änderung eines Produktnamens oder das Anlegen eines neuen Tisches unveränderlich protokolliert wird — diese Daten sind Stammdaten, keine Geschäftsvorfälle.

Damit historische Bestellungen trotzdem korrekt bleiben, auch wenn sich ein Produktname oder -preis später ändert, friert jedes Bestellungs-Event die relevanten Produktdaten zum Zeitpunkt der Bestellung ein, statt zur Laufzeit auf die aktuellen Stammdaten zu verweisen. Ein nachträglich umbenanntes Produkt verfälscht damit keine drei Monate alte Buchung.

Interessant ist ein Grenzfall: Auch die Kassensitzungen selbst — eigentlich Teil der Kernkassenlogik — laufen als klassische CRUD-Tabelle, nicht als Events. Der Grund ist pragmatisch, nicht dogmatisch: Sie liegen im Hot Path bei jedem Tischaufruf, und Event-Sourcing hätte hier nur Komplexität ohne fachlichen Mehrwert hinzugefügt. Die Faustregel bei jotti lautet: Event Sourcing dort, wo die Domäne Unveränderbarkeit und lückenlose Historie fordert — CRUD überall sonst.

## BYOD-Smartphones als reine Eingabegeräte

Servicekräfte nutzen bei jotti ihre eigenen Smartphones — kein Verein muss Hardware anschaffen. Rechtlich ist das nur unproblematisch, wenn die Smartphones nicht selbst als eigenständige "Aufzeichnungssysteme" gelten, sondern reine Eingabegeräte für ein zentrales System sind, das im Backend die TSE-Pflicht erfüllt.

Diese Einordnung ist keine bloße Behauptung, sondern eine Eigenschaft der Architektur: Das Frontend hat keinen Service Worker, keine optimistischen Updates und keinen Offline-Speicher für Vorgangsdaten. Jede Aktion — Bestellung aufgeben, stornieren, kassieren — ist ein synchroner Request ans Backend, dort verbucht, signiert und persistiert. Auf dem Smartphone bleibt fiskalisch nichts zurück. Fällt das Gerät aus oder wird es verloren, geht keine einzige Buchung verloren, weil nie eine dort war.

## Fazit

Die Architekturentscheidungen in jotti — append-only Kassenjournal, TSE-Signatur pro Vorgang, synchrone Projektion, CRUD für Stammdaten, Smartphones als reine Eingabegeräte — sind fast alle direkt aus AO, KassenSichV und GoBD ableitbar. Der Gesetzestext hat hier weniger Architektur verhindert als vielmehr eine vorgegeben, die sich auch ohne rechtlichen Zwang gut begründen ließe: Nachvollziehbarkeit und Unveränderbarkeit sind Eigenschaften, die man sich für ein Kassensystem sowieso wünscht.

jotti ist aktuell in der Beta, quelloffen (Source-Available) auf GitHub entwickelt. Wer sich den Code ansehen will: [github.com/nicograef/jotti](https://github.com/nicograef/jotti).
