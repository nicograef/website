# Bounded Context: Klare Grenzen für komplexe Domänen

Stell dir vor, du sitzt in einem Meeting mit Vertrieb und Kundendienst. Beide reden über „den Kunden“. Der Vertrieb meint damit Kontaktdaten, Kaufhistorie, Kundensegment. Der Kundendienst meint ein offenes Ticket mit Problembeschreibung und Eskalationsstufe. Beide benutzen dasselbe Wort, meinen aber etwas völlig anderes.

Solange das nur ein Meeting ist, redet man aneinander vorbei und klärt es im Gespräch. Im Code wird derselbe Unterschied dauerhaft und teuer.

## Das Problem: Ein Begriff, viele Bedeutungen

Sobald ein System wächst, treffen mehrere Fachbereiche mit ihren eigenen Sprachen aufeinander. Jeder hat seine eigene Vorstellung davon, was ein „Kunde“, ein „Auftrag“ oder eine „Transaktion“ ist. Das klingt abstrakt, wird im Datenmodell aber schnell konkret.

Viele Systeme ignorieren diesen Unterschied und lassen alles in ein einziges `Customer`-Objekt fließen: `taxId` für die Buchhaltung, `campaignSegment` fürs Marketing, `supportTier` für den Kundendienst. Das Objekt wächst mit jeder Abteilung, die dazukommt. Keine Abteilung versteht das gesamte Modell noch. Jede Änderung kann in einem fremden Bereich etwas kaputtmachen, den man beim Ändern gar nicht im Blick hatte.

Die Lösung dafür ist keine schnellere Datenbank und kein besseres ORM. Sie ist eine Grenze: der Bounded Context.

## Was ist ein Bounded Context?

Ein **Bounded Context** ist eine explizite Grenze, innerhalb derer ein bestimmtes Domänenmodell gilt. Innerhalb dieser Grenze hat jeder Begriff eine präzise Bedeutung. Außerhalb darf derselbe Begriff etwas anderes bedeuten, und genau das ist gewollt.

Eric Evans, der Begründer von Domain Driven Design, definierte es so: *„A description of a boundary (typically a subsystem, or the work of a particular team) within which a particular model is defined and applicable.“*

Entwickler und Fachexperten teilen innerhalb eines Bounded Context eine gemeinsame Sprache: die Ubiquitous Language. „Kunde“ im Rechnungswesen beschreibt etwas anderes als „Kunde“ im Marketing. Deshalb verdienen sie zwei verschiedene Modelle in zwei verschiedenen Kontexten.

> **Subdomain vs. Bounded Context:** Eine Subdomain ist ein Teilbereich des Fachgebiets (z.B. „Zahlungsabwicklung“) und existiert unabhängig von der Software. Der Bounded Context ist die Designentscheidung: Wie grenzen wir diesen Bereich im Code ab? Manchmal deckt sich beides, manchmal nicht.

## Ein Begriff, zwei Modelle

Nehmen wir ein E-Commerce-Unternehmen mit zwei Bounded Contexts: Rechnungswesen und Marketing.

Im Rechnungswesen-Kontext ist ein Kunde eine zahlungspflichtige Entität. Relevant sind Name, Adresse, Steuernummer, IBAN, Zahlungskonditionen und offene Rechnungen. Kommunikationspräferenzen oder Kampagnenzugehörigkeit interessieren hier niemanden.

Im Marketing-Kontext ist ein Kunde ein Empfänger von Kampagnen. Relevant sind Kommunikationskanal, Segment, Klick- und Öffnungsraten. Steuernummern und Zahlungskonditionen spielen keine Rolle.

Es ist derselbe Mensch, aber zwei saubere Modelle in zwei Kontexten. Ein gemeinsames `Customer`-Objekt müsste beides tragen. Das Ergebnis: ein aufgeblähtes Modell, das keine Seite vollständig versteht und das bei jeder Änderung Seiteneffekte riskiert. Zwei getrennte Modelle, jeweils nur mit den Feldern, die der Kontext braucht, sind kleiner, verständlicher, testbarer und unabhängig voneinander änderbar. Was für den einen Kontext ein Pflichtfeld ist, existiert im anderen schlicht nicht.

Denselben Effekt habe ich bei jotti, meinem Kassensystem für Vereine: „Produkt“ bedeutet in der Produktverwaltung etwas anderes als in der Kasse. Im Admin-Bereich pflege ich Produkte mit Varianten, Preisen und Steuersätzen, alles jederzeit änderbar. In der Kasse dagegen friert jede Bestellung Name und Preis beim Aufnehmen ein. Benenne ich ein Produkt nächste Woche um, zeigt die Bestellhistorie von heute trotzdem, was die Servicekraft wirklich kassiert hat.

## Wenn Kontexte miteinander reden

Irgendwann braucht ein Kontext Daten aus einem anderen. Der Rechnungswesen-Kontext braucht eine Kundenadresse, die im Vertriebs-Kontext gepflegt wird. Der Marketing-Kontext will wissen, ob ein Kunde aktive Rechnungen hat.

Eine **Context Map** dokumentiert diese Beziehungen. Sie zeigt, welche Kontexte kommunizieren und in welcher Richtung die Abhängigkeit verläuft. Der Kontext, der Daten liefert, heißt Upstream. Der Kontext, der sie konsumiert, heißt Downstream.

Wie der Downstream mit dem Upstream-Modell umgeht, hängt vom Integrations-Pattern ab. Ein Conformist übernimmt das Upstream-Modell direkt: pragmatisch, aber eng gekoppelt. Wer das eigene Modell schützen will, baut einen Anti-Corruption Layer als Übersetzungsschicht dazwischen. Bedient ein Kontext viele Konsumenten, bietet sich ein Open Host Service an: eine stabile, dokumentierte API.

Die strategische Entscheidung kommt vor der technischen Umsetzung. Erst die Beziehung zwischen den Kontexten klären, dann die API bauen.

## Bounded Contexts und Microservices

Wer heute Microservices hört, denkt oft zuerst an Bounded Contexts, und das ist keine schlechte Assoziation. Ein Bounded Context ist eine fachliche Grenze, ein Microservice eine technische Deployment-Grenze. Die beiden müssen sich nicht decken, aber ein Bounded Context ist ein guter Ausgangspunkt, um einen Service zuzuschneiden. Wenn ein Modell ohnehin nur innerhalb einer Grenze gilt, lässt es sich auch hinter dieser Grenze deployen.

Der umgekehrte Weg geht selten gut aus. Wer Services nach technischen Kriterien oder nach dem Organigramm schneidet, ohne die fachlichen Grenzen zu kennen, landet oft in einem Distributed Monolith: Dienste laufen zwar in eigenen Prozessen, hängen aber logisch so eng zusammen, dass sich fast keine Änderung auf einen einzigen Service beschränken lässt. Man hat dann die Betriebskosten verteilter Systeme und trotzdem die Kopplung eines Monolithen, also das Schlechteste aus beiden Welten.

Wichtig ist mir dabei: Bounded Contexts sind kein Microservices-Konzept. Sie funktionieren genauso im modularen Monolithen. Dort ist jedes Modul ein Kontext mit eigenem Modell und eigener Sprache, nur eben ohne Netzwerkgrenze dazwischen. Die fachliche Trennung ist die eigentliche Arbeit; ob am Ende ein Prozess läuft oder zehn, ist eine spätere Entscheidung.

## Fazit

Die Grenze eines Bounded Context folgt dem Sprachgebrauch, nicht der Technik: Wo ein Begriff anfängt, etwas anderes zu bedeuten, gehört eine Kontextgrenze hin. Jeder Kontext bekommt ein Modell, das nur enthält, was er braucht. Die Context Map macht sichtbar, was sonst implizit wächst. Und ist die fachliche Grenze einmal sauber gezogen, ist die Frage nach Microservice oder Modul fast nur noch ein Deployment-Detail.
