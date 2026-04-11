# Strategisches Domain Driven Design: Große Domänen aufteilen

> **Entwurf** — Dieser Artikel ist ein Draft und noch nicht veröffentlicht.

Dein E-Commerce-System hat 200 Tabellen in einer Datenbank. Das Vertriebsteam sagt "Kunde" und meint Umsatzpotenzial. Das Support-Team sagt "Kunde" und meint ein offenes Ticket. Beide greifen auf dasselbe `Customer`-Objekt zu. Jede Änderung an diesem Objekt kann den jeweils anderen Bereich kaputtmachen.

Strategisches DDD liefert Werkzeuge, um große Domänen in Bereiche aufzuteilen, die sich unabhängig voneinander entwickeln lassen.

## Subdomains: Teilbereiche des Fachgebiets

Eine Subdomain ist ein Teilbereich der Domain mit eigenen Zielen. Im E-Commerce: Bestellverwaltung, Zahlungsabwicklung, Lagerhaltung, Kundenkommunikation. Nicht alle Subdomains sind gleich wichtig.

Die Core Domain ist das Kerngeschäft, der Wettbewerbsvorteil. Hier lohnt sich die höchste Sorgfalt. Supporting Domains unterstützen das Kerngeschäft, verdienen solide Arbeit, aber kein Overengineering. Generic Domains (Authentifizierung, E-Mail-Versand) löst man mit eingekaufter Software.

Die Unterscheidung bestimmt, wo du deine knappe Entwicklungszeit investierst.

## Bounded Context: Wo welches Modell gilt

Ein Bounded Context ist eine explizite Grenze, innerhalb derer ein bestimmtes Modell und eine einheitliche Sprache gelten. "Kunde" im Rechnungswesen hat andere Attribute als "Kunde" im Marketing. Zwei saubere Modelle in zwei Kontexten sind wartbarer als ein aufgeblähtes Universalmodell.

Der bestehende Artikel zu Bounded Contexts behandelt dieses Thema ausführlich.

## Context Mapping: Beziehungen zwischen Kontexten

Bounded Contexts existieren nicht isoliert. Wenn der Rechnungswesen-Kontext Kundendaten aus dem Vertriebs-Kontext braucht, entsteht eine Beziehung. Die Context Map dokumentiert diese Beziehungen: Wer liefert Daten (Upstream), wer konsumiert sie (Downstream)? Welches Integrations-Pattern wird verwendet?

Die drei häufigsten Patterns: Conformist (Downstream übernimmt das Upstream-Modell direkt), Anti-Corruption Layer (Downstream baut eine Übersetzungsschicht) und Open Host Service (Upstream stellt eine stabile API für viele Konsumenten bereit).

Die strategische Entscheidung (welche Beziehung?) kommt vor der technischen (welche API?).

## Wann lohnt sich strategisches Design?

Strategisches Design lohnt sich, sobald mehrere Teams an einer Codebasis arbeiten oder verschiedene Fachbereiche dasselbe System nutzen. Für ein einzelnes Team mit einer überschaubaren Domain ist es Overhead.
