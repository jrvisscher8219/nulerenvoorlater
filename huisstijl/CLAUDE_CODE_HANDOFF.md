# Handoff-prompt voor Claude Code

Kopieer onderstaande tekst en plak 'm in Claude Code in VS Code.
Vervang `<huisstijl-map>` door het pad waar je `huisstijl/` neerzet
(bv. `assets/huisstijl/` of gewoon `huisstijl/` in de root).

---

## Prompt 1 — Eerste analyse (begin hiermee)

```
Ik heb een nieuwe huisstijl aangeleverd in de map huisstijl/.

Doe eerst een analyse — verander nog niets:
1. Lees huisstijl/BRAND.md helemaal door.
2. Lees huisstijl/tokens.css.
3. Inventariseer mijn huidige website: welke HTML-pagina's en CSS-bestanden
   zijn er? Welke kleuren, fonts en componenten gebruik ik nu?
4. Maak een lijst van wat moet veranderen om aan de huisstijl te voldoen,
   gerangschikt van klein naar groot. Schat per item de impact in
   (cosmetisch / structureel / inhoudelijk).
5. Stel voor in welke volgorde we het beste kunnen werken.

Begin pas met aanpassen als ik akkoord geef.
```

---

## Prompt 2 — Foundation invoeren

```
Voer stap 1 van het plan uit: integreer huisstijl/tokens.css in mijn site.

- Importeer tokens.css als allereerste in mijn hoofd-stylesheet.
- Vervang bestaande kleur-variabelen door de --sage-* en --chalk varianten
  uit tokens.css, behoud de oude waardes als comment voor referentie.
- Vervang bestaande font-stack door var(--font-sans).
- Laat me één commit per logisch blok zien zodat ik het kan reviewen
  voor je doorgaat.
```

---

## Prompt 3 — Componenten

```
Werk per component de stijl bij volgens BRAND.md sectie 4:
header, hero, cards, knoppen, testimonials, footer.

Per component:
- Toon eerst een diff-voorstel.
- Wacht op mijn ok.
- Pas dan aan.

Belangrijk:
- Verander geen content/copy zonder dat te benoemen.
- Geen nieuwe componenten toevoegen — alleen bestaande aanpassen.
- Houd je aan de "Niet"-regels uit BRAND.md (geen schaduwen, geen italic,
  max 2 kleuren per scherm).
```

---

## Prompt 4 — Logo's vervangen

```
Vervang verwijzingen naar mijn oude logo door de bestanden in huisstijl/:
- huisstijl/logo.png        → header en e-mail
- huisstijl/logo-mark.png   → favicon, avatar-formaten, footer-mark
- huisstijl/logo-mark-white.png → footer (donkere achtergrond)

Update ook open-graph-tags en favicon.
```

---

## Tips bij het werken met Claude Code

1. **Werk in kleine stappen.** Eén prompt = één component of één concept.
   Niet "maak mijn hele site huisstijl-proof in één keer".

2. **Vraag altijd eerst om een diff-voorstel** voordat het écht aanpast.
   Claude Code kan dat — gewoon zeggen: *"laat eerst de diff zien"*.

3. **Commit per stap.** Na elke geaccepteerde verandering een git commit.
   Dan kun je terug als iets niet bevalt.

4. **Browser ernaast openhouden** met live-reload (bv. `live-server` of
   de Live Preview-extensie in VS Code) zodat je direct ziet wat verandert.

5. **Je hoeft geen designer te zijn** om dit te beoordelen — kijk gewoon of
   het lijkt op de templates in Huisstijl B.html. Klopt het qua kleur, font,
   ruimte en logo-positie? Dan ben je goed.

6. **Als iets niet bevalt:** zeg precies wat. *"De knop is te groot"* werkt
   slechter dan *"De knop heeft te veel padding — maak 'm 12px ipv 16px"*.
   Verwijs naar BRAND.md als referentie.

---

## Wat als je vastloopt?

- **Claude Code begrijpt je codebase niet?** Open één bestand handmatig in
  VS Code en zeg: *"kijk naar dit bestand en stel een aanpak voor"*.
- **Stylesheet conflict?** Vraag: *"Welke CSS-regels overschrijven mijn
  --sage variabelen?"*.
- **Iets gaat helemaal mis?** `git reset --hard HEAD` zet alles terug
  naar de laatste commit.

---

## Checklist achteraf

Open je vernieuwde site en loop deze lijst af:

- [ ] Salie-groen #507A76 is duidelijk de hoofdkleur
- [ ] Inter Tight is overal het lettertype
- [ ] Logo zit linksboven, op elke pagina dezelfde plek
- [ ] Footer heeft donkergroene achtergrond met witte mark
- [ ] Geen stockfoto's met laptops + handen
- [ ] Geen schaduwen of gradiënten
- [ ] Knoppen zijn salie-deep met krijt-kleurige tekst
- [ ] nulerenvoorlater.nl staat in de footer in monospace
- [ ] Mobiel (375px breed) leest het nog
- [ ] Tagline "Menselijke visie, AI precisie." komt minstens één keer voor
