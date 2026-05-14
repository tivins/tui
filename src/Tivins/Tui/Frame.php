<?php

declare(strict_types=1);

namespace Tivins\Tui;

/**
 * Terminal frame with pluggable border glyphs, optional titles, padding,
 * and independent colors for border vs. inner content.
 *
 * Border presets:
 * - {@see Frame::STYLE_SINGLE} — ┌─┐ │ └─┘
 * - {@see Frame::STYLE_DOUBLE} — ╔═╗ ║ ╚═╝
 * - {@see Frame::STYLE_ROUNDED} — ╭─╮ │ ╰─╯
 * - {@see Frame::STYLE_HEAVY} — ┏━┓ ┃ ┗━┛
 */
class Frame
{
    public const STYLE_SINGLE = 'single';

    public const STYLE_DOUBLE = 'double';

    public const STYLE_ROUNDED = 'rounded';

    public const STYLE_HEAVY = 'heavy';

    public const ALIGN_LEFT = 'left';

    public const ALIGN_CENTER = 'center';

    public const ALIGN_RIGHT = 'right';

    /** @var array{tl:string,tr:string,bl:string,br:string,h:string,v:string} */
    private array $borderChars;

    private string $borderStyle = self::STYLE_SINGLE;

    private ?TermColor $borderColor = null;

    private ?TermColor $contentColor = null;

    private ?TermColor $titleColor = null;

    private ?TermColor $bottomTitleColor = null;

    private string $title = '';

    private string $titleAlignment = self::ALIGN_LEFT;

    private string $bottomTitle = '';

    private string $bottomTitleAlignment = self::ALIGN_LEFT;

    private int $paddingTop = 0;

    private int $paddingRight = 0;

    private int $paddingBottom = 0;

    private int $paddingLeft = 0;

    private string $content = '';

    private string $contentAlignment = self::ALIGN_LEFT;

    private int $minInnerWidth = 0;

    /**
     * @param array{tl?:string,tr?:string,bl?:string,br?:string,h?:string,v?:string}|null $override merged over {@see STYLE_SINGLE}
     */
    public function __construct(
        string $content = '',
        ?array $override = null,
    ) {
        $this->content = $content;
        $this->borderChars = $override !== null
            ? [...self::presetChars(self::STYLE_SINGLE), ...$override]
            : self::presetChars(self::STYLE_SINGLE);
    }

    /** @return array{tl:string,tr:string,bl:string,br:string,h:string,v:string} */
    public static function presetChars(string $style): array
    {
        return match ($style) {
            self::STYLE_SINGLE => ['tl' => '┌', 'tr' => '┐', 'bl' => '└', 'br' => '┘', 'h' => '─', 'v' => '│'],
            self::STYLE_DOUBLE => ['tl' => '╔', 'tr' => '╗', 'bl' => '╚', 'br' => '╝', 'h' => '═', 'v' => '║'],
            self::STYLE_ROUNDED => ['tl' => '╭', 'tr' => '╮', 'bl' => '╰', 'br' => '╯', 'h' => '─', 'v' => '│'],
            self::STYLE_HEAVY => ['tl' => '┏', 'tr' => '┓', 'bl' => '┗', 'br' => '┛', 'h' => '━', 'v' => '┃'],
            default => ['tl' => '┌', 'tr' => '┐', 'bl' => '└', 'br' => '┘', 'h' => '─', 'v' => '│'],
        };
    }

    public static function from(string $content): self
    {
        return new self($content);
    }

    public function borderStyle(string $style): self
    {
        $this->borderStyle = $style;
        $this->borderChars = self::presetChars($style);

        return $this;
    }

    /**
     * Fully custom border; overrides {@see borderStyle()} until changed again.
     *
     * @param array{tl:string,tr:string,bl:string,br:string,h:string,v:string} $chars
     */
    public function borderChars(array $chars): self
    {
        $this->borderChars = $chars;

        return $this;
    }

    public function borderColor(?TermColor $color): self
    {
        $this->borderColor = $color;

        return $this;
    }

    public function contentColor(?TermColor $color): self
    {
        $this->contentColor = $color;

        return $this;
    }

    /**
     * Color for the top embedded title segment; defaults to border color when null at render time.
     */
    public function titleColor(?TermColor $color): self
    {
        $this->titleColor = $color;

        return $this;
    }

    /**
     * Color for the bottom embedded title segment; defaults to border color when null at render time.
     */
    public function bottomTitleColor(?TermColor $color): self
    {
        $this->bottomTitleColor = $color;

        return $this;
    }

    public function title(string $title, string $alignment = self::ALIGN_LEFT): self
    {
        $this->title = $title;
        $this->titleAlignment = $alignment;

        return $this;
    }

    public function bottomTitle(string $title, string $alignment = self::ALIGN_LEFT): self
    {
        $this->bottomTitle = $title;
        $this->bottomTitleAlignment = $alignment;

        return $this;
    }

    /**
     * Padding inside the frame, on all sides.
     */
    public function padding(int $all): self
    {
        $p = max(0, $all);
        $this->paddingTop = $this->paddingRight = $this->paddingBottom = $this->paddingLeft = $p;

        return $this;
    }

    public function paddingVertical(int $vertical): self
    {
        $p = max(0, $vertical);
        $this->paddingTop = $this->paddingBottom = $p;

        return $this;
    }

    public function paddingHorizontal(int $horizontal): self
    {
        $p = max(0, $horizontal);
        $this->paddingRight = $this->paddingLeft = $p;

        return $this;
    }

    public function paddingRect(int $top, int $right, int $bottom, int $left): self
    {
        $this->paddingTop = max(0, $top);
        $this->paddingRight = max(0, $right);
        $this->paddingBottom = max(0, $bottom);
        $this->paddingLeft = max(0, $left);

        return $this;
    }

    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function contentAlignment(string $alignment): self
    {
        $this->contentAlignment = $alignment;

        return $this;
    }

    /**
     * Minimum width between the two vertical rules (excluding side borders).
     */
    public function minInnerWidth(int $width): self
    {
        $this->minInnerWidth = max(0, $width);

        return $this;
    }

    public function render(): string
    {
        $v = $this->borderChars['v'];
        $normalized = $this->normalizeNewlines($this->content);
        $lines = $normalized === '' ? [] : explode("\n", $normalized);

        $innerTextWidth = 0;
        foreach ($lines as $line) {
            $innerTextWidth = max($innerTextWidth, $this->displayWidth($line));
        }

        $padH = $this->paddingLeft + $this->paddingRight;
        $innerFromContent = $innerTextWidth + $padH;

        $topNeed = $this->title === '' ? 0 : $this->horizontalTitleNeed($this->title, $this->titleAlignment);
        $bottomNeed = $this->bottomTitle === '' ? 0 : $this->horizontalTitleNeed($this->bottomTitle, $this->bottomTitleAlignment);

        $innerWidth = max($this->minInnerWidth, $innerFromContent, $topNeed, $bottomNeed);
        if ($innerWidth === 0) {
            $innerWidth = 1;
        }

        $bodyTextWidth = max(0, $innerWidth - $padH);

        $out = [];
        $out[] = $this->fmtTopOrBottom(
            $this->borderChars['tl'],
            $this->borderChars['tr'],
            $this->title,
            $this->titleAlignment,
            $innerWidth,
            $this->titleColor,
        );

        for ($i = 0; $i < $this->paddingTop; $i++) {
            $out[] = $this->fmtRow($v, str_repeat(' ', $innerWidth), $v);
        }

        foreach ($lines as $line) {
            $cell = $this->alignLine($line, $bodyTextWidth, $this->contentAlignment);
            $padded = str_repeat(' ', $this->paddingLeft) . $cell . str_repeat(' ', $this->paddingRight);
            $out[] = $this->fmtRow($v, $padded, $v);
        }

        if ($lines === [] && ($this->paddingTop > 0 || $this->paddingBottom > 0)) {
            /* already added top padding; if no lines and no padding, skip body */
        } elseif ($lines === [] && $innerFromContent === 0 && $this->paddingTop === 0 && $this->paddingBottom === 0) {
            $out[] = $this->fmtRow($v, str_repeat(' ', $innerWidth), $v);
        }

        for ($i = 0; $i < $this->paddingBottom; $i++) {
            $out[] = $this->fmtRow($v, str_repeat(' ', $innerWidth), $v);
        }

        $out[] = $this->fmtTopOrBottom(
            $this->borderChars['bl'],
            $this->borderChars['br'],
            $this->bottomTitle,
            $this->bottomTitleAlignment,
            $innerWidth,
            $this->bottomTitleColor,
        );

        return implode("\n", $out);
    }

    public function __toString(): string
    {
        return $this->render();
    }

    private function horizontalTitleNeed(string $title, string $alignment): int
    {
        if ($title === '') {
            return 0;
        }

        $tLen = $this->strLen($title);
        $hLen = $this->strLen($this->borderChars['h']);

        return match ($alignment) {
            self::ALIGN_CENTER => 2 + $tLen,
            self::ALIGN_LEFT, self::ALIGN_RIGHT => $hLen + 2 + $tLen,
            default => $hLen + 2 + $tLen,
        };
    }

    /**
     * @return string Full top or bottom border line (corners + middle).
     */
    private function fmtTopOrBottom(
        string $leftCorner,
        string $rightCorner,
        string $title,
        string $alignment,
        int $innerWidth,
        ?TermColor $titleColor,
    ): string {
        $h = $this->borderChars['h'];
        if ($title === '') {
            $middle = str_repeat($h, $innerWidth);

            return $this->paintBorder($leftCorner) . $this->paintBorder($middle) . $this->paintBorder($rightCorner);
        }

        [$middle, $span] = $this->buildHorizontalWithTitle($h, $title, $alignment, $innerWidth);
        $titleCol = $titleColor ?? $this->borderColor;

        $left = $this->paintBorder($leftCorner);
        $mid = $titleCol === null || $span === null
            ? $this->paintBorder($middle)
            : $this->paintBorderWithTitleSlice($middle, $span, $titleCol);
        $right = $this->paintBorder($rightCorner);

        return $left . $mid . $right;
    }

    /**
     * @param array{start:int,len:int} $span Character offsets and length (UTF-8 code points via mb_* when available).
     */
    private function paintBorderWithTitleSlice(string $fullMiddle, array $span, TermColor $titleColor): string
    {
        $start = $span['start'];
        $len = $span['len'];
        if ($len <= 0) {
            return $this->paintBorder($fullMiddle);
        }

        $before = $this->strSliceLen($fullMiddle, 0, $start);
        $core = $this->strSliceLen($fullMiddle, $start, $len);
        $after = $this->strSliceFrom($fullMiddle, $start + $len);

        if ($this->borderColor === null) {
            return $before . $titleColor->fmt($core) . $after;
        }

        return $this->paintBorder($before)
            . $titleColor->fmt($core)
            . $this->paintBorder($after);
    }

    /**
     * @return array{0:string,1:array{start:int,len:int}|null}
     */
    private function buildHorizontalWithTitle(string $h, string $title, string $alignment, int $innerWidth): array
    {
        $cap = $this->maxTitleCharsInInner($h, $alignment, $innerWidth);
        $embedded = $this->truncateTitleToFit($title, $cap);

        return match ($alignment) {
            self::ALIGN_LEFT => $this->fitHorizontalLeft($h, $embedded, $innerWidth),
            self::ALIGN_CENTER => $this->fitHorizontalCenter($h, $embedded, $innerWidth),
            self::ALIGN_RIGHT => $this->fitHorizontalRight($h, $embedded, $innerWidth),
            default => $this->fitHorizontalLeft($h, $embedded, $innerWidth),
        };
    }

    private function maxTitleCharsInInner(string $h, string $alignment, int $innerWidth): int
    {
        $hLen = $this->strLen($h);

        return (int) match ($alignment) {
            self::ALIGN_CENTER => max(0, $innerWidth - 2),
            default => max(0, $innerWidth - $hLen - 2),
        };
    }

    private function truncateTitleToFit(string $title, int $cap): string
    {
        if ($cap <= 0 || $title === '') {
            return '';
        }

        if ($this->strLen($title) <= $cap) {
            return $title;
        }

        if ($cap === 1) {
            return '…';
        }

        return $this->strSliceLen($title, 0, $cap - 1) . '…';
    }

    /**
     * @return array{0:string,1:array{start:int,len:int}|null}
     */
    private function fitHorizontalLeft(string $h, string $title, int $innerWidth): array
    {
        $hLen = $this->strLen($h);
        $prefix = $h . ' ';
        $need = $this->strLen($prefix) + $this->strLen($title) + 1;
        $fill = max(0, $innerWidth - $need);
        $line = $prefix . $title . ' ' . str_repeat($h, $fill);
        $start = $hLen + 1;
        $len = $this->strLen($title);

        return [$line, $len > 0 ? ['start' => $start, 'len' => $len] : null];
    }

    /**
     * @return array{0:string,1:array{start:int,len:int}|null}
     */
    private function fitHorizontalCenter(string $h, string $title, int $innerWidth): array
    {
        $hLen = $this->strLen($h);
        $middle = ' ' . $title . ' ';
        $mLen = $this->strLen($middle);
        $dTotal = $innerWidth - $mLen;
        if ($dTotal < 0) {
            return [$this->strSliceLen($middle, 0, $innerWidth), null];
        }

        $left = intdiv($dTotal, 2);
        $right = $dTotal - $left;
        $line = str_repeat($h, $left) . $middle . str_repeat($h, $right);
        $start = $left * $hLen + 1;
        $len = $this->strLen($title);

        return [$line, $len > 0 ? ['start' => $start, 'len' => $len] : null];
    }

    /**
     * @return array{0:string,1:array{start:int,len:int}|null}
     */
    private function fitHorizontalRight(string $h, string $title, int $innerWidth): array
    {
        $hLen = $this->strLen($h);
        $suffix = ' ' . $title . ' ' . $h;
        $sLen = $this->strLen($suffix);
        $fill = max(0, $innerWidth - $sLen);
        $line = str_repeat($h, $fill) . $suffix;
        $start = $fill * $hLen + 1;
        $len = $this->strLen($title);

        return [$line, $len > 0 ? ['start' => $start, 'len' => $len] : null];
    }

    private function fmtRow(string $leftV, string $inner, string $rightV): string
    {
        $edgeL = $this->paintBorder($leftV);
        $edgeR = $this->paintBorder($rightV);
        $mid = $this->contentColor === null ? $inner : $this->contentColor->fmt($inner);

        return $edgeL . $mid . $edgeR;
    }

    private function paintBorder(string $chunk): string
    {
        if ($chunk === '' || $this->borderColor === null) {
            return $chunk;
        }

        return $this->borderColor->fmt($chunk);
    }

    private function alignLine(string $line, int $width, string $alignment): string
    {
        $len = $this->displayWidth($line);
        if ($len < $width) {
            $pad = $width - $len;

            return match ($alignment) {
                self::ALIGN_LEFT => $line . str_repeat(' ', $pad),
                self::ALIGN_RIGHT => str_repeat(' ', $pad) . $line,
                self::ALIGN_CENTER => str_repeat(' ', intdiv($pad, 2)) . $line . str_repeat(' ', intdiv($pad + 1, 2)),
                default => $line . str_repeat(' ', $pad),
            };
        }

        if ($len > $width) {
            $plain = $this->stripAnsiSgr($line);

            return $this->strSlice($plain, 0, $width);
        }

        return $line;
    }

    private function strLen(string $s): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($s, 'UTF-8');
        }

        return strlen($s);
    }

    /** Retire les séquences CSI SGR (`\e[…m`) pour mesurer la largeur à l’écran. */
    private function stripAnsiSgr(string $s): string
    {
        $out = preg_replace("/\x1b\[[0-9;]*m/", '', $s);

        return $out ?? $s;
    }

    private function displayWidth(string $s): int
    {
        return $this->strLen($this->stripAnsiSgr($s));
    }

    private function strSlice(string $s, int $start, int $length): string
    {
        return $this->strSliceLen($s, $start, $length);
    }

    private function strSliceLen(string $s, int $start, int $length): string
    {
        if ($length <= 0) {
            return '';
        }

        if (function_exists('mb_substr')) {
            return mb_substr($s, $start, $length, 'UTF-8');
        }

        return substr($s, $start, $length);
    }

    private function strSliceFrom(string $s, int $start): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($s, $start, null, 'UTF-8') ?? '';
        }

        return $start >= strlen($s) ? '' : substr($s, $start);
    }

    private function normalizeNewlines(string $content): string
    {
        return str_replace(["\r\n", "\r"], "\n", $content);
    }
}
