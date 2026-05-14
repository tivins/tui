# tivins/tui

Small PHP helpers for terminal user interfaces: **cursor and screen control**, **Unicode bordered frames**, **side-by-side layout** of multiline blocks, **text progress bars**, **ANSI colors** (16-color and 256-color foregrounds), a lightweight **ASCII banner** renderer, **ANSI-aware** string utilities, and a **spinner** with optional **rotating grayscale** on messages.

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
| Layout | `Layout` | `horizontal(array $blocks, int $gap = 1)` — place blocks side by side; each row is padded using `Ansi::displayWidth` so borders stay aligned. |
| Progress | `ProgressBar` | `render($percent, $width, ...)` — Unicode-filled bar (`█` / `░`) without a spinner; complements `Throbber`’s `{bar}` placeholder. |
| Banners | `AsciiText` | Three-line glyphs (Future Smooth subset) via `AsciiText::get()` and `AsciiText::toString()`. |
| Colors | `TermColor` | `TermColor::*->fmt(string)` wraps text in SGR sequences. |
| ANSI & 256-color | `Ansi` | `stripSgr()`, `displayWidth()`, `fmtForeground256(int $code, string)` for `\e[38;5;nm` foregrounds (e.g. grayscale 232–255). |
| Text effect | `RotatingColors` | Shifting palette along a string: `render($text, $offset, $palette)`; default grayscale uses smooth 256-color steps; palette entries may be `TermColor` or 256-color indices (`int`). |
| stdin | `Console` | `stdinIsTty()` and `readLine()` for interactive pauses without mixing stdin with terminal inquiries. |
| Activity | `Throbber` | Spinner (styles: braille, pipe, dots, line), template placeholders including `{message}`, `{rotating_message}` (grayscale wave; offset advances every `tick()` via internal `rotatingColorOffset`, independent of spinner wrap), `{bar}`, percent, elapsed, `registerStyle()`, `barWidth()`. |

## Quick examples

### Rotating colors

`RotatingColors::render()` applies a repeating palette along each UTF-8 character. The offset usually increases by one per animation frame. With `Throbber`, use `{rotating_message}` so the wave stays smooth when the spinner loops (the library uses a monotonic color offset, not the spinner frame index).

```php
use Tivins\Tui\RotatingColors;
use Tivins\Tui\Throbber;

echo RotatingColors::render('thinking...', $offset) . "\n";

$th = (new Throbber())
    ->message('thinking...')
    ->template('{spinner} {rotating_message}');
// call $th->tick() each frame; optional: $th->rotatingColorOffset()
```

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

### Side-by-side blocks and progress bar

```php
use Tivins\Tui\Frame;
use Tivins\Tui\Layout;
use Tivins\Tui\ProgressBar;

$left = Frame::from("A")->minInnerWidth(8)->render();
$right = Frame::from("B")->minInnerWidth(8)->render();
echo Layout::horizontal([$left, $right], 2) . "\n";

echo ProgressBar::render(37.5, 12) . "\n"; // e.g. ████░░░░░░░░
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
| `php examples/fullscreen_demo.php` | Full-screen animated dashboard: `Layout::horizontal`, nested `Frame`s, `Throbber`, `RotatingColors`, `ProgressBar`; `cursorHome` refresh; Enter when stdin is a TTY; short loop if not. |
| `php examples/frame.php` | `Frame` variants and edge cases. |
| `php examples/ascii_text.php` | Banner sample. |
| `php examples/throbber.php` | `Throbber` : défilement, une ligne puis deux lignes mises à jour en place (`\r`/`\e[2K`, `\e[1A`). |
| `php examples/rotating_colors.php` | `RotatingColors` and `{rotating_message}` : static output and a short live demo. |

## Tests

Scripts under `tests/` are plain PHP assertions (no PHPUnit):

```bash
php tests/terminal.php
php tests/frame_edges.php
php tests/ascii_text.php
php tests/throbber.php
php tests/rotating_colors.php
php tests/layout.php
php tests/progress_bar.php
php tests/ansi_console.php
```

## Tooling

The glyph map for `AsciiText` is generated from `tools/Future_Smooth.flf`:

```bash
php tools/generate_future_smooth.php
```

See [CHANGELOG.md](CHANGELOG.md) for release notes.

## License

MIT.
