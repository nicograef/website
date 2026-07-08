# Erst klären, dann coden: Mein Workflow mit KI-Coding-Agents

Du gibst einem Coding-Agent eine Aufgabe, und er legt sofort los. Keine Rückfrage, kein Plan, einfach Code. Zwanzig Minuten später hast du eine Lösung, die kompiliert und sogar Tests mitbringt, aber nicht das tut, was du gemeint hast. Das Problem ist selten das Modell. Das Problem sind die Lücken in deiner Aufgabenbeschreibung, denn bei jeder davon muss das Modell raten.

Mir ist das oft genug passiert, um daraus Konsequenzen zu ziehen. Für jotti, mein Kassensystem für Vereine, arbeite ich deshalb in einem festen Workflow aus vier Phasen: klären, planen, umsetzen, prüfen. Jede Phase hat eine eigene Anleitung, einen sogenannten **Skill**. Das ist eine Markdown-Datei, die dem Agent Schritt für Schritt vorgibt, wie er in der jeweiligen Phase arbeiten soll. Ich nutze dafür Claude Code; das Konzept funktioniert aber mit jedem Agent, der solche Anleitungen lesen kann. Wie sich der Workflow anfühlt, zeige ich an einem echten Feature aus jotti.

## Das Feature: ein Doppel-Tap, zwei signierte Events

jotti speichert jeden Verkauf als unveränderliches Event in einem Kassenjournal, signiert von einer <abbr title="Technische Sicherheitseinrichtung">TSE</abbr>, wie es das deutsche Steuerrecht für elektronische Kassen verlangt. Was einmal signiert ist, lässt sich nicht mehr löschen.

Jetzt stell dir eine Servicekraft im Festzelt vor. Sie tippt auf „Kassieren“, das WLAN verschluckt die Antwort, und die App schickt den Request erneut. Oder sie tippt aus Ungeduld gleich zweimal. In beiden Fällen kommen zwei Requests für denselben Verkauf an, und im schlimmsten Fall landen zwei signierte Events im Kassenjournal: ein Verkauf, doppelt gebucht, nicht löschbar.

Gefunden habe ich diese Lücke nicht im Live-Betrieb, sondern durch ein Code-Audit, die vierte Phase meines Workflows. Warum das kein Widerspruch ist, dazu komme ich am Ende. Erst einmal der Weg des Features durch die vier Phasen.

## Phase 1: Klären statt raten

Am Anfang größerer Features steht bei mir ein **PRD** (Product Requirements Document): ein Dokument mit Userstories und den Entscheidungen, die feststehen müssen, bevor Code entsteht. Der `/write-prd`-Skill dreht dafür den Spieß um. Nicht ich schreibe dem Agent eine möglichst perfekte Aufgabenbeschreibung, sondern der Agent interviewt mich. Er erkundet zuerst die Codebasis, und was er dort nicht beantwortet findet, fragt er in mehreren Runden ab. Die Fragen kommen als Multiple-Choice, jede Option mit einer Empfehlung samt Begründung. Ich treffe Entscheidungen, statt Prosa zu formulieren. Anthropic empfiehlt dieses Interview-Muster mittlerweile selbst in den [Best Practices für Claude Code](https://code.claude.com/docs/en/best-practices). Meiner Erfahrung nach ist es der Teil des Workflows, der die meiste Fehlentwicklung verhindert.

Ehrlicherweise: Für das Feature aus diesem Artikel gab es gar kein PRD. Das Audit-Finding war schon präzise genug, Problem und Lösungsidee standen fest. Nicht jede Änderung braucht den vollen Apparat. Ein PRD lohnt sich dort, wo noch niemand die Anforderungen durchdacht hat, nicht dort, wo sie schon auf dem Tisch liegen.

## Phase 2: Der Plan

Aus dem PRD (oder eben direkt aus dem Finding) macht der `/create-plan`-Skill einen Umsetzungsplan. Zwei Regeln unterscheiden ihn von einem lockeren „Schreib mir mal einen Plan“. Erstens muss der Agent jede Annahme gegen den echten Code verifizieren, bevor sie in den Plan darf, mit Datei und Zeilennummer als Beleg. Zweitens stellt er auch hier strukturierte Rückfragen, bevor er plant. Das Klären zieht sich als roter Faden durch den halben Workflow; daher der Titel dieses Artikels.

Der fertige Plan besteht aus **vertikalen Slices**: kleinen, in sich abgeschlossenen Funktionsscheiben, die durch alle Schichten schneiden (Datenbank, Backend, Frontend) und einzeln testbar und committbar sind. Jeder Slice bringt Akzeptanzkriterien zum Abhaken mit.

Bei unserem Feature hat genau diese Phase die Fragen beantwortet, bei denen ein drauflos codender Agent geraten hätte. Die Lösungsidee war schnell klar: eine eindeutige ID pro Kassiervorgang, die Duplikate erkennbar macht. Aber die Details waren offen. Erzeugt der Client diese Vorgangs-ID oder der Server? Bekommt ein doppelter Request einen Fehler oder dieselbe Antwort wie der erste? Gilt die Deduplizierung pro Event-Typ oder global? Am Ende standen die Antworten im Plan. Der Client erzeugt pro Vorgang eine Pflicht-ID. Ein Duplikat bekommt dieselbe Erfolgsantwort wie der erste Versuch, dieselbe Anfrage zweimal ergibt also trotzdem nur eine Buchung. Und die Transaktion rollt komplett zurück, bevor ein zweiter Signaturauftrag an die TSE entsteht.

## Phase 3: Umsetzen, eine Sektion nach der anderen

Für die Umsetzung öffne ich eine frische Session, gebe dem Agent den Plan und starte den `/implement-plan`-Skill. Der arbeitet genau eine Sektion des Plans ab, also einen Slice: Aufgaben von oben nach unten, jede sofort abgehakt, am Ende Build, Lint und Tests. Dann stoppt er. Dieses Stoppen ist kein Mangel, sondern der Kern der Sache. Ich reviewe und committe jeden Slice einzeln, statt am Ende vor einem Riesen-Diff zu sitzen.

Die frische Session pro Phase ist mehr als Ordnungsliebe. Das Kontextfenster eines Modells füllt sich schnell, und je voller es wird, desto eher gehen frühere Anweisungen unter. Zwischen meinen Phasen reist deshalb nur das jeweilige Artefakt (das PRD in die Planung, der Plan in die Umsetzung), nicht der komplette Chatverlauf. Recherche-Aufgaben lagere ich zusätzlich an Subagents aus, die in ihrem eigenen Kontext lesen und nur ihr Ergebnis zurückmelden.

Ein Nebeneffekt der Phasentrennung: Jede Phase kann ein anderes Modell bekommen. Fürs offene Klären nehme ich das gründlichste Modell, das ich bekommen kann (aktuell Fable 5), fürs Planen ein starkes Reasoning-Modell (Opus 4.8), fürs Umsetzen ein schnelles, zuverlässiges (Sonnet 5). Diese Namen sind vermutlich schneller veraltet als der Rest dieses Artikels. Das Muster dahinter bleibt: die meiste Denkleistung in die offenen Fragen stecken, nicht in die klar umrissene Umsetzung.

## Phase 4: Das Audit schließt den Kreis

Nach der Umsetzung kommt der `/code-audit`-Skill, wieder in einer frischen Session. Er prüft, ob die Schichten noch zusammenpassen: Frontend-Typen gegen Backend-Handler, Backend gegen Datenbankschema. Er verfolgt exemplarische Abläufe von der App bis zur SQL-Query und sucht Stellen, die komplizierter sind als nötig. Der frische Kontext ist hier der eigentliche Trick: Ein Reviewer, der den Code nicht selbst geschrieben hat, verteidigt ihn auch nicht.

Und damit zurück zum Anfang. Genau aus so einem Audit stammt das Feature dieses Artikels: Das Finding landete auf einer Liste, wurde geklärt, geplant und umgesetzt. Der Workflow ist kein Fließband mit einem Ende, sondern ein Kreislauf, in dem Phase 4 die nächste Phase 1 füttert.

Eine Warnung gehört hierher: Ein Audit findet immer etwas. Das heißt nicht, dass jedes Finding einen Fix verdient. Wer alle Findings pflichtbewusst abarbeitet, baut irgendwann Abstraktionen und Tests für Fälle, die nie eintreten. Ich behandle die Liste als priorisierte Vorschläge, nicht als Pflichtenheft.

## Wann lohnt sich das?

Der Workflow spielt seine Stärken aus, wenn die Codebasis mehrere Schichten hat, wenn Fehler teuer sind und wenn Arbeit über mehrere Sessions, Personen oder Agents verteilt ist. Auf jotti trifft alles drei zu: Go-Backend, React-Frontend und PostgreSQL müssen konsistent bleiben, und ein doppelt signiertes Event lässt sich nicht wegräumen, weil das Kassenjournal append-only ist.

Genauso klar sind die Gegenanzeigen: Für einen Prototyp, ein Wegwerf-Skript oder eine triviale Änderung ist das alles Overkill. Anthropic formuliert es in den eigenen Best Practices sinngemäß so: Wenn du den Diff in einem Satz beschreiben kannst, brauchst du keinen Plan. Ich halte mich selbst daran; Tippfehler und kleine Umbenennungen gehen bei mir ohne PRD, ohne Plan und ohne schlechtes Gewissen.

## Fazit

Der Gewinn dieses Workflows liegt nicht darin, dass der Agent besseren Code schreibt. Er liegt darin, dass die Entscheidungen schon gefallen sind, wenn der Code entsteht. Was bei einem doppelten Request passiert, stand in unserem Fall im Plan, bevor die erste Zeile geschrieben war. Raten war keine Option mehr, weder für den Agent noch für mich.

Das Feature stammt aus [jotti](https://jotti.rocks), meinem Kassensystem für Vereine und gemeinnützige Organisationen: source-available und ausgelegt auf die <abbr title="Kassensicherungsverordnung">KassenSichV</abbr>. Die Pläne und Audits, von denen dieser Artikel erzählt, liegen dort als Markdown-Dateien im Repository. Unter [github.com/nicograef/jotti](https://github.com/nicograef/jotti) kannst du sie dir ansehen.
