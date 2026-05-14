# Changelog

## [1.1.2] - 2026-05-14

### Added

- `AsciiText` : jeu complet Future Smooth pour `AsciiText::CHARSET`, généré depuis `Future_Smooth.flf` (`AsciiTextGlyphs.generated.php`) avec `php tools/generate_future_smooth.php`.
- `tests/ascii_text.php` : assertions sur la carte des glyphes et la composition.

### Changed

- `AsciiText::get()` utilise le découpage Unicode (`preg_split`) et `mb_strtolower()` pour les majuscules ; `toString()` insère un espace entre glyphes et joint les lignes avec `\n`.

## [1.1.1] - 2026-05-14

### Added

- `examples/frame.php` : démonstrations et cas limites.
- `tests/frame_edges.php` : assertions sans phpunit (UTF-8, CRLF, padding, titres ambigus, largeur stable).

### Fixed

- `Frame::render()` utilise des sauts de ligne `\n` (affichage terminal cohérent, hors `PHP_EOL`).
- Titres : colorisation par plage de caractères (titres dupliquant le glyphe horizontal).
- Troncature des titres selon la largeur utile et `horizontalTitleNeed` tenant compte de la largeur du glyphe `h`.
- Normalisation `\r\n` / `\r` dans le contenu ; padding négatif ignoré (clamp).

## [1.1.0] - 2026-05-14

### Added

- `Frame` component: terminal boxes with presets (single, double, rounded, heavy), optional top/bottom titles with alignment, padding, custom border glyphs, and separate ANSI colors for border vs. content (`Tivins\Tui\Frame`).
