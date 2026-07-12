# Domain Driven Design: Wenn der Code die Fachsprache lernt

Du bist Entwickler in einem E-Commerce-Unternehmen. Die Fachabteilung meldet einen Bug: „Die Buchungsanfrage lässt sich nicht stornieren.“ Du schaust ins Log, findest einen `DELETE /booking-request/42` mit Status `204 No Content`. Alles hat funktioniert. Trotzdem ist der Bug da. Was ist passiert?

Die Fachabteilung meint mit „stornieren“ etwas anderes als dein Code mit `DELETE`. Stornieren heißt: den Datensatz behalten, den Status ändern, offene Posten rückgängig machen, den Kunden benachrichtigen. Dein `DELETE` hat den Datensatz gelöscht. Die Fachabteilung und dein Code sprechen schlicht nicht dieselbe Sprache.

Genau hier setzt **Domain Driven Design** (kurz **<abbr title="Domain Driven Design">DDD</abbr>**) an. DDD ist kein Framework und keine Bibliothek. Es ist eine Sammlung von Prinzipien, die Software so gestaltet, dass sie die Fachlichkeit widerspiegelt statt sie nur zu verwalten.

## Was ist eine Domain?

**Domain** bezeichnet das Fachgebiet, für das du Software baust. In einem E-Commerce-Unternehmen ist die Domain alles rund um Bestellungen, Zahlungen, Lagerhaltung und Kundenkommunikation. Die Domain umfasst die Geschäftsregeln, Prozesse und Begriffe, die Mitarbeiter täglich verwenden. Andere Wörter dafür: Fachlichkeit, Fachdomäne, Problemraum.

DDD dreht die übliche Reihenfolge um. Statt zuerst über Datenbanktabellen, REST-Endpunkte oder Frontend-Frameworks nachzudenken, stehen die Geschäftsprozesse und Fachbegriffe am Anfang. Die Technik folgt der Fachlichkeit.

## Das Kommunikationsproblem

Der Bug vom Anfang ist kein Ausrutscher, sondern die Regel. In vielen Projekten sprechen Fachabteilung und Entwickler zwei verschiedene Sprachen. Die Fachabteilung sagt „stornieren“, der Code sagt `DELETE`. Die Fachabteilung sagt „Bestellung aufgeben“, der Code sagt `POST /orders`. Die Fachabteilung sagt „Genehmigung erteilen“, der Code sagt `PATCH /requests/42 { "status": "approved" }`.

Das klingt harmlos, wird aber zur Fehlerquelle. Was genau passiert beim „Stornieren“? Wird ein Eintrag gelöscht oder bleibt er mit dem Status „storniert“ erhalten? Werden offene Posten rückgängig gemacht? Bekommt der Kunde eine E-Mail? Im Code steht nur `DELETE`, und die Antwort auf diese Fragen bleibt offen. Jeder Entwickler, der den Endpunkt später anfasst, muss sie erneut raten.

Ich habe für mich ein paar Symptome gesammelt, an denen ich merke, dass genau dieses Problem ignoriert wird:

- Derselbe Begriff bedeutet in verschiedenen Teams etwas anderes.
- Geschäftsregeln liegen verstreut über Controller, Services und Datenbankschichten.
- Änderungen im Fachbereich lösen unerwartete Nebeneffekte im Code aus.
- Niemand kann mehr erklären, warum das System sich so verhält, wie es sich verhält.

DDD macht dieses Problem sichtbar und liefert das Werkzeug, um es zu schließen.

## Die Ubiquitous Language

DDDs Antwort auf das Kommunikationsproblem heißt **Ubiquitous Language**: die gemeinsame, verbindliche Sprache von Fachexperten und Entwicklern. Sie entsteht aus Gesprächen zwischen beiden Seiten und wird zur einzigen Sprache im Projekt.

Die Regeln sind einfach. Begriffe der Ubiquitous Language tauchen im Code auf: in Klassen, Methoden, Modulen. Wenn sich die Sprache ändert, ändert sich der Code. Synonyme sind verboten. Entweder heißt es „Buchungsanfrage“ oder „Reservierungsanfrage“, nicht beides.

Zurück zum Beispiel: Die Fachabteilung sagt „stornieren“, nicht „löschen“. Also wird aus `DELETE /booking-request` ein `POST /buchungsanfrage-stornieren`. Der Code spricht dieselbe Sprache wie die Fachabteilung. Die Übersetzungsleistung auf Entwicklerseite (von Deutsch zu Englisch, von Fachsprache zu technischer Abstraktion) entfällt. Missverständnisse wie der vermeintliche Bug verschwinden, weil es nichts mehr zu übersetzen gibt.

Das kann auch bedeuten, dass ein deutscher Fachbegriff zwischen dem sonst englischsprachigen Code auftaucht. Das ist kein Versehen, sondern Absicht: Der Code soll sich so lesen, wie die Fachabteilung das Geschäft beschreibt.

## Fachlogik im Kern, Technik außen

Wenn du DDD anwendest, entsteht im Code ein Bereich, der ausschließlich die Fachlogik enthält. Dieser Bereich hat keine Abhängigkeit auf eine Datenbank, einen HTTP-Controller oder ein Frontend-Framework. Die Fachlichkeit in der echten Welt hat diese Abhängigkeiten auch nicht: Eine Stornierung ist eine Stornierung, egal ob sie in PostgreSQL, in MongoDB oder auf einem Zettel landet.

Genau das ist der Grund für die Trennung. Die Geschäftsregeln sind der Teil des Systems, der am schwersten zu verstehen und am teuersten falsch ist. Wenn sie mitten im SQL oder im Controller stecken, verändern sie sich bei jedem technischen Umbau mit. Liegen sie in einem eigenen Kern, kannst du sie entwickeln und testen, ohne Infrastruktur hochzufahren: keine Datenbank starten, keinen Webserver konfigurieren, keinen DOM rendern. Der Rest der Anwendung (Datenbank, API, Frontend) wird um diesen Fachkern herum gebaut.

Der Nutzen zeigt sich, sobald sich etwas ändert. Ein Datenbankwechsel berührt die Fachlogik nicht. Eine Änderung der Geschäftsregeln berührt die Infrastruktur nicht. Beides lässt sich unabhängig weiterentwickeln, und beim Lesen einer Fachklasse stört kein technischer Ballast das Verständnis der eigentlichen Regel.

## Strategisches und taktisches Design

DDD unterscheidet zwei Werkzeugkästen. [Das strategische Design](/articles/strategisches-domain-driven-design) teilt große Domänen in überschaubare Bereiche auf, mit Konzepten wie Subdomains, Bounded Contexts und Context Maps. [Das taktische Design](/articles/taktisches-domain-driven-design) liefert Bausteine für die Umsetzung im Code: Entities, Value Objects, Aggregates, Repositories und Domain Events.

Beide Werkzeugkästen sind eigenständige Themen, die je einen eigenen Artikel füllen. Der Einstieg funktioniert ohne sie. Die Ubiquitous Language und die Trennung von Fachlogik und Technik bringen bereits Klarheit, bevor du dich mit Aggregates oder Context Maps beschäftigst.

## Wann lohnt sich DDD?

DDD ist kein Allheilmittel. Der Aufwand für Modellierung, Abstimmung und Strukturierung lohnt sich erst ab einer gewissen Komplexität.

DDD lohnt sich, wenn die Domain komplex ist: viele Geschäftsregeln, die Entwickler ohne Fachexperten nicht vollständig verstehen. Wenn mehrere Teams an derselben Codebasis arbeiten. Wenn das System über Jahre wächst und sich verändert. Branchen wie Finanzen, Gesundheit, Logistik oder Recht sind typische Kandidaten.

Für einfache Datenanwendungen (Formular, Datenbank, Liste) ist DDD zu viel Aufwand. Auch wenn das Team klein ist und alle die Domain gut kennen, zahlt sich die Modellierung nicht aus. Und wenn schnelle Lieferung wichtiger ist als langfristige Wartbarkeit, ist der Overhead schwer zu rechtfertigen.

Der pragmatische Einstieg: Fang mit der Ubiquitous Language an. Die Fachsprache in den Code zu bringen kostet wenig und bringt sofort Klarheit. So halte ich es auch bei jotti, meinem Kassensystem für Vereine: Der Code spricht von Kassensitzung, Kassensturz und Tagesabschluss, in derselben Sprache wie die Menschen an der Kasse.
