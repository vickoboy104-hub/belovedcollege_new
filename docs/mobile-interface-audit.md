# Mobile interface audit — 13 July 2026

The first responsive pass is based on the supplied Android screenshots and is intentionally scoped below the desktop breakpoint.

## Corrected patterns

- Compact fixed header with readable school identity and visible search control.
- Safe content offset below the fixed header.
- Consistent page gutters instead of narrow centered desktop columns.
- Two-column metric cards on phone screens.
- Compact quick-action and announcement cards.
- Standard mobile form-control height, including report quick search.
- Horizontally scrollable section navigation without clipping the last category.
- Student cards that wrap names at word boundaries.
- Fixed bottom navigation that does not cover final content.
- Only one bottom-navigation destination appears active.

## Verification widths

Test the following viewport widths after pulling the branch:

- 320px
- 360px
- 375px
- 390px
- 412px
- 768px

## Pages represented in the supplied screenshots

- Dashboard
- People Hub
- Student directory
- Student categories
- Report directory and quick search
- Individual student report workspace

Additional modules should be reviewed separately before receiving page-specific mobile rules.
