# Erst klären, dann coden: Mein Workflow mit KI-Coding-Agents

Ein Coding-Agent hat mir in jotti, meinem Kassensystem für Vereine, einmal das Einmalpasswort für den Erst-Login umgebaut. Ursprünglich waren das sechs Ziffern. Der Agent stellte den Generator auf acht alphanumerische Zeichen um und zog das Frontend sauber nach: Das Eingabefeld verlangte jetzt acht Zeichen. Nur eine Kleinigkeit blieb liegen: In der Initial-Migration stand weiterhin der hartkodierte Argon2id-Hash des alten Codes `123456`, also sechs Ziffern. Bei jeder Neuinstallation verlangte das Eingabefeld acht Zeichen, der einzige gültige Code hatte aber sechs. Kein Login möglich, Deadlock ab dem ersten Start. Das Backend hätte das auffangen können, prüfte das Format des Codes aber gar nicht, nur ob überhaupt etwas eingegeben wurde.

Das Modell hat hier nichts falsch gerechnet. Es hat an einer Stelle geraten, die niemand vorgegeben hatte: dass Generator, Eingabefeld und der geseedete Code zusammenpassen müssen. Das ist mein Punkt. Das Problem ist selten das Modell. Das Problem sind die Lücken in der Aufgabe, denn bei jeder davon muss das Modell raten. Mir ist das oft genug passiert, um daraus einen festen Workflow aus vier Phasen zu machen: klären, planen, umsetzen, prüfen. Jede Phase hat ihren eigenen **Skill**.

> Ein Skill ist eine Markdown-Anleitung, die dem Agent für eine
> bestimmte Aufgabe eine Arbeitsweise vorgibt: welche Schritte er
> in welcher Reihenfolge geht und woran er sich dabei hält.

Ich nutze dafür Claude Code, das **Harness** meiner Wahl; das Konzept funktioniert aber mit jedem Agent, der solche Anleitungen lesen kann.

> Das Harness ist die Software rund um das Sprachmodell: die
> Werkzeuge, der Dateizugriff und die Regeln, mit denen das Modell
> arbeitet. Claude Code ist ein solches Harness.

Wie sich der Workflow anfühlt, zeige ich an einem echten, größeren Feature aus jotti.

## Das Feature: die TSE-Signatur aus dem Kassier-Pfad lösen

jotti speichert jeden Verkauf als unveränderliches Event in einem Kassenjournal, signiert von einer <abbr title="Technische Sicherheitseinrichtung">TSE</abbr>, wie es das deutsche Steuerrecht für elektronische Kassen verlangt. jotti nutzt dafür eine Cloud-TSE, kein lokales Stück Hardware. Bisher passierte die Signatur synchron im Kassier-Request: Wer auf „Kassieren“ tippte, wartete, bis die Cloud geantwortet hatte.

Stell dir eine Servicekraft im Festzelt vor. Das Vereins-WLAN ist zäh, jede Zahlung zahlt die Cloud-Roundtrips als Wartezeit, und wenn die TSE gerade nicht erreichbar ist, blockiert das Kassieren komplett. Genau im hektischsten Moment. Die Idee des Features: Die Signatur aus dem Kassier-Pfad herauslösen. Der Verkauf wird sofort gebucht, der Signaturauftrag landet in einer Warteschlange, und ein Worker im Hintergrund arbeitet ihn ab. Sofort speichern, Warteschlange, ein Worker.

```text
Kassieren ─► Event + Auftrag  (ein Commit)
                    │
                    ▼
              Warteschlange
                    │
                    ▼
             Signatur-Worker ─► TSE
```

Diese Bauform heißt transaktionale Outbox: Event und Signaturauftrag werden im selben Datenbank-Commit geschrieben, danach zieht der Worker nach. Fällt die TSE aus, bleibt der Verkauf trotzdem gebucht, der Auftrag wartet einfach länger.

## Phase 1: Klären statt raten

Am Anfang größerer Features steht bei mir ein **PRD** (Product Requirements Document): ein Dokument mit Userstories und den Entscheidungen, die feststehen müssen, bevor Code entsteht. Der `/write-prd`-Skill dreht dafür den Spieß um. Nicht ich schreibe eine möglichst perfekte Aufgabenbeschreibung, sondern der Agent interviewt mich. Er erkundet zuerst die Codebasis, und was er dort nicht beantwortet findet, fragt er in mehreren Runden ab. Die Fragen kommen als Multiple-Choice, jede Option mit einer Empfehlung samt Begründung. Ich treffe Entscheidungen, statt Prosa zu formulieren. So sieht so eine Rückfrage aus (nachgestellt):

```text
▌ Beleg ohne TSE-Daten: wann erlaubt?

 1) Nie, Beleg wartet immer auf Signatur
 2) Bei jedem Rückstand in der Queue
 3) Nur bei dokumentiertem TSE-Ausfall  ◄ Empfehlung

 › 3
```

Für dieses Feature entstand tatsächlich ein PRD (`docs/prds/prd-tse-signatur-outbox.md`), und es ging durch eine Review-Runde. Aus der kam die wichtigste Leitplanke des ganzen Features: Ein Beleg ohne TSE-Daten darf nur bei einem echten, automatisch dokumentierten Ausfall entstehen, nie bei bloßer Queue-Latenz. Das ist keine Detailfrage, sondern die rechtliche Grenze zwischen „zulässig verspätet signiert“ und „unzulässig unsigniert“. Meiner Erfahrung nach ist diese Phase der Teil des Workflows, der die meiste Fehlentwicklung verhindert. Anthropic empfiehlt dieses Interview-Muster mittlerweile selbst in den [Best Practices für Claude Code](https://code.claude.com/docs/en/best-practices).

## Phase 2: Der Plan

Aus dem PRD macht der `/create-plan`-Skill einen Umsetzungsplan. Zwei Regeln unterscheiden ihn von einem lockeren „Schreib mir mal einen Plan“. Erstens muss der Agent jede Annahme gegen den echten Code verifizieren, bevor sie in den Plan darf, mit Datei und Zeilennummer als Beleg. Zweitens stellt er auch hier strukturierte Rückfragen, bevor er plant. Das Klären zieht sich als roter Faden durch den halben Workflow; daher der Titel dieses Artikels.

Der fertige Plan bestand aus sieben Phasen, jede eine **vertikale Slice**: eine in sich abgeschlossene Funktionsscheibe, die durch alle Schichten schneidet (Datenbank, Backend, Frontend) und einzeln testbar und committbar ist. Und genau in dieser Planungsphase wurde am meisten geklärt: Der Plan durchlief mehrere Vereinfachungsrunden, bevor eine Zeile Code entstand. Zwei Fragen, die dort entschieden wurden, statt sie später zu raten: „Darf ein Beleg ohne Signatur raus?“ (nur bei dokumentiertem Ausfall, siehe die Leitplanke aus Phase 1) und „Was zählt überhaupt als Ausfall?“. Auf die zweite Frage fiel eine bewusst simple Antwort: kein Herumrechnen an Latenzen zur Laufzeit, sondern ein statusbasierter Ausfallbegriff. Solange Aufträge fehlschlagen, gilt die Queue als gestört; das ist ein Zustand, den man ablesen kann, keine Heuristik, die man kalibrieren muss.

## Phase 3: Umsetzen, eine Slice nach der anderen

Für die Umsetzung öffne ich eine frische Session, gebe dem Agent den Plan und starte den `/implement-plan`-Skill. Der arbeitet genau eine Phase des Plans ab, also eine Slice: Aufgaben von oben nach unten, jede sofort abgehakt, am Ende Build, Lint und Tests. Dann stoppt er. Dieses Stoppen ist kein Mangel, sondern der Kern der Sache. Ich reviewe und committe jede Slice einzeln, statt am Ende vor einem Riesen-Diff zu sitzen. Bei diesem Feature entstand pro Plan-Phase genau ein Commit, sieben Slices, sieben Commits, nachvollziehbar in der Historie:

```text
26cae8d  feat(tse): signaturauftrag admin verwaltung and queue monitoring (phase 6)
856e721 docs(tse): document outbox signing model across reference docs
d70ba5e feat(tse): Kassenabschluss-Gate über Signaturstatus-Funktion (Phase 5)
4f2b737 feat(tse): mark unsigned jobs when TSE unconfigured (phase 4)
44111c1 feat(tse): Fehlertaxonomie und Störungszustand des Signatur-Workers
9e003ee feat(tse): add stoerungsprotokoll, signaturstatus function and rueckstand watchdog (phase 2)
312f2a0 feat(tse): decouple TSE signing from checkout via transactional outbox
```

Die frische Session pro Phase ist mehr als Ordnungsliebe. Sie hält das **Kontextfenster** frei.

> Das Kontextfenster ist das begrenzte Arbeitsgedächtnis des
> Modells. Es füllt sich im Lauf einer Session, und je voller es
> wird, desto eher gehen frühere Anweisungen darin unter.

Zwischen meinen Phasen reist deshalb nur das jeweilige Artefakt (das PRD in die Planung, der Plan in die Umsetzung), nicht der komplette Chatverlauf. Recherche-Aufgaben lagere ich zusätzlich an **Subagents** aus.

> Ein Subagent ist ein eigenständiger Agent mit eigenem, frischem
> Kontextfenster. Er bearbeitet eine Teilaufgabe getrennt und
> liefert nur sein Ergebnis zurück, nicht seinen ganzen Verlauf.

Ein Nebeneffekt der Phasentrennung: Jede Phase kann ein anderes Modell bekommen. Fürs offene Klären nehme ich das gründlichste Modell, das ich bekommen kann (aktuell Fable 5), fürs Planen ein starkes Reasoning-Modell (Opus 4.8), fürs Umsetzen ein schnelles, zuverlässiges (Sonnet 5). Diese Namen sind vermutlich schneller veraltet als der Rest dieses Artikels. Das Muster dahinter bleibt: die meiste Denkleistung in die offenen Fragen stecken, nicht in die klar umrissene Umsetzung.

## Phase 4: Das Audit schließt den Kreis

Nach der Umsetzung kommt der `/code-audit`-Skill, wieder in einer frischen Session. Für dieses Feature gab es einen eigenen, dedizierten Audit des ganzen Umbaus: Prüft die Implementierung gegen den Plan, gegen die Rechtsquellen und über alle Schichten hinweg? Der frische Kontext ist hier der eigentliche Trick. Ein Reviewer, der den Code nicht selbst geschrieben hat, verteidigt ihn auch nicht.

Der Audit fand zwei Klassen von Findings. Die eine waren klare Detailfehler; zwei davon habe ich direkt entschieden und umgesetzt (Ausfalldokumentation im Seed, ein Compliance-Absatz zur asynchronen Signierung). Die andere war interessanter. Drei Findings drehten sich darum, die Admin-Oberfläche für die Signaturaufträge zu härten: ein möglicher Race beim Verwerfen, No-Op-Antworten, ein fehlendes Listen-Limit. Statt sie einzeln zu härten, habe ich eine Richtungsentscheidung getroffen: Diese Verwaltungsfunktionen braucht ein nicht-technischer Vereinshelfer gar nicht. Wer auf einen deterministischen Fehler „erneut versuchen“ klickt, produziert nur neue Fehlversuche. Also: verkleinern statt härten, Monitoring behalten, die Einzelauftrags-Verwaltung löschen. Daraus wurde ein Folge-PRD (`prd-tse-admin-vereinfachung.md`) und dessen Umsetzung.

Und damit schließt sich der Kreis. Der Audit fand nicht nur Fehler, er löste das nächste Feature aus, diesmal eines, das durch Löschen entstand. Der Workflow ist kein Fließband mit einem Ende, sondern ein Kreislauf, in dem Phase 4 die nächste Phase 1 füttert.

Eine Warnung gehört hierher, und dieser Fall ist ihr bestes Beispiel: Ein Audit findet immer etwas, aber nicht jedes Finding verdient einen Fix. Die drei „Härtungs“-Findings pflichtbewusst abzuarbeiten hätte Abstraktionen, Guards und Tests für eine Funktion gebaut, die besser ganz verschwindet. Ich behandle die Liste als priorisierte Vorschläge, nicht als Pflichtenheft. Und nicht jede Änderung braucht den vollen Apparat: Der OTP-Bug vom Anfang war ein Zweizeiler, kein PRD, kein Sieben-Phasen-Plan. Klären lohnt sich dort, wo noch niemand die Anforderungen durchdacht hat, nicht dort, wo der Diff in einen Satz passt.

## Wann lohnt sich das?

Der Workflow spielt seine Stärken aus, wenn die Codebasis mehrere Schichten hat, wenn Fehler teuer sind und wenn Arbeit über mehrere Sessions, Personen oder Agents verteilt ist. Auf jotti trifft alles drei zu: Go-Backend, React-Frontend und PostgreSQL müssen konsistent bleiben, und ein einmal signiertes Event lässt sich nicht mehr wegräumen, weil das Kassenjournal append-only ist. Genau bei der Outbox zeigt sich das: Ob ein Beleg ohne Signatur rausgehen darf, ist eine rechtliche Frage, keine, die man beim Coden nebenbei entscheidet.

Genauso klar sind die Gegenanzeigen: Für einen Prototyp, ein Wegwerf-Skript oder eine triviale Änderung ist das alles Overkill. Anthropic formuliert es in den eigenen Best Practices sinngemäß so: Wenn du den Diff in einem Satz beschreiben kannst, brauchst du keinen Plan. Ich halte mich selbst daran; Tippfehler und kleine Umbenennungen gehen bei mir ohne PRD, ohne Plan und ohne schlechtes Gewissen.

## Fazit

Der Gewinn dieses Workflows liegt nicht darin, dass der Agent besseren Code schreibt. Er liegt darin, dass die Entscheidungen schon gefallen sind, wenn der Code entsteht. Ob ein Beleg ohne TSE-Daten rausgehen darf, stand in unserem Fall im PRD, bevor die erste Zeile geschrieben war. Raten war keine Option mehr, weder für den Agent noch für mich.

Das Feature stammt aus [jotti](https://jotti.rocks), meinem Kassensystem für Vereine und gemeinnützige Organisationen: source-available und ausgelegt auf die <abbr title="Kassensicherungsverordnung">KassenSichV</abbr>. Die Pläne und Audits, von denen dieser Artikel erzählt, liegen dort als Markdown-Dateien im Repository. Unter [github.com/nicograef/jotti](https://github.com/nicograef/jotti) kannst du sie dir ansehen.
