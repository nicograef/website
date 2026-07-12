# Strategisches Domain Driven Design: Große Domänen aufteilen

Dein E-Commerce-System hat 200 Tabellen in einer Datenbank. Das Vertriebsteam sagt „Kunde“ und meint Umsatzpotenzial. Das Support-Team sagt „Kunde“ und meint ein offenes Ticket. Beide greifen auf dasselbe `Customer`-Objekt zu. Jede Änderung an diesem Objekt kann den jeweils anderen Bereich kaputtmachen.

Strategisches DDD liefert Werkzeuge, um große Domänen in Bereiche aufzuteilen, die sich unabhängig voneinander entwickeln lassen. Während taktisches Design beschreibt, wie du einen einzelnen Bereich in Code gießt, geht es hier eine Ebene höher: um den Zuschnitt, um Grenzen und um die Frage, wo sich Investition überhaupt lohnt.

## Subdomains: Teilbereiche des Fachgebiets

Eine Subdomain ist ein Teilbereich der Domain mit eigenen Zielen und Verantwortlichkeiten. Im E-Commerce: Bestellverwaltung, Zahlungsabwicklung, Lagerhaltung, Kundenkommunikation. Der entscheidende Punkt ist, dass nicht alle Subdomains gleich wichtig sind. DDD unterscheidet drei Typen, und die Unterscheidung ist keine akademische Übung, sondern eine Anweisung, wohin deine knappe Entwicklungszeit fließt.

Die **Core Domain** ist das Kerngeschäft, das, womit du dich vom Wettbewerb abhebst. Bei einem Streaming-Dienst ist das die Empfehlungslogik, nicht der Login. Hier lohnt sich die höchste Sorgfalt: eigene Entwicklung, sorgfältige Modellierung, deine besten Leute. Wer die Core Domain wie einen Nebenschauplatz behandelt, verschenkt genau die Stelle, an der Softwarequalität sich in Geschäftserfolg übersetzt.

Eine **Supporting Domain** unterstützt das Kerngeschäft, ist aber selbst kein Alleinstellungsmerkmal. Beim Streaming-Dienst etwa die Verwaltung von Abo-Tarifen: notwendig, spezifisch genug, dass man sie nicht von der Stange kauft, aber kein Ort für Overengineering. Solide Umsetzung reicht, und wenn hier ein Modell etwas gröber bleibt als in der Core Domain, ist das eine bewusste Entscheidung, kein Versäumnis.

Eine **Generic Domain** löst ein allgemeines Problem, das viele haben: Authentifizierung, E-Mail-Versand, Zahlungsabwicklung. Hier eigene Software zu bauen ist fast immer Verschwendung. Man kauft ein oder nimmt eine erprobte Bibliothek, denn ein selbstgebauter Login bringt keinen Kunden dazu, das Abo abzuschließen. Die eingesparte Zeit gehört der Core Domain.

Die praktische Konsequenz: Bevor du Architektur baust, ordne deine Subdomains ein. Sie sagt dir, wo Modellierungsaufwand sich auszahlt und wo er nur Kosten verursacht.

## Bounded Context: Wo welches Modell gilt

Ein Bounded Context ist eine explizite Grenze, innerhalb derer ein bestimmtes Modell und eine einheitliche Sprache gelten. „Kunde“ im Rechnungswesen hat andere Attribute als „Kunde“ im Marketing. Zwei saubere Modelle in zwei Kontexten sind wartbarer als ein aufgeblähtes Universalmodell, das jeden Sonderfall aller Bereiche kennen muss.

Der [Artikel zu Bounded Contexts](/articles/bounded-context-erklaert) behandelt dieses Thema ausführlich. Hier genügt: Subdomain ist der fachliche Teilbereich im Problemraum, Bounded Context die Grenze, die du im Lösungsraum tatsächlich um ein Modell ziehst. Im Idealfall fällt beides zusammen, in der Praxis nicht immer.

Im Backend von jotti, meinem Kassensystem für Vereine, schneide ich die Domäne genau so: ein Go-Paket pro Bounded Context.

```text
backend/domain/
├── kasse/      Bestellung, Zahlung, Stornierung
├── produkt/    Produktverwaltung mit Varianten
├── tisch/
├── betreiber/
├── tse/        TSE-Anbindung und Signaturen
└── …           dazu u. a. steuer/ und reporting/
```

Jedes Paket hat sein eigenes Modell. Die Fachpakete heißen bewusst deutsch, nur Infrastruktur wie `event` oder `jwt` bleibt englisch. Ein Kontext wie `tse` ist dabei ein interessanter Sonderfall: Diesen Bounded Context gäbe es ohne die Kassensicherungsverordnung gar nicht, er ist ein Kontext, der allein aus einem regulatorischen Zwang entstanden ist.

## Context Mapping: Beziehungen zwischen Kontexten

Bounded Contexts existieren nicht isoliert. Wenn der Rechnungswesen-Kontext Kundendaten aus dem Vertriebs-Kontext braucht, entsteht eine Beziehung. Die Context Map dokumentiert diese Beziehungen: Wer liefert Daten (Upstream), wer konsumiert sie (Downstream)? Und vor allem: Welches Integrations-Pattern beschreibt das Verhältnis?

Drei Patterns tauchen am häufigsten auf, und die Wahl zwischen ihnen ist eine strategische, keine technische:

- **Conformist:** Der Downstream übernimmt das Upstream-Modell unverändert. Das ist die günstigste Option und die richtige, wenn das fremde Modell ohnehin gut zu deinem Bedarf passt und du keine Verhandlungsmacht über den Upstream hast. Der Preis ist Kopplung: Ändert sich das Upstream-Modell, ändert sich deins mit.
- **Anti-Corruption Layer:** Der Downstream baut eine Übersetzungsschicht, die das fremde Modell an der Grenze in sein eigenes umwandelt. Das lohnt sich, sobald das Upstream-Modell schlecht zu deiner Domäne passt oder instabil ist, oder wenn du einen austauschbaren Drittanbieter kapseln willst. Der [Anti-Corruption Layer](/articles/anti-corruption-layer-erklaert) kostet Aufwand, hält deine Core Domain aber sauber.
- **Open Host Service:** Der Upstream stellt eine bewusst stabile, dokumentierte API bereit, die viele Konsumenten bedient. Das lohnt sich, wenn ein Kontext zum geteilten Lieferanten wird und du nicht für jeden Konsumenten eine Sonderlösung pflegen willst. Der Aufwand liegt beim Upstream, dafür bleiben die Downstreams entkoppelt.

Die Faustregel: Je unpassender oder wackliger das fremde Modell, desto eher ein Anti-Corruption Layer. Je mehr Konsumenten ein Kontext bedient, desto eher ein Open Host Service. Und die strategische Entscheidung (welche Beziehung?) kommt immer vor der technischen (welche API, welches Protokoll?).

## Wann lohnt sich strategisches Design?

Strategisches Design ist kein Selbstzweck. Es lohnt sich konkret, wenn:

- Mehrere Teams an einer Codebasis arbeiten und sich sonst ständig in die Quere kommen.
- Verschiedene Fachbereiche dasselbe System nutzen und denselben Begriff unterschiedlich meinen.
- Die Domain groß genug ist, dass ein einzelnes Modell sie nicht mehr ohne Widersprüche abbildet.
- Das System über Jahre wächst und Teile davon unabhängig voneinander weiterentwickelt werden sollen.

Für ein einzelnes Team mit einer überschaubaren Domain ist der volle Apparat aus Subdomains und Context Maps dagegen Overhead. Wer eine Formular-Datenbank-Liste-Anwendung baut, gewinnt durch eine Context Map nichts außer Diagrammen.

Ehrlicherweise falle ich mit jotti selbst in die zweite Kategorie: alleiniger Entwickler, überschaubare Domain. Den Schnitt in Bounded Contexts habe ich trotzdem gemacht, schon damit jedes Paket für sich verständlich bleibt. Der Nutzen war weniger Team-Koordination als kognitive Entlastung: Wer im `kasse`-Paket arbeitet, muss nicht das `steuer`-Paket im Kopf haben.
