# Design Map

Source: https://www.sweetgreen.com (home and /menu, 1440×900).

## Spacing Scale
- 8px, 12px: inside a component (12px is the most used value, 236 times)
- 24px: between elements of a group
- 48px 24px: grid gap (row, column)
- 120px: between every section (9 of 9 gaps)
- 40px: side padding of the page

## Font Hierarchy
- h1: 80px, weight 200, line-height 80px, letter-spacing -3.5px (Grenette)
- h2: 48px, weight 200, line-height 48px, letter-spacing -2.25px (Grenette)
- lead: 24px, weight 400 (SweetSansText)
- body: 18px, weight 400, line-height 22.5px (SweetSansText)
- ui and nav: 16px, weight 400, nav in uppercase (SweetSansText)
- caption: 12px, weight 400

## Color Palette
- `#F4F3E7` page background (30% of area)
- `#D8E5D6` section background (47%)
- `#D6E9E9` alternate section background (20%)
- `#00473C` primary text and the nav button
- `#0E150E` strong text
- `#E6FF55` accent: main button and badges only (2.9% of area)

## Image Ratios
- product: 1:1 (600×600, shown at 432px)
- hero: ~16:9, full width

## Component Tokens
- border-radius: 20px (photos and media), 1000px (buttons and badges)
- shadows: none
- borders on cards: none
- grid: 3 columns, gap 48px 24px, full width with 40px side padding
- transitions: `color 0.3s ease-in-out`, `opacity 0.25s ease-in-out`, `transform 0.3s ease-in-out`
- `:focus-visible` and `prefers-reduced-motion` are both handled

---

# Taste DNA

### The photo is the card
- **Trigger**: A grid of dishes to compare.
- **Decision**: An unframed square photo with text set directly on the page, over a white card with border, shadow and padding.
- **Reason**: People choose food with their eyes; a frame around each dish makes you look at the frames.
- **Evidence**: 10 of 10 product images 1:1 at 432px, radius 20px; 0 card containers detected; 0 shadows; the 48px/24px gap does the separating.

### Rooms, not boxes
- **Trigger**: One topic has to be separated from the next.
- **Decision**: Full-width background colour changes and 120px of air, over cards, dividers and shadows.
- **Reason**: A change of wall colour tells you that you walked into another room without anyone drawing a line on the floor.
- **Evidence**: 3 section backgrounds (`#F4F3E7`, `#D8E5D6`, `#D6E9E9`) cover 97% of the area; 9 of 9 section gaps are 120px; no shadows at all.

### One loud thing per screen
- **Trigger**: The order action has to be findable everywhere.
- **Decision**: A single high-voltage accent on under 3% of the surface, over spreading brand colour across headers, icons and links.
- **Reason**: When only one thing shouts, nobody has to hunt for the button.
- **Evidence**: `#E6FF55` at 2.9% of area with 4 interactive uses; every other colour is a green-tinted neutral (`#00473C`, `#0E150E`).

### Hierarchy by size, tight inside and wide outside
- **Trigger**: Setting the type and spacing scale.
- **Decision**: Huge thin headings and a two-speed spacing scale (8–12px inside a group, 120px between groups), over bold weights and evenly graded gaps.
- **Reason**: A big quiet headline reads as confidence, and things that sit close together are understood as one thing.
- **Evidence**: h1 80px and h2 48px at weight 200 with line-height 1.0; 536 of 549 text nodes at weight 400; spacing 12px ×236, 8px ×75, 120px ×9 with little in between.
