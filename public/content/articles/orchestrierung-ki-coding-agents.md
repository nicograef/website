# Der Orchestrator übernimmt: KI-Agents setzen nachts meinen Plan um

Am Ende von Teil 1 stand ein Versprechen. Ich hatte dort meinen Workflow beschrieben, klären, planen, umsetzen, prüfen, jede Phase in ihrer eigenen Session, ich als Übergabestelle dazwischen. Im Bonus skizzierte ich die nächste Ausbaustufe, dieselbe Phasentrennung auf mehrere Agents verteilt, und schob sie als Experiment weg: „Dazu, samt paralleler Agents und Git Worktrees, schreibe ich einen eigenen Artikel." Hier ist er. Und es ist kein Experiment mehr.

Zwischen Teil 1 und heute habe ich etwas gemacht, das ich mir vorher nicht ganz zugetraut hätte. Ich habe abends einen Plan an einen Agent übergeben, das Notebook offen gelassen und mich schlafen gelegt. Über Nacht hat er ein Feature aus sieben Phasen umgesetzt und jede Phase einzeln auf `main` committet, ohne dass ich einen einzigen Schritt freigegeben habe. Das Kommando `/effort ultracode` schaltet Claude Code für diese eine Session in die höchste Stufe:

> Set effort level to ultracode (this session only): xhigh + dynamic workflow orchestration

Danach die Anweisung, wörtlich so getippt:

> ultracode: implement the plan @docs/plans/plan-service-redesign.md autonomously and commit each phase to main. Verify, especially the visuals e2e, before or after each commit. I am going to sleep now, the notebook and claude session is kept on.

Am nächsten Morgen lagen sieben Commits auf `main`, verifiziert, aber bewusst nicht gepusht. Wie das sicher gehen kann und wo es trotzdem gehakt hat, ist der Rest dieses Artikels.

## Ein paar Begriffe vorab

Teil 1 hat Skill, Harness, Subagent, Kontextfenster und vertikale Slice schon erklärt; die setze ich hier voraus. Und **ultracode** aus dem Kommando oben ist keine eigene Anleitung, die ich geschrieben habe, sondern eine Effort-Stufe von Claude Code, „xhigh" plus dynamische Workflow-Orchestrierung: Das Modell darf seine Arbeit selbst in parallele Teilaufgaben zerlegen. Zwei Begriffe brauchen mehr als einen Satz.

Der Agent, der oben den Plan entgegennimmt, ist der **Orchestrator**.

> Der Orchestrator, auch Controller-Agent, ist die oberste
> Session. Er zerlegt den Plan, verteilt die Arbeit, lässt die
> Prüfungen laufen und ist der Einzige, der committet.

Die Arbeit selbst macht ein **Worker**.

> Ein Worker (Implementer-Worker) ist ein Subagent mit frischem
> Kontextfenster, der genau eine Slice umsetzt. Er darf Dateien
> ändern, aber nicht committen.

Verteilt und koordiniert werden die Worker über ein Workflow-Skript, den eingebauten Fan-out-Mechanismus von Claude Code, der Subagents startet und ihre Ergebnisse einsammelt. Den zweiten, adversarialen Reviewer aus Teil 1 nutze ich hier weiter; seine Aufgabe bleibt, die Lösung zu widerlegen statt zu bestätigen.

## Die Nacht, Phase für Phase

Der Plan war `plan-service-redesign.md`, ein Redesign des Service-Bereichs von jotti, meinem Kassensystem für Vereine, in sieben vertikalen Slices. Der Orchestrator baute daraus eine Aufgabenliste: erst die Referenz-Screenshots vorbereiten, dann die sieben Phasen der Reihe nach, am Schluss ein Abschlussbericht. Für jede Phase startete er ein eigenes Workflow-Skript.

Innerhalb einer Phase lief immer dasselbe Muster. Ein Implementer-Worker setzte die Slice um. Sein Diff ging an zwei Reviewer parallel, einen skeptischen und einen adversarialen, die den Code lasen, bevor er committet war. Ihre Findings gingen an einen dritten Worker, der sie behob. So sieht das Muster pro Phase aus, nachgebaut (die echten Skripte sind länger):

```text
Controller  (Plan → 1 Workflow pro Phase)
     │
     ▼
 Implement   1 Opus-Worker, eine Slice
     │
     ▼
 Review ×2   Opus: skeptisch + adversarial
     │
     ▼
 Fix         1 Opus-Worker
     │
     ▼
 Gate+Commit  Controller: make check,
              E2E 24/24, Visual-Diff
```

Der entscheidende Schnitt liegt beim Committen. In jedem Phasenskript steht dieselbe Regel für alle Beteiligten: „Repo: …, Branch main, direkt im Working Tree arbeiten. NIEMALS committen, NIEMALS pushen." Die Worker ändern also Dateien, aber die Commit-Hoheit bleibt beim Orchestrator. Erst wenn er selbst die Prüfungen bestanden sieht, `make check` grün, die volle Playwright-Suite bei 24/24, der visuelle Abgleich gegen die exportierten Design-Frames stimmig, macht er den Commit. Genau ein Commit pro Phase, sieben Phasen, sieben Commits. Jede Commit-Message endet mit einer Zeile wie „Phase 3 of docs/plans/plan-service-redesign.md.", und als alle sieben lagen, entfernte ein letzter Commit den abgearbeiteten Plan wieder:

```text
$ git log --grep='plan-service-redesign' --oneline
2c2bb40 feat(service): confirmation drawer polish …
88b14fd feat(kasse): optional user comment on reboo…
f7a56f4 feat(service): consolidated dashboard and d…
cbd91f6 feat(service): scannable history rows with …
355aa00 feat(service): select-all and remaining bal…
a28d7cf feat(service): flat variant list with stick…
2c2b666 feat(service): add bottom dock, unified ste…
```

Eine Phase fiel aus dem Muster. Phase 6 rührte an das Event-Schema des Kassenjournals, und das ist eingefroren, weil daran aufbewahrungspflichtige Kassendaten in produktiven Instanzen hängen. Für diese eine autorisierte Ausnahme hängte der Orchestrator einen dritten Reviewer an, den „Contract-Wächter", ausdrücklich mit dem stärksten Modell: `model: 'fable', effort: 'xhigh'`. Der prüfte Punkt für Punkt, jeweils `VERIFIZIERT` oder `VERLETZT` mit Beleg, und gab am Ende ein `GESAMTURTEIL` aus. Es lautete `CONTRACT-SICHER`: das neue Kommentarfeld ist additiv, alte Events bleiben byte-gleich lesbar.

Damit zum Thema Modelle, denn hier steckt eine Entscheidung. Das Prinzip ist simpel: mechanische, klar umrissene Arbeit bekommt ein günstiges, schnelles Modell (Sonnet), der Standard-Worker und auch der Standard-Reviewer laufen auf Opus, und das teuerste, gründlichste Modell (Fable) hebe ich mir für die wenigen Stellen auf, an denen sich Spitzenreasoning wirklich auszahlt. In dieser Nacht sah die echte Verteilung so aus: Implementer auf Opus, beide Reviewer auf Opus, Fable genau einmal, beim Contract-Wächter in Phase 6. Wie in Teil 1 gilt: Diese Modellnamen veralten schneller als das Muster dahinter.

## Wo es gehakt hat

Das klingt sauber, und der Teil, der mich überzeugt, ist genau der unsaubere. Die Prüfungen des Orchestrators fingen in drei Phasen Fehler, die beide Reviewer übersehen hatten. In Phase 2 machte die neue flache Produktliste Variantennamen wie „Normal" DOM-weit mehrdeutig, ein Test-Helfer griff mit `.last()` das falsche Produkt (bestellt wurde Brezel statt Bratwurst). In Phase 6 nahm eine neue Test-Assertion einen falschen Autotext-Wortlaut an. In Phase 7 kollidierte ein Matcher mit dem neuen Hilfetext. Der Bericht fasst es so zusammen: „Kein einziger davon war ein Produktcode-Fehler." Alle drei steckten in den Tests und Specs, und trotzdem hätte sie ohne die E2E-Gates niemand bemerkt. Genau dafür sind die Gates da: Sie fangen das, was ein Reviewer, der nur den Diff liest, nicht sehen kann.

Der zweite Haken ist unangenehmer. In einem anderen, größeren Lauf, einem Aufräum-Audit über die ganze Codebasis (130 Agent-Aufrufe, 15 von 15 Experten, 82 Rohbefunde, nach Deduplizierung 66), sollte jeder Befund per Mehrheitsvotum mehrerer adversarialer Verifizierer bestätigt oder widerlegt werden. Mitten drin ging dem Verifizierer-Modell das Guthaben aus:

```text
[verify:ops-smoke.sh] failed: You're out of
usage credits. Run /usage-credits to keep
using Fable 5 or /model to switch models.
```

Das Ergebnis am Ende: „24 bestätigt, 2 widerlegt, 40 unsicher". Die 40 Unsicheren sind zum großen Teil Befunde, deren Verifikationsvoten nie fertig liefen, weil das Guthaben weg war. Der adversariale Prüfschritt, das Herzstück meiner Absicherung, war ab da löchrig. Kein Absturz, keine Fehlermeldung im Ergebnis, nur ein leiser Qualitätsverlust, den man erst sieht, wenn man die Spalte „unsicher" ernst nimmt.

Der dritte Haken, in einer späteren Nacharbeits-Session zum selben Redesign: Dort scheiterte der Review-Agent einer Phase komplett, „StructuredOutput retry cap (5) exceeded", fünf Fehlversuche ohne gültige Ausgabe. Direkt danach in der Logzeile darunter: „Phase 7 committed: 69b55a5". Die Phase wurde committet, obwohl ihr Reviewer ausgefallen war. Das Harness hat den Ausfall nicht als Stopp behandelt, sondern protokolliert und weitergemacht. Für einen unbeaufsichtigten Lauf ist das ein echtes Risiko, und es ist einer der Gründe, warum am Ende trotzdem ich draufschaue.

## Der Kontrast: Git Worktrees

In der Nacht liefen die Phasen nacheinander, ein Worker zur Zeit, kein Worktree im Spiel. Sobald aber mehrere Implementer gleichzeitig am selben Repo arbeiten sollen, brauchen sie Isolation über einen **Git Worktree**.

> Ein Git Worktree ist eine zweite Arbeitskopie desselben
> Repos auf einem eigenen Branch. Zwei Agents können so
> parallel Dateien ändern, ohne sich gegenseitig zu
> überschreiben.

Genau so lief ein anderer Audit, diesmal mit drei parallelen Opus-Implementern. Jeder bekam seinen eigenen Worktree und Branch, etwa `worktree-wf_9b766b40-eb9-1` unter `.claude/worktrees/`, committete dort in seinen Branch (jeder mit eigenem `commitSha` und `checksPassed: true`) und wurde am Ende per `git worktree remove --force` und `git branch -D` wieder aufgeräumt. Damit sie sich nicht doch ins Gehege kommen, stand in ihren Aufträgen eine harte Grenze („VERBOTEN: irgendetwas in /home/nico/r/jotti anfassen") und ein Hinweis, dass „Port 5432 für einen anderen Agenten reserviert" sei.

Der Unterschied zur Nacht ist bewusst. Wo Agents parallel schreiben, sind Worktrees die saubere Trennung. Wo sie nacheinander laufen, reicht die einfachere Regel „Worker ändern, der Controller committet", und der Worktree-Aufwand entfällt. Und parallel ist nicht gratis. Mein eigener Skill dazu warnt: „Parallel agents cost roughly 15× the tokens of a single linear pass, so reserve this for genuinely independent, each-substantial work." Fan-out ist ein Werkzeug für wirklich unabhängige, je gewichtige Arbeit, kein Default.

## Warum ich das über Nacht laufen lasse

„Läuft, während du schläfst" klingt nach Kontrollverlust. Was es tragbar macht, sind ein paar Leitplanken, die ich nicht dem Modell überlasse. Die Commit-Hoheit habe ich schon erwähnt: Worker dürfen im Working Tree ändern, aber nie committen. Dazu blockiert ein Hook in der Konfiguration jeden erzwungenen Push und jedes Umgehen der Commit-Prüfungen schon auf Kommando-Ebene, bevor das Modell den Befehl überhaupt absetzen kann. Ein weiterer Skill sorgt dafür, dass ein fertiger Branch nur integriert wird, wenn die Tests grün sind; scheitern sie, wird nichts gemergt, gepusht oder verworfen. Und abgearbeitete Pläne lösche ich, damit `docs/plans/` eine Arbeitswarteschlange bleibt und kein Friedhof. Nichts davon ist spektakulär. Zusammen sorgen sie dafür, dass der schlimmste Ausgang einer schiefgelaufenen Nacht ein paar verwerfbare Commits auf einem lokalen `main` sind, kein kaputter Remote.

## Woher der Plan kam

Eine Sache fehlt noch, weil sie zeigt, dass am Anfang der Kette wieder ein Mensch und ein Modell zusammen entscheiden. Das Redesign entstand nicht als Plan, sondern als Design. Claude hat mir den neuen Service-Bereich als Paket hochauflösender HTML-Mockups entworfen, sogenannte Design Components (`.dc.html`), mit einer README, die direkt an den umsetzenden Agent gerichtet war. Darin steht ausdrücklich, was diese Dateien nicht sind: „Die beiden `.dc.html`-Dateien in diesem Paket sind Design-Referenzen in HTML … Sie sind kein Produktionscode." Und ein fertiger Vorschlag, wie man sie benutzt: „Pro Phase eine Session/PR: Lies docs/design_handoff_service_redesign/README.md und setze Phase 1 um. Halte dich an bestehende Muster in frontend/src." Aus diesem Handoff wurde `plan-service-redesign.md`, und dieser Plan war es, den der Orchestrator in der Nacht abgearbeitet hat. Entwurf, Plan, Umsetzung, eine durchgehende Kette.

## Was bleibt

Die Orchestrierung ist real und sie spart mir echte Zeit. Aber ehrlich bleibt: Nicht die Agents machen sie sicher, sondern die Gates und der Blick, der am Ende noch draufgeht. Die überzeugendste Stelle dieser Nacht war nicht, dass sieben Phasen durchliefen, sondern dass die Prüfungen drei Fehler fingen, die beide Reviewer übersehen hatten. Der morgendliche Bericht sagte: „Das Service-Redesign ist komplett: alle 7 Phasen sind umgesetzt, verifiziert und auf main committet (nicht gepusht)." Der letzte Punkt war „Push steht aus", bewusst, ohne dass ein Auftrag dazu vorlag. Genau da endet die Autonomie, mit Absicht. Die Agents haben getippt, verglichen und committet. Die Entscheidung, das auf einen Server zu schieben, und der letzte prüfende Blick sind bei mir geblieben. Das ist mir die zwei Zeilen vor dem Schlafengehen wert.
