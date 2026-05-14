# Changelog

## [1.3.2] - 2026-05-14

### Fixed

- `Throbber` `{rotating_message}` : décalage de couleur indépendant du cycle du spinner (`rotatingColorOffset`, incrémenté à chaque `tick()`) pour éviter le « saut » visuel quand les images du style repartent à zéro.

### Added

- `Throbber::rotatingColorOffset()` : lecture du décalage cumulé pour le message tournant.

## [1.3.1] - 2026-05-14

### Added

- `Ansi::fmtForeground256(int $code, string $text)` — avant-plan jeu 256 couleurs (`38;5`).

### Changed

- `RotatingColors::defaultPalette()` utilise des niveaux de gris intermédiaires (**237, 240, 244, 255, …**) pour un dégradé plus doux ; `render()` accepte une palette `list<TermColor|int>` (`int` = code 256).

## [1.3.0] - 2026-05-14

### Added

- `RotatingColors` : effet « couleurs tournantes » sur une chaîne (`render($text, $offset, $palette)`), palette par défaut gris / gris clair / blanc.
- `Throbber` : placeholder `{rotating_message}` — message coloré avec le même décalage que l’index d’animation (`tick()`).

### Added (tests / examples)

- `tests/rotating_colors.php`, `examples/rotating_colors.php`.

## [1.2.1] - 2026-05-14

### Fixed

- `examples/throbber.php` : plus d’avertissement `ob_flush(): Failed to flush buffer` quand `output_buffering` est désactivé ; utilitaire `throbber_demo_flush_output()` qui n’appelle `ob_flush()` que si `ob_get_level() > 0`.

## [1.2.0] - 2026-05-14

### Added

- `Throbber` : placeholder `{bar}` — barre de progression Unicode (`█` / `░`) de largeur configurable via `barWidth(int $width)`.
- `Throbber` : `STYLE_DOTS` (`⣾⣽⣻⢿⡿⣟⣯⣷`, 8 images — braille circulaire dense) et `STYLE_LINE` (`▁▂▃▄▅▆▇█▇▆▅▄▃▂`, 14 images — barre montante/descendante).

### Changed

- `examples/throbber.php` : démo entièrement repensée — section défilement restaurée avec couleurs, section barre de progression avançant de 0 à 100 %, section deux tâches en parallèle avec barres colorées ; chaque frame bufferisée en une seule chaîne + `ob_flush()/flush()` pour éliminer les états partiels à l'écran.
- `Throbber` : fix indentation dans le docblock (ligne introductive).
- `tests/throbber.php` : tests ajoutés pour `{bar}` (0 %, 50 %, 100 %, `barWidth` personnalisée, `{bar}` sans percent), `STYLE_DOTS` et `STYLE_LINE`.

## [1.1.13] - 2026-05-14

### Changed

- `examples/throbber.php` : démo deux lignes avec `Terminal::cursorHide()` / `cursorShow()` (+ `finally`) pendant la réécriture pour éviter le curseur qui saute ou clignote.

## [1.1.12] - 2026-05-14

### Added

- `Terminal` : `carriageReturn()`, `lineOverwritePrefix()` (préfixe réécriture ligne : `\r\e[2K`).
- `examples/throbber.php` : réécritures via `Terminal` (`cursorPreviousLine`, `eraseLine`, `lineOverwritePrefix`).

### Changed

- `Throbber::lineRefreshPrefix()` délègue à `Terminal::lineOverwritePrefix()`.

## [1.1.11] - 2026-05-14

### Changed

- `examples/throbber.php` : démo « deux lignes » mises à jour en place (`\e[1A\r` depuis la fin de la 2ᵉ ligne + effacement).

## [1.1.10] - 2026-05-14

### Changed

- `examples/throbber.php` : section supplémentaire « une ligne » avec `lineRefreshPrefix()` + `flush()` pour mise à jour en place.

## [1.1.9] - 2026-05-14

### Added

- `Throbber` : indicateur d’activité terminal (styles `braille`, `pipe`), `tick()`, message, pourcentage, durée depuis `start()`, modèle `{spinner} {message}{trail}` et placeholders (`percent`, `elapsed`, `elapsed_paren`, etc.), `registerStyle()` pour styles personnalisés, `lineRefreshPrefix()` pour réécrire une ligne.
- `tests/throbber.php`, `examples/throbber.php`.

## [1.1.8] - 2026-05-14

### Added

- `README.md` : project overview in English (install, components, examples, tests, tooling).

## [1.1.7] - 2026-05-14

### Added

- `Ansi` : `stripSgr()`, `displayWidth()` pour chaînes avec séquences CSI SGR.
- `Console` : `stdinIsTty()`, `readLine()` pour l’entrée standard.
- `tests/ansi_console.php` : assertions sur ces utilitaires.

### Changed

- `Frame` utilise `Ansi` pour la mesure du contenu et le rognage des lignes colorées.
- `examples/showcase.php` : centre les blocs avec `Ansi`, pause avec `Console`.
- `tests/frame_edges.php` : largeurs visibles via `Ansi::stripSgr`.

## [1.1.6] - 2026-05-14

### Fixed

- `Frame::render()` / `alignLine()` : largeur du contenu mesurée sans les séquences CSI couleur (`\e[…m`), pour que le remplissage aligne correctement les bordures verticales quand le texte contient de l’ANSI.
- `Frame::alignLine()` : ne plus tronquer lorsque la largeur affichée égale exactement la colonne utile (`>` au lieu de `>=`), et tronquer sur le texte sans ANSI si dépassement (évite une coupure au milieu des séquences ou du texte visible).

### Added

- `tests/frame_edges.php` : cadre à lignes de largeurs affichées égales avec contenu partiellement coloré ; ligne qui « remplit » exactement la largeur utile avec ANSI sans être tronquée.

### Changed

- `examples/showcase.php` : `minInnerWidth(7)` sur les quatre mini-cadres (mot le plus long « rounded ») pour aligner leurs bordures entre eux.

## [1.1.5] - 2026-05-14

### Added

- `examples/showcase.php` : démo plein écran (`Terminal`), bannière `AsciiText`, panneaux `Frame`, dimensions via `screenSize()`, sortie sur Entrée si stdin est un TTY.

## [1.1.4] - 2026-05-14

### Fixed

- `Terminal::screenSize()` : sous Windows, utiliser `2>nul` au lieu de `2>/dev/null` pour les appels `stty` / `tput` (évite l’erreur cmd « Le chemin d’accès spécifié est introuvable »).

## [1.1.3] - 2026-05-14

### Added

- `Terminal::screenSize()` : dimensions du terminal (`LINES`/`COLUMNS`, `posix_ioctl` si dispo, `mode con` sous Windows, puis `stty` / `tput`).
- `Terminal::parseCursorPositionResponse()` : interprétation d’une réponse CPR (`\e[row;colR`).
- `Terminal::eraseLine()`, `eraseLineEnd()`, `enterAlternateScreen()`, `leaveAlternateScreen()`.

### Fixed

- `Terminal::cursorPosition()` : lecture complète de la réponse CPR (au lieu de 3 octets), `fflush(STDOUT)`, flux par défaut `/dev/tty` sous Unix ; type de retour `?array`.
- `Terminal::cursorDisable()` / `cursorEnable()` : délégation à `cursorHide()` / `cursorShow()` pour éviter la duplication.

### Added (tests)

- `tests/terminal.php` : parsing CPR et contrôle basique de `screenSize()`.

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
