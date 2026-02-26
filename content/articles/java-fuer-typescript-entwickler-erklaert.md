---
title: Java für TypeScript-Entwickler erklärt
description: Du kommst aus der TypeScript/Node.js-Welt und willst Java verstehen? Dieser Artikel erklärt die Java-Plattform, die Sprache und die Datenmodellierung — mit Vergleichen zu TypeScript.
author: Nico Gräf
author_url: https://nicograef.com
date: 2025-02-26
tags:
  - Java
  - TypeScript
  - Software Architecture
  - Backend
---

# Java für TypeScript-Entwickler erklärt

Du kommst aus der TypeScript- und Node.js-Welt und willst Java verstehen? Dann ist dieser Artikel für dich. Statt bei Null anzufangen, nutzen wir dein bestehendes Wissen als Brücke: Für jedes Java-Konzept zeige ich dir das Äquivalent in TypeScript oder Node.js, damit du eine Intuition aufbauen kannst.

Dieser Artikel behandelt die Java-Plattform, die Sprache selbst und die Datenmodellierung. Im Folgeartikel [Spring Boot für TypeScript-Entwickler erklärt](/articles/spring-boot-fuer-typescript-entwickler-erklaert) geht es dann um das Spring-Boot-Framework — Javas Antwort auf Express.js.

## Die Java-Plattform: JDK, JRE und JVM

In der Node.js-Welt installierst du Node.js und hast damit alles: Runtime, Paketmanager (npm), und los geht's. In Java gibt es drei Schichten — von außen nach innen:

| Schicht                            | Enthält                          | TypeScript-Analogie                 |
| ---------------------------------- | -------------------------------- | ----------------------------------- |
| **JDK** (Java Development Kit)     | Compiler (`javac`) + Tools + JRE | Node.js + npm + TypeScript-Compiler |
| **JRE** (Java Runtime Environment) | JVM + Standardbibliothek         | Node.js Runtime (ohne npm)          |
| **JVM** (Java Virtual Machine)     | Führt Bytecode aus               | V8-Engine in Node.js                |

**Hinweis:** Seit Java 11 wird das JRE nicht mehr separat angeboten — du installierst immer das JDK. Die Trennung ist aber konzeptionell weiterhin nützlich.

Die **JVM** ist der Kern. Sie nimmt kompilierten **Bytecode** (`.class`-Dateien) und führt ihn aus — plattformunabhängig. Ob Linux, Mac oder Windows: Derselbe Bytecode läuft überall, weil die JVM die Brücke zum Betriebssystem bildet.

## Der Build-Prozess

In TypeScript kompilierst du `.ts`-Dateien zu `.js` und führst sie dann mit Node.js aus. In Java funktioniert das ähnlich:

1. **`javac`** kompiliert `.java`-Dateien zu `.class`-Dateien (Bytecode) — ähnlich wie `tsc` TypeScript zu JavaScript kompiliert.
2. **`java`** startet die JVM und führt die `.class`-Dateien aus — ähnlich wie `node dist/index.js`.

In der Praxis tippt man diese Befehle aber nicht einzeln ein. Dafür gibt es **Maven**.

## Maven — Javas Paketmanager und Build-Tool

Maven ist Javas Dependency-Manager und Build-Tool in einem. In der Node.js-Welt verteilt sich das auf mehrere Tools: npm/pnpm verwalten Dependencies, während `tsc`, Webpack oder esbuild den Build übernehmen. Maven macht beides. Die zentrale Konfigurationsdatei heißt `pom.xml` (Project Object Model) — das Äquivalent zur `package.json`.

```xml
<!-- pom.xml — Javas package.json -->
<parent>
    <groupId>org.springframework.boot</groupId>
    <artifactId>spring-boot-starter-parent</artifactId>
    <version>3.4.3</version>
</parent>

<!-- Projekt-Koordinaten — eindeutige Identifikation -->
<groupId>com.example</groupId>      <!-- ≈ npm-Scope: @example -->
<artifactId>bookstore</artifactId>   <!-- ≈ Paketname: bookstore -->
<version>0.0.1-SNAPSHOT</version>    <!-- SNAPSHOT = Entwicklungsversion -->

<!-- Dependencies -->
<dependencies>
    <dependency>
        <groupId>org.springframework.boot</groupId>
        <artifactId>spring-boot-starter-web</artifactId>
        <!-- Keine Version nötig — kommt vom Parent -->
    </dependency>
</dependencies>
```

Statt `npm install` und `npm run build` hat Maven einen eigenen Lifecycle:

| Maven-Befehl          | Was passiert                     | npm-Äquivalent  |
| --------------------- | -------------------------------- | --------------- |
| `mvn compile`         | `.java` → `.class` kompilieren   | `tsc`           |
| `mvn test`            | Compile + Tests ausführen        | `npm test`      |
| `mvn package`         | Compile + Test + `.jar` erzeugen | `npm run build` |
| `mvn clean`           | `target/`-Ordner löschen         | `rm -rf dist/`  |
| `mvn spring-boot:run` | App im Dev-Modus starten         | `npm run dev`   |

Das Ergebnis eines Builds ist eine `.jar`-Datei im `target/`-Ordner — ein ZIP-Archiv mit dem kompilierten Bytecode. Bei Spring Boot erzeugt das Build-Plugin ein sogenanntes Fat-JAR, das alle Dependencies enthält. Du startest es mit:

```bash
java -jar target/bookstore-0.0.1-SNAPSHOT.jar
```

Vergleichbar mit Go: `go build` erzeugt ein Binary, `./binary` startet es. Der Unterschied: Go erzeugt nativen Maschinencode, Java erzeugt Bytecode, der von der JVM interpretiert und zur Laufzeit optimiert wird.

## JVM zur Laufzeit: Warum Java schnell ist

Die JVM ist kein einfacher Interpreter. Sie optimiert deinen Code aktiv — während er läuft:

1. **Bytecode laden** — der ClassLoader lädt `.class`-Dateien
2. **Interpretieren** — Bytecode wird zunächst interpretiert (schnellerer Start)
3. **JIT-Kompilierung** — häufig ausgeführter Code ("Hot Code") wird zur Laufzeit in nativen Maschinencode kompiliert
4. **Garbage Collection** — automatische Speicherbereinigung

Punkt 3 ist besonders spannend: Die JVM beobachtet, welche Methoden oft aufgerufen werden, und kompiliert genau diese in optimierten Maschinencode. Das ist vergleichbar mit V8's TurboFan in Node.js. Das bedeutet in der Praxis: Java-Apps starten langsamer als Node.js (die JVM muss hochfahren und Klassen laden), sind aber im Dauerbetrieb durch die JIT-Optimierung oft genauso schnell oder schneller.

## Stack, Heap und Garbage Collection

Java verwaltet Speicher in zwei Bereichen — genau wie JavaScript in V8:

|                     | Stack                                      | Heap                            |
| ------------------- | ------------------------------------------ | ------------------------------- |
| **Was**             | Lokale Variablen, Methodenaufrufe          | Objekte, Arrays                 |
| **Lebensdauer**     | Automatisch freigegeben nach Method-Return | Garbage Collector räumt auf     |
| **Geschwindigkeit** | Sehr schnell (LIFO-Struktur)               | Langsamer (komplexe Verwaltung) |
| **Analogie**        | Call Stack in den JS DevTools              | Heap in Chrome Memory Profiler  |

```java
public double calculateTotal(List<OrderItem> items) {
    double sum = 0.0;                      // primitiver Wert → Stack
    List<String> names = new ArrayList<>(); // Referenz → Stack, Objekt → Heap
    return sum;                             // Referenzen vom Stack entfernt,
}                                          // GC räumt Heap-Objekte auf
```

Die **Garbage Collection** räumt nicht mehr referenzierte Objekte vom Heap. Das passiert automatisch — du musst nie `free()` aufrufen. In Node.js (V8) funktioniert das identisch. Der Unterschied zu C/C++: Dort muss der Speicher manuell verwaltet werden.

---

## Die Sprache Java

### Sichtbarkeit: `public`, `private`, `protected`

In TypeScript kennst du `public`, `private` und `protected` aus Klassen. Java funktioniert ähnlich, hat aber eine zusätzliche Stufe:

| Modifier          | Sichtbar für                                    | Beispiel                                                     |
| ----------------- | ----------------------------------------------- | ------------------------------------------------------------ |
| `public`          | Alle                                            | `public Book findById(Long id)` — öffentliche API            |
| `private`         | Nur die eigene Klasse                           | `private boolean isValidIsbn(String isbn)` — internes Detail |
| `protected`       | Eigene Klasse + Unterklassen + gleiches Package | `protected Book() {}` — eingeschränkter Zugriff              |
| _(kein Modifier)_ | Nur im selben Package                           | `record BookRequest(...)` — package-private                  |

Die Faustregel ist identisch zu TypeScript: Alles so restriktiv wie möglich. `private` als Default, `public` nur für die bewusste API.

### `static` vs. non-static

In TypeScript unterscheidest du zwischen einer Funktion auf Modul-Ebene und einer Methode auf einer Instanz. In Java ist diese Unterscheidung explizit:

```java
// BookService.java
private static final Map<String, String> CATEGORIES = Map.of(
    "FIC", "Fiction", "SCI", "Science"
);
// static → existiert genau 1x, egal wie viele Instanzen
// ≈ const CATEGORIES = { FIC: "Fiction", SCI: "Science" } auf Modul-Ebene

public Book findById(Long id) { ... }
// non-static → wird auf einer Instanz aufgerufen: service.findById(...)
// ≈ Methode auf einem Objekt
```

|                        | `static`                           | non-static               |
| ---------------------- | ---------------------------------- | ------------------------ |
| **Gehört zu**          | Der Klasse selbst                  | Einer Instanz            |
| **Zugriff**            | `ClassName.method()`               | `instance.method()`      |
| **Zugriff auf `this`** | Nein                               | Ja                       |
| **Typischer Einsatz**  | Konstanten, Utility-Methoden       | Business-Logik, Services |
| **TS-Analogie**        | `export const` / `export function` | Methode auf einer Klasse |

### `final` — Javas `const`

Java hat kein `const`-Keyword. Stattdessen gibt es `final` — und es kann an verschiedenen Stellen eingesetzt werden:

| Java             | TypeScript              | Bedeutung                                 |
| ---------------- | ----------------------- | ----------------------------------------- |
| `final` Variable | `const` / `readonly`    | Referenz kann nicht neu zugewiesen werden |
| `final` Klasse   | —                       | Klasse kann nicht vererbt werden          |
| `final` Methode  | —                       | Methode kann nicht überschrieben werden   |
| `static final`   | `const` auf Modul-Ebene | Klassen-Konstante                         |

```java
private final BookService bookService;
// ≈ readonly bookService: BookService — kann nach dem Konstruktor nicht neu zugewiesen werden

private static final Map<String, String> CATEGORIES = Map.of("FIC", "Fiction");
// ≈ const CATEGORIES = Object.freeze({ FIC: "Fiction" })
```

**Wichtig**: `final` schützt nur die **Referenz**, nicht den **Inhalt**. Eine `final List<String>` kann nicht auf eine andere Liste zeigen, aber die Elemente _in_ der Liste können geändert werden. Für echte Immutabilität nutzt man `List.of()` oder `Map.of()` — unmodifizierbare Collections.

### Reflection — Javas Superkraft im Hintergrund

Reflection erlaubt es, zur Laufzeit Klassen zu inspizieren und zu manipulieren: Felder lesen, Methoden aufrufen und Objekte erstellen — ohne den konkreten Typ zur Compile-Zeit zu kennen.

```java
// Was Hibernate intern beim Laden eines Buches aus der DB tut:
Class<?> clazz = Class.forName("com.example.model.Book");
Object entity = clazz.getDeclaredConstructor().newInstance(); // new Book()
Field field = clazz.getDeclaredField("title");
field.setAccessible(true);  // umgeht "private"
field.set(entity, "Clean Code");  // setzt den Wert ohne Setter
```

Als TypeScript-Entwickler wirst du diesen Code nie selbst schreiben. Aber es ist wichtig zu wissen, dass Reflection existiert, denn zwei zentrale Technologien im Java-Ökosystem nutzen es intensiv:

1. **Hibernate** (das ORM) lädt Daten aus der Datenbank und setzt die Felder per Reflection. Deshalb braucht eine Entity-Klasse einen leeren Konstruktor, und die Felder dürfen nicht `final` sein.
2. **Spring** (Dependency Injection) findet Klassen mit Annotations wie `@Service` oder `@Controller` per Classpath-Scanning und injiziert automatisch die richtigen Abhängigkeiten.

In TypeScript gibt es kein echtes Reflection, aber TypeScript-Decorators und `Reflect.metadata` kommen dem am nächsten.

---

## Datenmodellierung

### Getter und Setter — warum Java so "verbose" wirkt

Wer zum ersten Mal Java-Code sieht, wundert sich oft über die vielen Getter und Setter. In TypeScript schreibst du einfach `book.title` — in Java ist das Feld `private` und der Zugriff läuft über Methoden:

```java
private String title;
public String getTitle() { return title; }
public void setTitle(String title) { this.title = title; }
```

Warum? Drei Gründe:

1. **Kapselung** — du kannst die interne Repräsentation ändern, ohne die API zu brechen
2. **Frameworks erwarten es** — Jackson (JSON-Serialisierung) und Hibernate nutzen Getter/Setter
3. **Java-Convention** — die gesamte Toolchain baut auf diesem Pattern auf

Das wirkt umständlich, aber dafür gibt es zwei Abkürzungen:

### Lombok — der Boilerplate-Killer

Lombok ist eine Compile-Time-Library, die per Annotation Getter, Setter und Konstruktoren generiert:

```java
// MIT Lombok:
@Data  // generiert: Getter, Setter, equals(), hashCode(), toString()
public class Book {
    private String title;
    private String author;
}

// OHNE Lombok — das Gleiche, aber manuell:
public class Book {
    private String title;
    private String author;

    public String getTitle() { return title; }
    public void setTitle(String title) { this.title = title; }
    public String getAuthor() { return author; }
    public void setAuthor(String author) { this.author = author; }
    // plus equals(), hashCode(), toString() ...
}
```

Lombok reduziert den Boilerplate-Code erheblich. Der Nachteil: Der generierte Code ist unsichtbar. In Lernprojekten ist expliziter Code oft verständlicher, in Produktionsprojekten ist Lombok allerdings weit verbreitet.

### Records — Javas Antwort auf TypeScript `type`

Seit Java 16 gibt es **Records**: immutable Datenklassen ohne jeglichen Boilerplate. Der Compiler generiert automatisch Konstruktor, Accessor-Methoden (z.B. `title()` statt `getTitle()`), `equals()`, `hashCode()` und `toString()`.

```java
// Ein Record in Java:
record BookResponse(Long id, String title, String author, double price) {}

// Das Äquivalent in TypeScript:
// type BookResponse = { id: number; title: string; author: string; price: number }
```

Zugriff auf Felder funktioniert ohne `get`-Prefix: `response.title()`, `response.author()`.

**Wann Record, wann Klasse?**

|                             | Record                              | Klasse                            |
| --------------------------- | ----------------------------------- | --------------------------------- |
| **Mutierbar?**              | Nein — immutable                    | Ja                                |
| **Boilerplate**             | Null                                | Getter/Setter/Konstruktor manuell |
| **Typisches Einsatzgebiet** | DTOs: `BookRequest`, `BookResponse` | JPA Entity: `Book`                |
| **Vererbung**               | Keine                               | Möglich                           |

**Warum kann eine JPA Entity kein Record sein?** Weil die JPA-Spezifikation einen leeren Konstruktor und nicht-`final`e Felder verlangt. Records sind immutable und haben keinen leeren Konstruktor — das passt nicht zusammen.

### DTOs — Daten transportieren, nicht mehr

DTOs (Data Transfer Objects) sind Objekte, die nur Daten transportieren — zwischen Schichten (Controller ↔ Service) oder zwischen Client und Server (JSON). Sie enthalten keine Business-Logik.

```java
// Drei DTOs für drei verschiedene Zwecke:
record BookRequest(String title, String author, double price) {}      // eingehend
record BookResponse(Long id, String title, String author, double price) {}  // ausgehend
record BookListEntry(Long id, String title, String author) {}         // Listenansicht
```

**Warum nicht einfach die Entity als JSON senden?** Weil du kontrollieren willst, welche Felder der Client sieht. Die Entity hat z.B. interne IDs oder Timestamps — das muss nicht in jeder Response mitgeschickt werden. In TypeScript/Node.js ist es das gleiche Prinzip: Du schickst selten das Prisma-Model 1:1 als JSON, sondern mappst auf ein Response-Objekt.

### Jackson — JSON automatisch

Jackson ist die Standard-JSON-Library in der Java-Welt. Sie konvertiert Java-Objekte zu JSON und umgekehrt — automatisch.

```
Client sendet JSON:     { "title": "Clean Code", "author": "Robert C. Martin", "price": 29.99 }
                              ↓ Jackson deserialisiert
Java-Objekt:            BookRequest("Clean Code", "Robert C. Martin", 29.99)

Server antwortet:       BookResponse(1, "Clean Code", "Robert C. Martin", 29.99)
                              ↓ Jackson serialisiert
JSON an Client:         { "id": 1, "title": "Clean Code", "author": "Robert C. Martin", "price": 29.99 }
```

Jackson matcht Felder per Name: Das JSON-Feld `"title"` wird auf den Record-Parameter `title` gemappt. Bei Klassen nutzt Jackson die Getter-Methoden: `getTitle()` wird zu `"title"`, `isAvailable()` wird zu `"available"`.

In TypeScript machst du das mit `JSON.parse()` und `JSON.stringify()` — aber ohne Typsicherheit. Jackson gibt dir beides: Serialisierung _und_ automatisches Mapping auf typisierte Objekte — vergleichbar mit `zod.parse()`, nur mit automatischem Serialisieren.

---

## Fazit

Java und TypeScript/Node.js sind sich ähnlicher, als man auf den ersten Blick denkt. Die Grundkonzepte — Typsysteme, Module, Build-Prozesse, Garbage Collection — existieren in beiden Welten. Java ist expliziter (Sichtbarkeitsmodifier, Getter/Setter), bietet dafür aber ein ausgereifteres Ökosystem für große Backend-Systeme.

Die wichtigsten Parallelen auf einen Blick:

| Java                 | TypeScript/Node.js                   |
| -------------------- | ------------------------------------ |
| JDK → JRE → JVM      | Node.js + npm → V8                   |
| Maven (`pom.xml`)    | npm/pnpm (`package.json`)            |
| `.jar`-Datei         | `dist/`-Ordner                       |
| `private` / `public` | `private` / `public` in Klassen      |
| `final`              | `const` / `readonly`                 |
| Records              | `type` / `interface`                 |
| Jackson              | `JSON.parse()` + zod                 |
| Lombok (optional)    | — (nicht nötig, weniger Boilerplate) |

Im nächsten Artikel schauen wir uns an, wie mit Spring Boot ein vollständiges Backend entsteht — von der HTTP-Schicht über Dependency Injection bis zur Datenbank: [Spring Boot für TypeScript-Entwickler erklärt](/articles/spring-boot-fuer-typescript-entwickler-erklaert).
