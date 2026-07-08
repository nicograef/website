# Anti-Corruption Layer: Saubere Grenzen zwischen deinem Code und Legacy-Systemen

Jedes Unternehmen ab einer gewissen Größe hat Altsysteme. Ein CRM mit kryptischen Feldnamen, ein ERP aus einer anderen Ära, eine SOAP-Schnittstelle, die seit Jahren keiner angefasst hat. Alle brauchen sie, niemand will an sie ran.

Dein neues System braucht Daten aus so einem Altsystem. Du bindest die Schnittstelle an, übernimmst die Datenstruktur, und nach ein paar Wochen sieht dein Code aus wie das Altsystem. Der **Anti-Corruption Layer** verhindert das.

## Das Problem: Wenn das Legacy-Modell deinen Code übernimmt

Du baust einen neuen Bestellservice. Für jede Bestellung brauchst du Kundendaten, und die liefert das Legacy-CRM.

Das CRM antwortet mit folgendem JSON:

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

Der schnellste Weg: Du modellierst deinen Kunden direkt nach dieser Struktur.

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

Damit hast du das CRM-Modell in deinen Bestellservice kopiert. Dein Code verwendet jetzt Feldnamen, die niemand ohne CRM-Dokumentation versteht. Alles ist ein `string`, obwohl `isActive` ein Boolean sein sollte. Ob ein Kunde aktiv ist, bestimmt `AKTIV_FLG === "J"`, eine interne CRM-Konvention, die nichts in deinem Bestellservice zu suchen hat.

Benennt das CRM-Team `AKTIV_FLG` irgendwann um, bricht dein Service. Du bist direkt von Entscheidungen abhängig, die in einem anderen System getroffen werden.

Der Datenfluss sieht dann so aus:

```text
┌───────────────────────────┐
│        Legacy-CRM         │
│ KNDNR · NNAME · AKTIV_FLG │
└─────────────┬─────────────┘
              │  liefert CRM-Format
              ▼
┌───────────────────────────┐
│      Bestellservice       │
│ KNDNR · NNAME · AKTIV_FLG │
│ überall im Code verteilt  │
└───────────────────────────┘
```

Die untere Box ist das Problem: Das CRM-Vokabular liegt nicht an einer Stelle, sondern verteilt sich über jede Datei, die Kundendaten anfasst. Genau das macht die Umbenennung von `AKTIV_FLG` so teuer.

## Das Pattern: Der Anti-Corruption Layer

Der **Anti-Corruption Layer** (ACL) ist eine Übersetzungsschicht zwischen zwei Systemen. Er nimmt das Modell des externen Systems entgegen und übersetzt es in dein eigenes Domänenmodell. Dein Code kennt das externe Format nicht. Er arbeitet nur mit der Schnittstelle, die der ACL bereitstellt.

> „As a downstream client, create an isolating layer to provide your system with functionality of the upstream system in terms of your own domain model. This layer talks to the other system through its existing interface, requiring little or no modification to the other system.“ (Eric Evans)

Du passt dein Modell nicht dem externen System an. Du definierst zuerst, wie dein Modell aussehen soll, und schreibst dann den ACL, der die Übersetzung übernimmt.

Mit ACL sieht derselbe Datenfluss so aus:

```text
┌───────────────────────────┐
│        Legacy-CRM         │
│ KNDNR · NNAME · AKTIV_FLG │
└─────────────┬─────────────┘
              │  liefert CRM-Format
              ▼
┌───────────────────────────┐
│     CrmAdapter (ACL)      │
│ übersetzt ins eigene      │
│ Domänenmodell             │
└─────────────┬─────────────┘
              │  liefert Customer
              ▼
┌───────────────────────────┐
│      Bestellservice       │
│ kennt nur Customer        │
│ und Address               │
└───────────────────────────┘
```

Der entscheidende Unterschied steckt im mittleren Kasten: Oben kommt das CRM-Format an, unten kommt nur noch `Customer` heraus. Der Bestellservice bekommt vom CRM-Vokabular nichts mehr mit.

Das Gegenstück zum ACL ist der **Conformist**: Du übernimmst das externe Modell unverändert und sparst dir die Übersetzungslogik. Das ist weniger Aufwand, dafür hängst du direkt am fremden Modell.

## Praxisbeispiel: CrmAdapter als Übersetzungsschicht

Zurück zum Bestellservice. Statt das CRM-Modell zu kopieren, definierst du zuerst dein eigenes Kundenmodell:

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

Dann schreibst du einen Adapter, der die Übersetzung übernimmt:

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

Der `CrmAdapter` ist der ACL. Er ist die **einzige Stelle im gesamten Bestellservice, die das CRM-Format kennt**. Jeder andere Teil des Systems arbeitet mit `Customer`: mit sprechenden Feldnamen, typisierten Werten und einer Struktur, die zum Bestellservice passt.

Benennt das CRM-Team ein Feld um, änderst du eine Zeile im Adapter. Der Rest deines Codes bemerkt nichts.

## Wann lohnt sich ein ACL?

Du schreibst und pflegst die Übersetzungslogik. Das kostet etwas. Der Aufwand lohnt sich in drei Situationen:

- **Kein Einfluss auf das externe System.** Es gehört einem anderen Team oder einem Drittanbieter. Änderungen kommen ohne Vorwarnung.
- **Das externe Modell passt nicht zu deiner Fachlichkeit.** Kryptische Feldnamen, Codes statt sinnvoller Typen, eine Struktur, die mit deiner Domäne nichts zu tun hat.
- **Du löst ein Altsystem schrittweise ab.** Beim *Strangler Fig Pattern* umschließen neue Services das Altsystem von außen, Stück für Stück. Jeder neue Service nutzt einen ACL. Sobald das Altsystem abgelöst ist, fällt der ACL weg.

Bei jotti, meinem Kassensystem für Vereine, habe ich die Anbindung an die <abbr title="Technische Sicherheitseinrichtung">TSE</abbr>, die jeden Kassenvorgang signiert, nach diesem Prinzip gebaut. Die Domäne definiert ein eigenes `TSEClient`-Interface in `backend/domain/tse/client.go`; die Implementierung für den TSE-Anbieter fiskaly liegt separat in `backend/repository/tse_repo/`. Der Rest des Codes kennt nur das eigene Interface. Das fiskaly-API-Format endet im Adapter. Streng genommen ist das ein Adapter um einen Drittanbieter, kein Legacy-System. Der Mechanismus ist aber derselbe: Das fremde Modell endet an der Übersetzungsschicht.

**Wann kein ACL nötig ist:** Wenn das externe Modell sauber ist, sich selten ändert und nah an deinem eigenen Modell liegt, reicht der Conformist-Ansatz. Nicht jede Integration braucht eine Übersetzungsschicht.

## Fazit

Der ACL löst ein konkretes Problem: Er hält fremde Modelle aus deinem Code heraus. Änderungen im externen System enden am Adapter. Und am Tag, an dem du das Altsystem endgültig ablöst, entfernst du einfach den Adapter. Der Rest deines Codes funktioniert weiter, ohne dass du eine Zeile anfassen musst.

Wenn du den `TSEClient` und seinen fiskaly-Adapter im Original sehen willst: jotti ist source-available, der komplette Code liegt auf [github.com/nicograef/jotti](https://github.com/nicograef/jotti). Was hinter dem Kassensystem steckt und warum es auf die <abbr title="Kassensicherungsverordnung">KassenSichV</abbr> ausgelegt ist, findest du auf [jotti.rocks](https://jotti.rocks).
