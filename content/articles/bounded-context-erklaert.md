# Bounded Context: Klare Grenzen für komplexe Domänen

Stell dir vor, du sitzt in einem Meeting mit Vertrieb und Kundendienst. Beide reden über "den Kunden". Der Vertrieb meint damit Kontaktdaten, Kaufhistorie, Kundensegment. Der Kundendienst meint ein offenes Ticket mit Problembeschreibung und Eskalationsstufe. Selbes Wort, andere Bedeutung.

Viele Systeme ignorieren diesen Unterschied. Alles fließt in ein einziges `Customer`-Objekt: `taxId` für die Buchhaltung, `campaignSegment` fürs Marketing, `supportTier` für den Kundendienst. Das Objekt wächst mit jeder Abteilung. Keine Abteilung versteht das gesamte Modell. Jede Änderung kann in einem fremden Bereich etwas kaputtmachen.

## Was ist ein Bounded Context?

Ein **Bounded Context** ist eine explizite Grenze, innerhalb derer ein bestimmtes Domänenmodell gilt. Innerhalb dieser Grenze hat jeder Begriff eine präzise Bedeutung. Außerhalb darf derselbe Begriff etwas anderes bedeuten — und genau das ist gewollt.

Eric Evans, der Begründer von Domain Driven Design, definierte es so: *"A description of a boundary (typically a subsystem, or the work of a particular team) within which a particular model is defined and applicable."*

Entwickler und Fachexperten teilen innerhalb eines Bounded Context eine gemeinsame Sprache — die Ubiquitous Language. "Kunde" im Rechnungswesen beschreibt etwas anderes als "Kunde" im Marketing. Deshalb verdienen sie zwei verschiedene Modelle in zwei verschiedenen Kontexten.

> **Subdomain vs. Bounded Context:** Eine Subdomain ist ein Teilbereich des Fachgebiets (z.B. "Zahlungsabwicklung") und existiert unabhängig von der Software. Der Bounded Context ist die Designentscheidung: Wie grenzen wir diesen Bereich im Code ab? Manchmal deckt sich beides, manchmal nicht.

Soweit die Theorie. Wie sieht das in der Praxis aus?

## Ein Begriff, zwei Modelle

Nehmen wir ein E-Commerce-Unternehmen mit zwei Bounded Contexts: Rechnungswesen und Marketing.

Im Rechnungswesen-Kontext ist ein Kunde eine zahlungspflichtige Entität. Relevant sind Name, Adresse, Steuernummer, IBAN, Zahlungskonditionen und offene Rechnungen. Kommunikationspräferenzen oder Kampagnenzugehörigkeit interessieren hier niemanden.

Im Marketing-Kontext ist ein Kunde ein Empfänger von Kampagnen. Relevant sind Kommunikationskanal, Segment, Klick- und Öffnungsraten. Steuernummern und Zahlungskonditionen spielen keine Rolle.

Ein gemeinsames `Customer`-Objekt müsste beides tragen. Das Ergebnis: ein aufgeblähtes Modell, das keine Seite vollständig versteht und das bei jeder Änderung Seiteneffekte riskiert. Zwei getrennte Modelle — jeweils nur mit den Feldern, die der Kontext braucht — sind kleiner, verständlicher und unabhängig voneinander änderbar.

Aber Kontexte existieren nicht in Isolation. Was passiert, wenn sie Daten austauschen müssen?

## Wenn Kontexte miteinander reden

Irgendwann braucht ein Kontext Daten aus einem anderen. Der Rechnungswesen-Kontext braucht eine Kundenadresse, die im Vertriebs-Kontext gepflegt wird. Der Marketing-Kontext will wissen, ob ein Kunde aktive Rechnungen hat.

Eine **Context Map** dokumentiert diese Beziehungen. Sie zeigt, welche Kontexte kommunizieren und in welcher Richtung die Abhängigkeit verläuft. Der Kontext, der Daten liefert, heißt Upstream. Der Kontext, der sie konsumiert, heißt Downstream.

Wie der Downstream mit dem Upstream-Modell umgeht, hängt vom Integrations-Pattern ab. Ein Conformist übernimmt das Upstream-Modell direkt — pragmatisch, aber eng gekoppelt. Wer das eigene Modell schützen will, baut einen Anti-Corruption Layer als Übersetzungsschicht dazwischen. Bedient ein Kontext viele Konsumenten, bietet sich ein Open Host Service an: eine stabile, dokumentierte API.

Die strategische Entscheidung kommt vor der technischen Umsetzung. Erst die Beziehung zwischen den Kontexten klären, dann die API bauen.

## Fazit

Ein Bounded Context ist keine technische Entscheidung. Die Grenze folgt dem Sprachgebrauch: Wo ein Begriff anfängt, etwas anderes zu bedeuten, gehört eine Kontextgrenze hin. Jeder Kontext bekommt ein Modell, das nur enthält, was er braucht. Teams können ihren Bereich unabhängig weiterentwickeln. Die Context Map macht sichtbar, was sonst implizit wächst.
