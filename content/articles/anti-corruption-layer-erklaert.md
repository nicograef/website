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

Damit hast du das CRM-Modell in deinen Bestellservice kopiert. Dein Code verwendet jetzt Feldnamen, die niemand ohne CRM-Dokumentation versteht. Alles ist ein `string`, obwohl `isActive` ein Boolean sein sollte. Ob ein Kunde aktiv ist, bestimmt `AKTIV_FLG === "J"` — eine interne CRM-Konvention, die nichts in deinem Bestellservice zu suchen hat.

Benennt das CRM-Team `AKTIV_FLG` irgendwann um, bricht dein Service. Du bist direkt von Entscheidungen abhängig, die in einem anderen System getroffen werden.

![Bestellservice ohne ACL: Das CRM-Modell fließt direkt in den Bestellservice](/assets/img/articles/acl-ohne-acl.png)
_Ohne ACL: Das CRM-Modell fließt direkt in den Bestellservice_

## Das Pattern: Der Anti-Corruption Layer

Der **Anti-Corruption Layer** (ACL) ist eine Übersetzungsschicht zwischen zwei Systemen. Er nimmt das Modell des externen Systems entgegen und übersetzt es in dein eigenes Domänenmodell. Dein Code kennt das externe Format nicht — er arbeitet nur mit der Schnittstelle, die der ACL bereitstellt.

> "As a downstream client, create an isolating layer to provide your system with functionality of the upstream system in terms of your own domain model. This layer talks to the other system through its existing interface, requiring little or no modification to the other system." (Eric Evans)

Du passt dein Modell nicht dem externen System an. Du definierst zuerst, wie dein Modell aussehen soll, und schreibst dann den ACL, der die Übersetzung übernimmt.

![Bestellservice mit ACL: Der CrmAdapter übersetzt das CRM-Modell](/assets/img/articles/acl-mit-acl.png)
_Mit ACL: Der Adapter übersetzt das CRM-Format, bevor es deinen Code erreicht_

Das Gegenstück zum ACL ist der **Conformist**: Du übernimmst das externe Modell unverändert und sparst dir die Übersetzungslogik. Weniger Aufwand, aber direkte Abhängigkeit. Die Wahl ist binär — du übersetzt oder du übernimmst.

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

Der `CrmAdapter` ist der ACL. Er ist die **einzige Stelle im gesamten Bestellservice, die das CRM-Format kennt**. Jeder andere Teil des Systems arbeitet mit `Customer` — mit sprechenden Feldnamen, typisierten Werten und einer Struktur, die zum Bestellservice passt.

Benennt das CRM-Team ein Feld um, änderst du eine Zeile im Adapter. Der Rest deines Codes bemerkt nichts.

## Wann lohnt sich ein ACL?

Du schreibst und pflegst die Übersetzungslogik — das kostet etwas. Der Aufwand lohnt sich in drei Situationen:

- **Kein Einfluss auf das externe System.** Es gehört einem anderen Team oder einem Drittanbieter. Änderungen kommen ohne Vorwarnung.
- **Das externe Modell passt nicht zu deiner Fachlichkeit.** Kryptische Feldnamen, Codes statt sinnvoller Typen, eine Struktur, die mit deiner Domäne nichts zu tun hat.
- **Du löst ein Altsystem schrittweise ab.** Beim *Strangler Fig Pattern* umschließen neue Services das Altsystem von außen, Stück für Stück. Jeder neue Service nutzt einen ACL. Sobald das Altsystem abgelöst ist, fällt der ACL weg.

**Wann kein ACL nötig ist:** Wenn das externe Modell sauber ist, sich selten ändert und nah an deinem eigenen Modell liegt, reicht der Conformist-Ansatz. Nicht jede Integration braucht eine Übersetzungsschicht.

## Fazit

Der ACL löst ein konkretes Problem: Er hält fremde Modelle aus deinem Code heraus. Änderungen im externen System enden am Adapter. Dein Code bleibt unabhängig.

Den größten Wert hat der ACL am Tag, an dem du das Altsystem endgültig ablöst. Dann entfernst du den Adapter — und der Rest deines Codes funktioniert weiter, ohne dass du eine einzige Zeile anfassen musst.
