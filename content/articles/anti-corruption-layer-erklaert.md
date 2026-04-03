# Anti-Corruption Layer — Saubere Grenzen zwischen deinem Code und Legacy-Systemen

Jedes größere Unternehmen hat sie: Systeme, die niemand mehr anfassen möchte, aber alle brauchen. Ein zwanzig Jahre altes CRM, ein ERP mit kryptischen Feldnamen, eine SOAP-Schnittstelle, die seit zwei Jahrzehnten unverändert läuft.

Wenn ein neues System Daten aus einem solchen Altsystem braucht, lauert eine stille Gefahr. Das externe Modell — gewachsen, unklar, voller technischer Schulden — sickert in deinen neuen Code ein. Der **Anti-Corruption Layer** ist das Muster, das genau das verhindert.

## Das Problem: Wenn Altlasten in neuen Code einsickern

Stell dir vor, du baust einen neuen **Bestellservice**. Für jede Bestellung brauchst du Kundendaten — und die stecken im Legacy-CRM, das seit fünfzehn Jahren gewachsen ist.

Das CRM liefert Kundendaten in etwa so:

```json
{
  "KNDNR": "0000012345",
  "ANREDE_CD": "1",
  "NNAME": "Müller",
  "VNAME": "Max",
  "STR_HNR": "Hauptstraße 12a",
  "PLZ": "10115",
  "ORT": "Berlin",
  "LAND_ISO": "DE",
  "AKTIV_FLG": "J"
}
```

Der einfachste Weg: Du baust das Kundenmodell deines Bestellservices direkt auf dieser Struktur auf.

```typescript
interface Customer {
  KNDNR: string;
  ANREDE_CD: string;
  NNAME: string;
  VNAME: string;
  STR_HNR: string;
  PLZ: string;
  ORT: string;
  LAND_ISO: string;
  AKTIV_FLG: string;
}
```

Damit hast du das Legacy-Modell in deinen neuen Code kopiert — inklusive aller Altlasten:

- Kryptische Feldnamen, die kein Entwickler ohne Dokumentation versteht
- Alles als `string`, obwohl `isActive` ein Boolean sein sollte
- Flache Struktur statt sprechender Werteobjekte
- `AKTIV_FLG === "J"` — dein Bestellservice muss die internen Konventionen des CRM kennen

Benennt das CRM-Team `AKTIV_FLG` irgendwann um, bricht dein Bestellservice. Du bist direkt von internen Entscheidungen eines anderen Systems abhängig.

Das nennt man **Modell-Kontamination** — das externe Modell hat dein internes Modell überschrieben.

![Bestellservice ohne ACL: Das CRM-Modell fließt direkt in den Bestellservice](/assets/img/articles/acl-ohne-acl.png)
_Ohne ACL: Das Legacy-Modell sickert direkt in deinen Code ein_

## Das Pattern: Was ist ein Anti-Corruption Layer?

Der **Anti-Corruption Layer** (ACL) ist eine Übersetzungsschicht zwischen zwei Systemen. Sie nimmt das Modell des externen Systems entgegen und übersetzt es in dein eigenes Modell — ohne dass dein Code das externe Modell kennen muss.

Eric Evans hat das Muster im Rahmen von [Domain-Driven Design](/articles/was-ist-domain-driven-design) geprägt:

> "As a downstream client, create an isolating layer to provide your system with functionality of the upstream system in terms of your own domain model. This layer talks to the other system through its existing interface, requiring little or no modification to the other system." — Eric Evans

Das Prinzip funktioniert unabhängig von DDD bei jeder Integration, bei der du ein externes Modell von deinem eigenen trennen möchtest.

Statt dein Modell dem externen System anzupassen, definierst du, wie dein Modell aussehen *soll* — und lässt den ACL die Übersetzung übernehmen. Dein System kennt das externe Modell nicht. Es kennt nur die saubere Schnittstelle, die der ACL bereitstellt.

![Bestellservice mit ACL: Der CrmAdapter übersetzt das CRM-Modell](/assets/img/articles/acl-mit-acl.png)
_Mit ACL: Die Übersetzungsschicht hält das Legacy-Format aus deinem Code heraus_

**ACL oder Conformist?** Der Gegenansatz zum ACL heißt *Conformist*: Du übernimmst das externe Modell so, wie es ist, und sparst dir die Übersetzungslogik. Das kann sinnvoll sein — macht dich aber direkt abhängig. Beide Ansätze schließen sich gegenseitig aus.

## Praxisbeispiel: Bestellservice mit CrmAdapter

Zurück zum Bestellservice. Statt das CRM-Modell zu kopieren, definieren wir zuerst unser eigenes Kundenmodell:

```typescript
interface Address {
  street: string;
  postalCode: string;
  city: string;
  country: string;
}

interface Customer {
  id: string;
  fullName: string;
  address: Address;
  isActive: boolean;
}
```

Dann schreiben wir den ACL — einen Adapter, der die Übersetzung übernimmt:

```typescript
class CrmAdapter {
  async getCustomer(customerId: string): Promise<Customer> {
    const raw = await this.crmClient.fetchCustomer(customerId);

    return {
      id: raw.KNDNR.replace(/^0+/, ""),
      fullName: `${raw.VNAME} ${raw.NNAME}`,
      address: {
        street: raw.STR_HNR,
        postalCode: raw.PLZ,
        city: raw.ORT,
        country: raw.LAND_ISO,
      },
      isActive: raw.AKTIV_FLG === "J",
    };
  }
}
```

Der `CrmAdapter` ist der ACL. Er ist die **einzige Stelle im gesamten Bestellservice, die das CRM-Format kennt**. Jeder andere Teil des Systems arbeitet ausschließlich mit `Customer`.

Benennt das CRM-Team `AKTIV_FLG` um, änderst du genau eine Zeile im Adapter. Der Rest des Bestellservices bemerkt davon nichts.

Das ist der Kern des Musters: **Änderungen im externen System stoppen an der Grenze. Sie dringen nicht in deinen Code ein.**

## Wann lohnt sich ein ACL?

Ein ACL kostet etwas: Du schreibst und pflegst die Übersetzungslogik. Der Aufwand lohnt sich, wenn mindestens eines dieser Kriterien zutrifft:

- **Kein Einfluss auf das externe Modell.** Das andere System gehört einem anderen Team oder einem Drittanbieter. Es kann sich jederzeit ändern — und du wirst nicht gefragt.
- **Das externe Modell bildet deine Fachlichkeit nicht ab.** Kryptische Feldnamen, Codes statt sinnvoller Typen, fehlende Struktur — das externe Modell spricht eine andere Sprache als dein System.
- **Du löst ein Legacy-System schrittweise ab.** Beim *Strangler Fig Pattern* wird ein Altsystem nicht auf einen Schlag ersetzt, sondern Schritt für Schritt von außen durch neue Services umschlossen. Jeder neue Service nutzt einen ACL zum Altsystem. Sobald das Altsystem vollständig abgelöst ist, fällt auch der ACL weg.

**Wann kein ACL nötig ist:** Ist das externe Modell sauber, ändert sich selten und liegt nah an deinem eigenen Modell, ist der Conformist-Ansatz oft die bessere Wahl. Unnötige Abstraktionen kosten mehr als sie nützen.

## Fazit

Der Anti-Corruption Layer ist ein wirkungsvolles Mittel gegen schleichende Komplexität bei System-Integrationen. Statt das Chaos externer Systeme ins eigene Haus zu lassen, ziehst du eine klare Grenze: Hier endet das externe Modell — dahinter beginnt deines.

Der initiale Mehraufwand zahlt sich aus, sobald sich das externe System ändert und du nur den Adapter aktualisieren musst. Oder wenn du das Altsystem irgendwann ablöst — dann wirfst du einfach den ACL weg, anstatt deinen Code zu durchkämmen.
