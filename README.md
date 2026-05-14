# tivins/tui

Small PHP helpers for terminal user interfaces: **cursor and screen control**, **Unicode bordered frames**, **ANSI colors**, a lightweight **ASCII banner** renderer, and **ANSI-aware** string utilities.

Requires **PHP 8.1+** (typed `enum`). The **mbstring** extension is recommended for correct Unicode handling in banners and width calculations.

## Install

```bash
composer require tivins/tui
```

There are **no Composer runtime dependencies**.

## Highlights

| Area | Classes | What they do |
|------|---------|---------------|
| Screen & cursor | `Terminal` | Clear/move cursor, CPR, `eraseLine`, alternate screen, `screenSize()`, `carriageReturn()`, `lineOverwritePrefix()` (réécrire une ligne), `cursorPreviousLine()` (CPL, colonne 1). |
| Boxes | `Frame` | Multiline bordered panels: presets `single`, `double`, `rounded`, `heavy`, optional titles, padding, independent border/content/title colors, colored content with correct alignment. |
| Banners | `AsciiText` | Three-line glyphs (Future Smooth subset) via `AsciiText::get()` and `AsciiText::toString()`. |
| Colors | `TermColor` | `TermColor::*->fmt(string)` wraps text in SGR sequences. |
| ANSI strings | `Ansi` | `stripSgr()` and `displayWidth()` for layouts that ignore escape codes when measuring width. |
| stdin | `Console` | `stdinIsTty()` and `readLine()` for interactive pauses without mixing stdin with terminal inquiries. |
| Activity | `Throbber` | Spinner (UTF-8 braille or ASCII pipe style), customizable template, optional percent and elapsed time, `registerStyle()` for more sprite sets. |

## Quick examples

### Framed panel

```php
<?php
require 'vendor/autoload.php';

use Tivins\Tui\Frame;
use Tivins\Tui\TermColor;

echo Frame::from("Hello,\nterminal.")
    ->borderStyle(Frame::STYLE_ROUNDED)
    ->borderColor(TermColor::Cyan)
    ->contentColor(TermColor::LightGray)
    ->title('demo', Frame::ALIGN_CENTER)
    ->padding(1)
    ->render() . "\n";
```

Use `render()` explicitly when you need a string inside another expression:

```php
$box = Frame::from('OK')->borderStyle(Frame::STYLE_DOUBLE)->render();
```

### Terminal size and fullscreen demo

```php
<?php
use Tivins\Tui\Terminal;

$size = Terminal::screenSize(); // ['rows' => ..., 'cols' => ...] or null
Terminal::enterAlternateScreen();
Terminal::clear();
echo "rows: {$size['rows']}\n";

// ...

Terminal::leaveAlternateScreen();
```

### ASCII banner

Only characters listed in `AsciiText::CHARSET` have glyphs (others render as blanks). Typical flow:

```php
use Tivins\Tui\AsciiText;

$letters = AsciiText::get('hello');
echo AsciiText::toString($letters), "\n";
```

## Runnable demos

From the repository root:

| Command | Description |
|---------|-------------|
| `php examples/showcase.php` | Full-screen demo (alternate buffer, banners, framed panels). Waits for Enter when stdin is a TTY. |
| `php examples/frame.php` | `Frame` variants and edge cases. |
| `php examples/ascii_text.php` | Banner sample. |
| `php examples/throbber.php` | `Throbber` : défilement, une ligne puis deux lignes mises à jour en place (`\r`/`\e[2K`, `\e[1A`). |

## Tests

Scripts under `tests/` are plain PHP assertions (no PHPUnit):

```bash
php tests/terminal.php
php tests/frame_edges.php
php tests/ascii_text.php
php tests/throbber.php
```

## Tooling

The glyph map for `AsciiText` is generated from `tools/Future_Smooth.flf`:

```bash
php tools/generate_future_smooth.php
```

See [CHANGELOG.md](CHANGELOG.md) for release notes.

## License

MIT.
