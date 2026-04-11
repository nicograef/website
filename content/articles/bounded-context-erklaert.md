# Bounded Context: Klare Grenzen für komplexe Domänen

Stell dir vor, du sitzt in einem Meeting mit Vertrieb und Kundendienst. Beide reden über "den Kunden", aber meinen sie dasselbe? Für den Vertrieb ist ein Kunde ein potenzieller Umsatz: Kontaktdaten, Kaufhistorie, Kundensegment. Für den Kundendienst ist ein Kunde ein offenes Ticket: Problembeschreibung, Gesprächsprotokoll, Eskalationsstufe.

In vielen Softwaresystemen wird dieses Problem ignoriert. Alles landet in einem einzigen, riesigen Datenmodell. Dann fangen die Probleme an.

## Das Problem: Ein Begriff, viele Bedeutungen

Sobald ein System wächst, treffen verschiedene Fachbereiche mit verschiedenen Sprachen aufeinander. Jeder Bereich hat seine eigene Vorstellung davon, was ein "Kunde", ein "Auftrag" oder eine "Transaktion" bedeutet.

Das klingt abstrakt, wird aber im Code sehr konkret. Ein gemeinsames `Customer`-Objekt muss plötzlich Felder für die Buchhaltung tragen (`taxId`), für das Marketing (`campaignSegment`) und für den Kundendienst (`supportTier`). Das Objekt wächst ins Unendliche. Keine Abteilung versteht das gesamte Modell noch vollständig. Jede Änderung kann unbeabsichtigte Nebeneffekte in einem anderen Bereich auslösen.

Die Lösung ist keine schnellere Datenbank und kein besseres ORM. Die Lösung ist **Bounded Context**.

## Was ist ein Bounded Context?

Ein **Bounded Context** (zu Deutsch: begrenzter Kontext) ist eine explizite Grenze, innerhalb derer ein bestimmtes Domänenmodell gilt. Innerhalb dieser Grenze hat jeder Begriff eine präzise, einheitliche Bedeutung. Außerhalb darf (und soll) derselbe Begriff etwas anderes bedeuten.

Eric Evans, der Begründer von [Domain Driven Design](/articles/was-ist-domain-driven-design), definierte den Bounded Context so: *"A description of a boundary (typically a subsystem, or the work of a particular team) within which a particular model is defined and applicable."*

Die zweite Säule eines Bounded Context ist die **Ubiquitous Language**, die gemeinsame, domänenspezifische Sprache, die Entwickler und Fachexperten innerhalb des Kontexts teilen. Diese Sprache gilt exakt in diesem Kontext und nicht darüber hinaus. Der Begriff "Kunde" im Rechnungswesen beschreibt etwas anderes als "Kunde" im Marketing. Genau deshalb verdienen sie zwei verschiedene Modelle.

> **Abgrenzung: Subdomain vs. Bounded Context:** Eine **Subdomain** beschreibt einen Teilbereich des Fachgebiets (z.B. "Zahlungsabwicklung") und existiert unabhängig davon, wie wir Software bauen. Der Bounded Context ist unsere Designentscheidung: Wie modellieren wir diesen Bereich im Code? Manchmal entspricht eine Subdomain einem Bounded Context, manchmal nicht.

## Ein Begriff, zwei Welten

Nehmen wir das Beispiel "Kunde" in einem E-Commerce-Unternehmen. Es gibt zwei Bounded Contexts: Rechnungswesen und Marketing.

![Zwei Bounded Contexts für "Kunde"](/assets/img/articles/bounded-context-erklaert-modelle.png)
_Derselbe Mensch, zwei saubere Modelle in zwei Bounded Contexts_

Im **Rechnungswesen-Kontext** interessiert uns:

- Name, Adresse, Steuernummer
- IBAN und Zahlungskonditionen
- Offene Rechnungen und Zahlungsstatus

Im **Marketing-Kontext** interessiert uns:

- Kommunikationskanal-Präferenz
- Kampagnenzugehörigkeit und Segment
- Klick- und Öffnungsrate

Ein gemeinsames Modell, das beides abbildet, wäre aufgebläht und fragil. Zwei saubere Modelle in zwei Bounded Contexts sind wartbar, testbar und verständlich, jedes für sich.

## Context Mapping: Wenn Kontexte miteinander reden müssen

Bounded Contexts können nicht vollständig isoliert existieren. Ein Online-Shop braucht irgendwann Informationen aus beiden Welten, zum Beispiel wenn eine Rechnung an einen Kunden verschickt werden soll, der im Marketing-Kontext abgemeldet ist.

Die entscheidende Frage lautet nicht "Welche API bauen wir?", sondern: "Welche Beziehung soll zwischen den Kontexten bestehen?" Erst nach dieser strategischen Entscheidung wählt man die Technologie. Diesen Prozess nennt man **Context Mapping**; dokumentiert wird er in einer **Context Map**.

![Context Map mit Anti-Corruption Layer](/assets/img/articles/bounded-context-erklaert-context-map.png)
_Context Map: Zwei Bounded Contexts, verbunden über einen Anti-Corruption Layer_

In jeder Kontext-Beziehung gibt es zwei Rollen:

- **Upstream:** der Kontext, der Daten oder Dienste bereitstellt. Er ist unabhängig und definiert den Vertrag.
- **Downstream:** der Kontext, der diese Daten konsumiert. Er muss sich anpassen.

Wie sich der Downstream anpasst, entscheidet das gewählte Pattern:

| Pattern | Verhalten |
|---|---|
| **Conformist** | Downstream übernimmt das Upstream-Modell ohne Transformation (pragmatisch, koppelt aber die Modelle) |
| **<abbr title="Anti-Corruption Layer">ACL</abbr>** | Downstream baut eine Übersetzungsschicht (mehr Aufwand, aber das eigene Modell bleibt sauber) |
| **<abbr title="Open Host Service">OHS</abbr>** | Upstream stellt eine stabile, dokumentierte API für viele Konsumenten bereit |

Der **Anti-Corruption Layer** ist meistens die bessere Wahl. Die Übersetzungsschicht isoliert den eigenen Kontext von Änderungen im Upstream. Wenn der Kundendaten-Kontext seine API umbaut, ändert sich nur der ACL; der Rest des Rechnungswesen-Kontexts bleibt unberührt.

## Bounded Contexts und Microservices

Bounded Contexts und Microservices ergänzen sich natürlich. Ein Bounded Context ist eine fachliche Grenze; ein Microservice ist eine technische Deployment-Grenze. Sie müssen nicht immer identisch sein, aber ein Bounded Context ist ein guter Ausgangspunkt für den Zuschnitt eines Microservice.

Wer Microservices ohne Bounded Contexts definiert, endet häufig in einem **Distributed Monolith**: Dienste sind physisch getrennt, aber logisch so eng gekoppelt, dass jede Änderung mehrere Services gleichzeitig betrifft. Die Vorteile von Microservices (unabhängiges Deployment, Teamautonomie) verschwinden.

Bounded Contexts sind kein Microservices-Konzept. Sie gelten genauso im modularen Monolithen: Jedes Modul kann ein Bounded Context sein: mit eigenem Modell, eigener Sprache, eigenen Grenzen.

## Fazit

Ein Bounded Context ist keine technische Entscheidung, sondern eine fachliche. Die Grenze folgt dem Sprachgebrauch des Business: Wo immer ein Begriff anfängt, etwas anderes zu bedeuten, ist eine Kontextgrenze sinnvoll.

Die konkreten Vorteile:

- **Klarere Modelle:** jeder Kontext enthält nur, was er wirklich braucht
- **Unabhängige Teams:** Teams können ihren Kontext weiterentwickeln, ohne andere zu blockieren
- **Explizite Abhängigkeiten:** die Context Map macht sichtbar, was sonst implizit und unkontrolliert wächst
- **Natürliche Microservice-Grenzen:** Bounded Contexts geben dem Zuschnitt eine fachliche Grundlage

Die Grundlagen von Domain Driven Design, inklusive Ubiquitous Language und taktischen Bausteinen wie Aggregates und Entities, findest du im Artikel [Was ist Domain Driven Design?](/articles/was-ist-domain-driven-design).

## Glossar

- **Bounded Context**: Eine explizite Grenze, innerhalb derer ein bestimmtes Domänenmodell und eine einheitliche Sprache gelten.
- **Ubiquitous Language**: Die gemeinsame, domänenspezifische Sprache von Entwicklern und Fachexperten innerhalb eines Bounded Context, gültig exakt dort, nicht darüber hinaus.
- **Context Map**: Diagramm, das die Beziehungen und Integrationsmuster zwischen Bounded Contexts visualisiert.
- **Anti-Corruption Layer (ACL)**: Übersetzungsschicht eines Downstream-Kontexts, die das eigene Modell vor Änderungen im Upstream-Modell schützt.
- **Conformist**: Integration-Pattern, bei dem der Downstream das Upstream-Modell ohne Transformation übernimmt.
- **Upstream / Downstream**: Richtungsbeziehung: Upstream ist unabhängig und liefert; Downstream konsumiert und passt sich an.
- **Open Host Service (OHS)**: Pattern, bei dem ein Kontext eine stabile, dokumentierte API für viele Konsumenten bereitstellt.
