---
title: Was ist Domain Driven Design?
description: Eine Einführung in Domain Driven Design (DDD) und wie es hilft, Software besser an die Fachlichkeit anzupassen.
author: Nico Gräf
author_url: https://nicograef.com
date: 2025-01-31
tags:
  - Domain Driven Design
  - Software Architecture
---

# Was ist Domain Driven Design?

> Diese Erklärung zu DDD wendet sich an Entwickler und konzentriert sich auf das sogenannte “tactical design”. Für Architekten ist wahrscheinlich das “strategic design” relevanter.

**Domain** ist ein englischer Fachbegriff aus der Softwareentwicklung und bezeichnet den Fachbereich oder das Anwendungsgebiet, für das eine Software entwickelt wird (z.B. Buchhaltung, Gastronomie). Die Domain umfasst die Geschäftslogik, Regeln, Prozesse und Konzepte, die für das jeweilige Anwendungsgebiet relevant bzw. spezifisch sind. Andere Wörter dafür sind Fachlichkeit, Geschäftslogik, Geschäftsprozesse, Fachdomäne oder Problemraum.

Wie der Name schon sagt, wird bei DDD die Softwareentwicklung von der Fachlichkeit "getrieben". Das bedeutet, wir lösen uns erstmal von technischen Details, CRUD-Operationen oder Frontend-Framework-Diskussionen, und konzentrieren uns stattdessen darauf, die Geschäftsprozesse und -regeln zu verstehen und ein gemeinsames Verständnis, sowie eine gemeinsame Sprache mit den Fachexperten aufzubauen (vgl. Ubiquitous Language).

![Beispiel API Design in DDD](/assets/img/articles/ddd-api-example.png)
_Beispiel API Design in DDD_

Im Code soll diese Fachlichkeit dann möglichst nahe an der Fachsprache und den Modellen der Domäne, sowie unabhängig von der jeweiligen Technik umgesetzt werden. Im bestenfall findet man eine Klasse / ein Modul / ein Paket / eine Funktion mit dem selben Namen wie das Fachkonzept (also so, wie es der Kunde oder die Fachabteilung auch benennen oder beschreiben würde). Das kann auch mal bedeuten, dass ein deutscher Fachbegriff zwischen dem (meist) englischsprachigen Code auftauch.

Ein Beispiel: Im Backend wird aus dem Endpunkt `DELETE /booking-request` mit DDD der Endpunkt `POST /buchungsanfrage-stornieren`. Denn die Mitarbeiter sagen "eine Buchungsanfrage wird storniert" und nicht "eine Bookingrequest wird gelöscht". Dadurch wird die Übersetzungsleistung auf Entwicklerseite reduziert &mdash; von deutsch zu englisch und von Fachsprache in technische Abstraktion &mdash; und Misverständnisse vermieden.

Dieser Teil der Software sollte keine Abhängigkeit auf eine Datenbank, einen HTTP-Controller oder ein Frontend-Framework haben &mdash; das hat die Fachlichkeit in der "echten" Welt auch nicht. So wie man bei Test-driven Development (TDD) zuerst die Tests schreibt, würde man bei DDD diesen Teil der Software zuerst modellieren und implementieren. Vorteil: Man braucht keine technische Infrastruktur, um die Fachlichkeit zu entwickeln und zum Testen muss man auch keine Datenbank hochfahren, einen Webserver starten oder einen DOM rendern.

![Vereinfachte Onion-Architektur nach Domain-Driven Design](/assets/img/articles/ddd-onion-architecture.png)
_Vereinfachte Onion-Architektur nach Domain-Driven Design_

Der Rest der Software (Datenbank, API, Frontend etc.) wird dann um diese Fachlichkeit herum gebaut. Dadurch wird die Fachlichkeit von technischen Details entkoppelt und kann leichter verstanden, getestet und weiterentwickelt werden. Ebenso können sich Geschäftsprozesse oder -regeln ändern, ohne dass die technische Infrastruktur angepasst werden muss und technische Änderungen (z.B. Wechsel der Datenbank oder des Frontend-Frameworks) haben keinen Einfluss auf die Fachlichkeit.
