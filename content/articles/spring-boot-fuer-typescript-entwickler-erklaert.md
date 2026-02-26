---
title: Spring Boot für TypeScript-Entwickler erklärt
description: Du kennst Express.js und willst Spring Boot verstehen? Dieser Artikel erklärt Dependency Injection, Controller, Services, JPA, Testing und mehr — mit Vergleichen zu Node.js.
author: Nico Gräf
author_url: https://nicograef.com
date: 2025-02-26
tags:
  - Java
  - Spring Boot
  - TypeScript
  - Software Architecture
  - Backend
---

# Spring Boot für TypeScript-Entwickler erklärt

> Dieser Artikel baut auf [Java für TypeScript-Entwickler erklärt](/articles/java-fuer-typescript-entwickler-erklaert) auf. Dort werden die Java-Plattform, die Sprache und die Datenmodellierung erklärt.

Du kennst Express.js und hast vielleicht schon REST-APIs mit Node.js gebaut? Dann hast du die richtige Grundlage für Spring Boot — Javas meistgenutztes Backend-Framework. Wo Express minimal ist und dir die Wahl lässt, bringt Spring Boot Konventionen, Struktur und Automatisierung mit.

## Vor Spring Boot: Eine kurze Zeitreise

Um Spring Boot zu verstehen, hilft ein kurzer Blick in die Geschichte — denn Spring Boot hat viele Probleme gelöst, die vorher schmerzhaft waren.

### Phase 1: Servlets (seit 1997)

Javas erste Web-API. Du schreibst Klassen, die HTTP-Requests verarbeiten, und deployst sie auf einen externen Application Server (z.B. Tomcat). Viel XML-Konfiguration, viel Boilerplate:

```java
// Ein Servlet — Javas erstes Web-Framework (ohne Spring):
public class BookServlet extends HttpServlet {
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) {
        String title = req.getParameter("title");
        // Manuell: JSON parsen, DB-Connection holen, SQL schreiben, Response bauen...
        resp.getWriter().write("{\"id\": 1, \"title\": \"" + title + "\"}");
    }
}
```

Stell dir vor, du müsstest in Express.js jede Response manuell als String zusammenbauen und jede Route in einer XML-Datei registrieren. So fühlte sich Java-Webentwicklung im Jahr 2000 an.

### Phase 2: Spring Framework (seit 2004)

Brachte Dependency Injection und reduzierte den Boilerplate deutlich — allerdings mit viel XML-Konfiguration und einem externen Tomcat-Server.

### Phase 3: Spring Boot (seit 2014)

Convention over Configuration. Embedded Tomcat (kein externer Server mehr nötig), automatische Konfiguration, Starter-Dependencies, kein XML. Das ist der heutige Standard — und damit arbeiten wir im Rest dieses Artikels.

## Was ist Tomcat?

Tomcat ist ein Servlet-Container — ein HTTP-Server, der Java-Code ausführen kann. Vergleichbar mit Node.js' eingebautem `http`-Modul (`http.createServer()`), aber auf einer höheren Abstraktionsebene.

In Spring Boot ist Tomcat **eingebettet** (embedded): Er steckt in deiner `.jar`-Datei und startet automatisch. Du musst ihn nicht separat installieren. Die gesamte Konfiguration besteht aus einer Zeile:

```properties
# application.properties
server.port=8080
```

Wenn `SpringApplication.run()` in der `main`-Methode aufgerufen wird, startet Spring den eingebetteten Tomcat, registriert alle Controller als Request-Handler und lauscht auf dem konfigurierten Port.

## Was ist Spring Boot?

Spring Boot ist ein Framework auf dem Spring Framework, das die Konfiguration automatisiert. Drei Kernprinzipien:

1. **Auto-Configuration** — füge eine Dependency hinzu und Spring konfiguriert alles automatisch. Tomcat, JSON-Serialisierung, Routing — alles inklusive.
2. **Embedded Server** — kein externer Tomcat nötig. Die App ist eine einzelne `.jar`-Datei. Vergleichbar mit `node server.js` statt Deployment auf Apache.
3. **Starter Dependencies** — ein "Starter" bringt alles mit, was du für ein Feature brauchst.

```xml
<!-- pom.xml — ein Starter, alles drin: -->
<dependency>
    <groupId>org.springframework.boot</groupId>
    <artifactId>spring-boot-starter-web</artifactId>
    <!-- Bringt mit: Tomcat + Spring MVC + Jackson -->
    <!-- ≈ npm install express (+ body-parser + JSON handling) -->
</dependency>
```

## Dependency Injection: Springs zentrale Idee

In Express.js erstellst du deine Services manuell und gibst sie weiter:

```typescript
// Node.js/Express — du verdrahtest alles selbst:
const bookRepository = new BookRepository(db);
const bookService = new BookService(bookRepository);
const bookController = new BookController(bookService);
app.use("/books", bookController.router);
```

In Spring Boot übernimmt das Framework diese Verdrahtung. Du sagst Spring nur, _was_ es gibt — Spring kümmert sich um das _Wie_.

### Inversion of Control und Dependency Injection

**Inversion of Control (IoC)** bedeutet: Du erstellst deine Abhängigkeiten nicht selbst, sondern jemand anderes gibt sie dir. Das Kontroll-Prinzip ist umgekehrt.

**Dependency Injection (DI)** ist die konkrete Umsetzung: Spring "injiziert" Abhängigkeiten in deinen Konstruktor.

```java
// OHNE DI — du erstellst die Abhängigkeit selbst:
public class BookController {
    private final BookService bookService;
    public BookController() {
        this.bookService = new BookService(); // eng gekoppelt
    }
}

// MIT DI — Spring gibt dir die Abhängigkeit:
public class BookController {
    private final BookService bookService;
    public BookController(BookService bookService) {
        this.bookService = bookService; // Spring injiziert das
    }
}
```

Der Vorteil: In Tests kannst du ein Mock übergeben, ohne den Controller-Code zu ändern. Und wenn `BookService` selbst Abhängigkeiten hat (z.B. ein `BookRepository`), löst Spring die gesamte Kette auf.

### Beans — Springs verwaltete Objekte

Ein **Bean** ist ein Objekt, das Spring erstellt und verwaltet. Stell dir einen zentralen Container vor (den "Application Context"), der alle Objekte kennt und auf Abruf bereitstellt.

Vier Annotations registrieren eine Klasse als Bean — sie tun alle technisch das Gleiche, unterscheiden sich aber in der Bedeutung:

| Annotation        | Bedeutung            | Beispiel                      |
| ----------------- | -------------------- | ----------------------------- |
| `@Component`      | Generische Bean      | Utility-Klassen               |
| `@Service`        | Business-Logik       | `BookService`, `OrderService` |
| `@Repository`     | Datenzugriff         | `BookRepository`              |
| `@RestController` | HTTP-Request-Handler | `BookController`              |

Technisch könnte man überall `@Component` schreiben. Aber die spezifischen Annotations machen den Code lesbar und ermöglichen Framework-spezifisches Verhalten — z.B. übersetzt `@Repository` SQL-Exceptions automatisch in Spring-Exceptions.

### Constructor Injection — die empfohlene Variante

Es gibt mehrere Wege, eine Dependency zu injizieren (Constructor, Setter, Field). Aber nur einer ist empfohlen:

```java
// ✅ Constructor Injection (Best Practice)
public class BookController {
    private final BookService bookService;
    public BookController(BookService bookService) {
        this.bookService = bookService;
    }
}

// ❌ Field Injection (nicht empfohlen)
public class BookController {
    @Autowired
    private BookService bookService;
}
```

Warum Constructor Injection? Die Felder können `final` sein (Immutabilität), die Abhängigkeiten sind explizit sichtbar, und man kann den Controller in Tests ohne Spring instanziieren: `new BookController(mockService)`.

Bei Constructor Injection braucht man keine `@Autowired`-Annotation — Spring erkennt automatisch, dass der einzige Konstruktor Injection benötigt.

## Die HTTP-Schicht: Controller und Routing

### Wie ein Request durch Spring fließt

```
HTTP Request → Embedded Tomcat → DispatcherServlet → @RestController-Methode
                                                            ↓
HTTP Response ← Jackson (→ JSON) ← ResponseEntity ← Return-Wert
```

Der **DispatcherServlet** ist Springs zentraler Request-Router — vergleichbar mit Express' internem Routing-Mechanismus. Er schaut auf `@GetMapping`, `@PostMapping` etc. und findet die richtige Methode.

### Ein Controller in Spring Boot vs. Express.js

```java
// Spring Boot:
@RestController
@RequestMapping("/api/books")
public class BookController {

    private final BookService bookService;

    public BookController(BookService bookService) {
        this.bookService = bookService;
    }

    @PostMapping
    public ResponseEntity<BookResponse> createBook(
            @Valid @RequestBody BookRequest request) {
        return ResponseEntity.ok(bookService.create(request));
    }

    @GetMapping("/{id}")
    public ResponseEntity<BookResponse> getBook(@PathVariable Long id) {
        return ResponseEntity.ok(bookService.findById(id));
    }
}
```

```typescript
// Das Äquivalent in Express.js:
const router = express.Router();

router.post("/", (req, res) => {
  const result = bookService.create(req.body);
  res.json(result);
});

router.get("/:id", (req, res) => {
  const result = bookService.findById(req.params.id);
  res.json(result);
});
```

Der Unterschied: In Spring Boot wird jeder Rückgabewert automatisch von Jackson zu JSON serialisiert. `@Valid` prüft die Constraints auf dem DTO (z.B. `@NotBlank`) — vergleichbar mit einer Zod-Validierung in Express.

### Globales Error-Handling

`@RestControllerAdvice` ist Springs globaler Error-Handler — das Äquivalent zu Express' Error-Handling-Middleware `app.use((err, req, res, next) => { ... })`:

```java
@RestControllerAdvice
public class GlobalExceptionHandler {

    @ExceptionHandler(MethodArgumentNotValidException.class)
    @ResponseStatus(HttpStatus.BAD_REQUEST)
    public Map<String, String> handleValidationErrors(
            MethodArgumentNotValidException ex) {
        return Map.of("error", "Validation failed");
    }

    @ExceptionHandler(Exception.class)
    @ResponseStatus(HttpStatus.INTERNAL_SERVER_ERROR)
    public Map<String, String> handleGenericError(Exception ex) {
        return Map.of("error", "Internal server error");
    }
}
```

Der Ablauf: Eine Controller-Methode wirft eine Exception → Spring fängt sie ab → sucht die passende `@ExceptionHandler`-Methode → serialisiert den Return-Wert zu JSON. Ohne `@RestControllerAdvice` liefert Spring Boot eine generische Fehlerantwort (JSON für REST-Clients, HTML-Whitelabel-Page für Browser) — mit `@RestControllerAdvice` kontrollierst du das Format selbst.

### RestClient: Externe HTTP-Aufrufe

Für Aufrufe an externe APIs bietet Spring den `RestClient` — vergleichbar mit `fetch()` oder `axios`:

```java
private final RestClient restClient = RestClient.builder()
        .baseUrl("https://openlibrary.org/api/books/")
        .build();

public BookDetails fetchDetails(String isbn) {
    try {
        var response = restClient.get()
                .uri("{isbn}.json", isbn)
                .retrieve()
                .body(OpenLibraryResponse.class); // Jackson deserialisiert automatisch
        return mapToBookDetails(response);
    } catch (Exception e) {
        return null; // Graceful Degradation
    }
}
```

```typescript
// TypeScript-Äquivalent:
const res = await fetch(`${BASE_URL}${isbn}.json`);
const data: OpenLibraryResponse = await res.json();
```

Der Unterschied: `RestClient` deserialisiert direkt in ein typisiertes Java-Objekt (`.body(OpenLibraryResponse.class)`).

---

## Die Datenbank-Schicht

### Von JDBC zu JPA — drei Abstraktionsebenen

In Node.js kennst du vielleicht den `pg`-Client für PostgreSQL (rohes SQL), Knex.js (Query Builder) und Prisma (ORM). In Java gibt es eine ähnliche Hierarchie:

```
JPA (Spezifikation) → Hibernate (Implementierung) → JDBC (Low-Level) → PostgreSQL
```

**JDBC** ist Javas Low-Level-API für Datenbankzugriffe — vergleichbar mit dem `pg`-Package in Node.js:

```java
// Rohes JDBC — so will niemand arbeiten:
Connection conn = DriverManager.getConnection("jdbc:postgresql://localhost:5432/bookstore");
PreparedStatement stmt = conn.prepareStatement(
    "INSERT INTO books (title, author, price) VALUES (?, ?, ?)");
stmt.setString(1, "Clean Code");
stmt.setString(2, "Robert C. Martin");
stmt.setDouble(3, 29.99);
stmt.executeUpdate();
conn.close();
```

**JPA** (Jakarta Persistence API) ist eine Spezifikation — ein Standard, der beschreibt, _wie_ ein ORM in Java aussehen soll. **Hibernate** ist die populärste Implementierung dieses Standards. Das Verhältnis ist vergleichbar mit einem TypeScript-Interface und einer Klasse, die es implementiert: JPA definiert die API, Hibernate liefert den Code dahinter.

### JPA Entities

Eine JPA Entity ist eine Java-Klasse, die auf eine Datenbanktabelle gemappt wird. Jede Instanz entspricht einer Zeile:

```java
@Entity                     // "Diese Klasse repräsentiert eine DB-Tabelle"
@Table(name = "books")      // Tabellenname explizit setzen
public class Book {

    @Id                     // Primärschlüssel
    @GeneratedValue(strategy = GenerationType.IDENTITY) // auto-increment
    private Long id;

    @Column(nullable = false, length = 255)
    private String title;

    @Column(nullable = false)
    private String author;

    private double price;

    // Getter, Setter, leerer Konstruktor (für Hibernate)
}
```

```typescript
// Prisma-Äquivalent:
// model Book {
//   id     Int     @id @default(autoincrement())
//   title  String  @db.VarChar(255)
//   author String
//   price  Float
// }
```

Die Spalten-Namen werden automatisch von camelCase in snake_case konvertiert: `publishedDate` → `published_date`. Das ist eine Spring-Boot-Convention.

### Spring Data JPA — null Zeilen eigener Code

Spring Data JPA generiert die gesamte CRUD-Implementierung aus einem Interface:

```java
// BookRepository.java — die GESAMTE Datei:
public interface BookRepository extends JpaRepository<Book, Long> {
}
```

Das gibt dir `save()`, `findAll()`, `findById()`, `deleteById()`, `count()` und vieles mehr — ohne eine einzige Implementierungszeile.

Unter der Haube passiert beim App-Start Folgendes: Spring scannt den Classpath, findet Interfaces, die `JpaRepository` erweitern, und erzeugt zur Laufzeit per Proxy eine vollständige Implementierung:

```
bookRepository.save(entity)
       ↓ Spring Data JPA (generierter Proxy)
       ↓ Hibernate (generiert SQL)
       ↓ JDBC (sendet SQL an DB)
       ↓ PostgreSQL (führt INSERT aus)
```

### Schema-Management mit Flyway

In Spring Boot wird das Datenbankschema **nicht** von Hibernate verwaltet (obwohl es könnte). Stattdessen nutzt man meistens **Flyway** — ein Migrations-Tool, vergleichbar mit Prisma Migrate.

```
src/main/resources/db/migration/
    V1__initial_schema.sql    ← wird einmalig beim Start ausgeführt
    V2__add_index.sql         ← nächste Version
```

Beim App-Start passiert Folgendes:

1. Flyway prüft die `flyway_schema_history`-Tabelle: Welche Migrationen wurden schon ausgeführt?
2. Neue Migrationen werden ausgeführt (z.B. V2, wenn V1 schon durch ist)
3. Hibernate validiert (`ddl-auto=validate`): Passt die Entity-Klasse zum Schema?

Flyway nutzt Plain-SQL-Dateien — einfach und datenbankspezifisch. Die Alternative Liquibase nutzt XML/YAML und ist datenbank-agnostisch, aber komplexer.

---

## Konfiguration: `application.properties`

`application.properties` ist die zentrale Konfigurationsdatei von Spring Boot — vergleichbar mit `.env` in Node.js:

```properties
# Datenbank — ${VAR:default} liest Umgebungsvariablen mit Fallback
spring.datasource.url=${SPRING_DATASOURCE_URL:jdbc:postgresql://localhost:5432/bookstore}
spring.datasource.username=${SPRING_DATASOURCE_USERNAME:admin}

# Hibernate validiert Schema, ändert es aber nicht
spring.jpa.hibernate.ddl-auto=validate

# Embedded Tomcat Port
server.port=8080
```

**Profile** sind das Äquivalent zu `NODE_ENV`. Spring lädt automatisch `application-{profil}.properties` als Override:

- `application.properties` — Basis (immer geladen)
- `application-dev.properties` — Overrides für Entwicklung

Aktiviert mit:

```bash
mvn spring-boot:run -Dspring-boot.run.profiles=dev
```

---

## Testing

Spring-Boot-Projekte haben typischerweise zwei Arten von Tests — vergleichbar mit Unit-Tests in Vitest und Integrationstests mit Supertest.

### Unit-Tests: Service isoliert testen

Pure Unit-Tests ohne Spring-Kontext. Die Service-Klasse wird direkt instanziiert:

```java
class BookServiceTest {

    private BookService service;

    @BeforeEach // ≈ beforeEach(() => { ... })
    void setUp() {
        service = new BookService(); // kein Spring nötig
    }

    @Test // ≈ it('calculates discount correctly', () => { ... })
    void calculatesDiscountForBulkOrder() {
        var result = service.calculateDiscount(10, 29.99);
        assertEquals(269.91, result, 0.01);
        // ≈ expect(result).toBeCloseTo(269.91)
    }

    @Test
    void rejectsInvalidIsbn() {
        assertFalse(service.isValidIsbn("000"));
        // ≈ expect(service.isValidIsbn("000")).toBe(false)
    }
}
```

Die wichtigsten JUnit-Assertions und ihre Vitest-Äquivalente:

| JUnit 5                          | Vitest/Jest                     |
| -------------------------------- | ------------------------------- |
| `assertEquals(expected, actual)` | `expect(actual).toBe(expected)` |
| `assertTrue(x)`                  | `expect(x).toBe(true)`          |
| `assertNull(x)`                  | `expect(x).toBeNull()`          |
| `@BeforeEach`                    | `beforeEach()`                  |
| `@Test`                          | `it()` / `test()`               |

### Integrationstests: Controller mit MockMvc

Integrationstests prüfen den Controller mit echtem HTTP-Routing, aber gemockten Services. Spring startet dafür einen Mini-Kontext — nur die Web-Schicht, keine Datenbank:

```java
@WebMvcTest(BookController.class) // nur Web-Schicht laden
class BookControllerTest {

    @Autowired
    private MockMvc mockMvc; // Fake-HTTP-Client, ≈ supertest(app)

    @MockitoBean // erstellt Mock + registriert als Spring Bean
    private BookService bookService;

    @Test
    void createBookReturnsOk() throws Exception {
        // Mock-Verhalten definieren
        when(bookService.create(any()))
            .thenReturn(new BookResponse(1L, "Clean Code", "Robert C. Martin", 29.99));

        mockMvc.perform(post("/api/books")
                .contentType(MediaType.APPLICATION_JSON)
                .content("{\"title\": \"Clean Code\", \"author\": \"Robert C. Martin\", \"price\": 29.99}"))
            .andExpect(status().isOk())
            .andExpect(jsonPath("$.title").value("Clean Code"))
            .andExpect(jsonPath("$.author").value("Robert C. Martin"));
    }

    @Test
    void createBookWithEmptyTitleReturnsBadRequest() throws Exception {
        mockMvc.perform(post("/api/books")
                .contentType(MediaType.APPLICATION_JSON)
                .content("{\"title\": \"\", \"author\": \"Test\"}"))
            .andExpect(status().isBadRequest());
    }
}
```

| Spring-Annotation           | Was es tut                                 | Analogie                             |
| --------------------------- | ------------------------------------------ | ------------------------------------ |
| `@WebMvcTest(X.class)`      | Mini-Spring-Context nur für den Controller | Supertest mit isoliertem Router      |
| `@MockitoBean`              | Registriert einen Mock als Spring Bean     | `vi.mock()` / `jest.mock()`          |
| `MockMvc`                   | Fake-HTTP-Client (kein echtes Netzwerk)    | `supertest(app)`                     |
| `when(...).thenReturn(...)` | Definiert Mock-Verhalten                   | `vi.mocked(fn).mockReturnValue(...)` |

Tests ausführen:

```bash
mvn test          # alle Tests (Unit-Tests via Surefire)
mvn verify        # Unit- + Integrationstests (via Failsafe)
```

---

## Spring-Boot-Conventions auf einen Blick

| Convention               | Beispiel                                           | Warum                                   |
| ------------------------ | -------------------------------------------------- | --------------------------------------- |
| Starter Dependencies     | `spring-boot-starter-web`                          | Ein Starter = alle Deps für ein Feature |
| Package-Struktur         | `controller/`, `service/`, `model/`, `repository/` | Übliche Schichtentrennung               |
| `application.properties` | `server.port=8080`                                 | Zentrale Konfiguration                  |
| Profile                  | `application-dev.properties`                       | Environment-spezifische Config          |
| Constructor Injection    | Kein `@Autowired` auf Feldern                      | Testbarkeit + Immutabilität             |
| Schema per Flyway        | `V1__initial_schema.sql`                           | Hibernate validiert nur, ändert nicht   |
| `@RestControllerAdvice`  | `GlobalExceptionHandler`                           | Zentrales Error-Handling                |
| Records für DTOs         | `BookRequest`, `BookResponse`                      | Immutable, kein Lombok nötig            |

---

## Das große Bild

Zum Abschluss ein Blick auf alle Schichten — von oben (HTTP) nach unten (Datenbank):

```
┌───────────────────────────────────────────────────────────────┐
│                     Spring Boot App                           │
├───────────────────────────────────────────────────────────────┤
│  @RestController (BookController)                             │
│    ├── empfängt HTTP Requests (≈ Express Router)              │
│    ├── Jackson deserialisiert JSON → Records (DTOs)           │
│    ├── @Valid prüft Constraints (≈ zod)                       │
│    └── @RestControllerAdvice fängt Fehler global              │
├───────────────────────────────────────────────────────────────┤
│  @Service (BookService, OrderService)                         │
│    ├── Business-Logik (Preisberechnung, Validierung)          │
│    └── RestClient → externe APIs (≈ fetch/axios)              │
├───────────────────────────────────────────────────────────────┤
│  JpaRepository (BookRepository — 0 Zeilen eigener Code)      │
│    └── Spring → Hibernate → JDBC → SQL                       │
├───────────────────────────────────────────────────────────────┤
│  @Entity (Book) ↔ Flyway (V1__initial_schema.sql)            │
│    └── Flyway verwaltet Schema, Hibernate validiert nur       │
├───────────────────────────────────────────────────────────────┤
│  Konfiguration: application.properties + Profile (dev/prod)  │
├───────────────────────────────────────────────────────────────┤
│  Tests: JUnit 5 (Unit) + MockMvc (Integration)               │
├───────────────────────────────────────────────────────────────┤
│  Maven (pom.xml) → Build → .jar                              │
├───────────────────────────────────────────────────────────────┤
│  JVM (Java 21) + Embedded Tomcat + PostgreSQL                 │
└───────────────────────────────────────────────────────────────┘
```

Spring Boot nimmt dir viel ab: Tomcat starten, JSON serialisieren, Abhängigkeiten verdrahten, SQL generieren. Dein Job ist es, Controller, Services und Entities zu schreiben — den Rest erledigt das Framework. Die Konzepte aus Express.js findest du alle wieder: Routing, Middleware, Request/Response. Spring Boot bringt darüber hinaus Dependency Injection, ein typisiertes ORM und ein ausgereiftes Test-Ökosystem mit — Dinge, die du dir in Node.js aus verschiedenen Libraries zusammenbauen müsstest.
