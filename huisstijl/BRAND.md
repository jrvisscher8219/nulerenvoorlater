# Huisstijl — Nu leren voor later

> **Doel van dit document:** als ontwikkelaar (mens of AI) lees je dit en weet je
> precies hoe de website, e-mails en social posts er uit moeten zien.
> Eén stem, één lettertype, één hoofdkleur.

---

## 1. Hoofdkleur

De website-kleur **#507A76** (salie-groen) is het anker. Alles bouwt daaromheen.

```
--sage:        #507A76   /* hoofdkleur · van de website */
--sage-deep:   #2F4D4A   /* tekst, accenten, donkere vlakken */
--sage-soft:   #A7C1BE   /* zachte vlakken, kaders */
--sage-tint:   #DDE7E5   /* lichte achtergrond */
--chalk:       #F6F4EF   /* warme witte achtergrond */
--ink:         #1F2926   /* lopende tekst */
--muted:       #4A544F   /* secundaire tekst */
--line:        #E3DDD0   /* hairline-dividers */
```

**Gebruiksregel:** één scherm/post = maximaal 2 kleuren naast elkaar.
Salie is altijd één van de twee. De andere is wit, krijt, tint of diep.

---

## 2. Typografie

Eén lettertype: **Inter Tight** (Google Fonts, gratis).
Voor labels, URL's en cijfers: **JetBrains Mono** (idem).

```
--font-sans: 'Inter Tight', system-ui, sans-serif;
--font-mono: 'JetBrains Mono', monospace;
```

| Rol      | Gewicht | Grootte    | Tracking | Leading |
| -------- | ------- | ---------- | -------- | ------- |
| Display  | 700     | 36–80px    | -0.03em  | 0.95    |
| Subkop   | 600     | 18–24px    | -0.01em  | 1.3     |
| Body     | 400     | 14–17px    | 0        | 1.55    |
| Label    | 500 mono| 11–13px    | 0.14em   | —       |

**Vuistregel:** niet meer dan 2 gewichten in één scherm. Geen italic.

---

## 3. Logo

Drie varianten staan klaar in `/huisstijl/`:

- `logo.png` — volledig logo (mark + woordmerk + tagline). Voor headers, banners.
- `logo-mark.png` — alleen de gloeilamp-mark. Voor avatars, kleine plekken.
- `logo-mark-white.png` — mark in krijt-kleur, voor donkere achtergrond.

**Witruimte-regel:** rondom het logo minimaal ½ × hoogte van de bol vrijhouden.
**Minimum formaat:** mark-only ≥ 32px, volledig logo ≥ 120px breed.

---

## 4. Componenten — hoe vertaal je dit naar de site

### Header / nav
- Logo links (volledig, ~48px hoog).
- Nav-items in `var(--ink)`, current-state onderlijnd in `var(--sage)`.
- Achtergrond: `var(--chalk)` of `#fff`.

### Hero / open scherm
- Display-tekst in `var(--ink)`, één gekleurd accentwoord in `var(--sage)`.
- Pattern: `"Menselijke visie. AI precisie."` — tweede zin in salie.
- Geen stockfoto's. Liever lege ruimte dan generieke onderwijsbeelden.

### Cards (workshop, blog, tool)
- Achtergrond: `#fff` met `1px solid var(--line)` óf `var(--chalk)` zonder rand.
- Kicker-label bovenaan in mono (`var(--sage)`, uppercase, letterspacing 0.14em).
- Titel in display-stijl, beschrijving in body-stijl.
- Geen schaduwen of gradiënten. Plat is het uitgangspunt.

### Knoppen
- Primair: `background: var(--sage-deep); color: var(--chalk); border-radius: 8px; padding: 12-14px 20px; font-weight: 600;`
- Secundair: `background: transparent; color: var(--sage-deep); border: 1.5px solid var(--sage-deep);`
- Geen hover-glows of grote schaduwen — alleen lichte darken (`--sage-deep` → 10% donkerder).

### Citaten / testimonials
- Open-quote-glyph (`"`) groot in `var(--sage)`, daarna tekst in display-stijl op `var(--sage-tint)` of `var(--chalk)`.
- Naam onderaan in body, met een korte salie-streep boven.

### Footer
- Achtergrond: `var(--sage-deep)`.
- Tekst in `var(--sage-soft)`, links in `var(--chalk)`.
- nulerenvoorlater.nl + e-mail + LinkedIn + Instagram + YouTube.
- Logo-mark in wit, links onderin.

---

## 5. Motieven (vier dingen die in elk component terugkomen)

1. **Index-label** — kleine mono-uppercase tag bovenaan secties. Voorbeeld: `TIP · #07`.
2. **Salie-balk** — 8px hoge gekleurde balk bovenaan iets nieuws (e-mail header, page-header).
3. **Hairline-divider** — 1px `var(--line)` tussen hook en bewijs.
4. **URL-mono** — `nulerenvoorlater.nl` in mono onderaan visueel materiaal.

---

## 6. Do's en niet's

✅ **Do**
- Logo-mark op dezelfde plek per kanaal (linksboven of rechtsboven).
- nulerenvoorlater.nl onderaan elk visueel beeld in mono.
- Eén accentwoord per kop, in salie.
- Veel witruimte. Liever leeg dan vol.

❌ **Niet**
- Geen stockfoto's met laptops + handen.
- Geen vierde kleur. Salie + 1 neutraal + diep is alles wat er is.
- Geen schaduwen, gradiënten of glas-effecten.
- Geen italic, geen ALL CAPS in body.
- Geen iconen "voor de duidelijkheid". Tekst is genoeg.
- Geen emoji's in body-tekst.

---

## 7. Tagline

> **Menselijke visie, AI precisie.**

Mag overal mee — banners, e-mail-handtekening, footer, social. Het is geen
slogan; het is de werkmethode in vier woorden.

---

## 8. Bestandenoverzicht

```
huisstijl/
├── BRAND.md              ← dit document
├── tokens.css            ← CSS-variabelen, klaar om te importeren
├── logo.png              ← volledig logo
├── logo-mark.png         ← alleen mark (avatar, klein)
└── logo-mark-white.png   ← mark voor donkere achtergrond
```
