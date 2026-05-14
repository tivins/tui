<?php

declare(strict_types=1);

/**
 * Démo des capacités principales : {@see \Tivins\Tui\Terminal} (écran alternatif,
 * dimensions), {@see \Tivins\Tui\AsciiText} (bannière type FIGlet), {@see \Tivins\Tui\Frame}
 * (styles, titres, couleurs ANSI).
 *
 * Exécuter : php examples/showcase.php
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Tivins\Tui\Ansi;
use Tivins\Tui\AsciiText;
use Tivins\Tui\Console;
use Tivins\Tui\Frame;
use Tivins\Tui\Terminal;
use Tivins\Tui\TermColor;

/** Centre une ligne dans la console (largeur affichée sans séquences SGR). */
function showcase_center_line(string $line, int $cols): string
{
    $w = Ansi::displayWidth($line);
    $pad = max(0, intdiv($cols - $w, 2));

    return str_repeat(' ', $pad) . $line;
}

/** Centre un bloc ligne par ligne (bannières où chaque rangée a une largeur différente). */
function showcase_center_block(string $block, int $cols): string
{
    $lines = explode("\n", $block);

    return implode("\n", array_map(static fn (string $l): string => showcase_center_line($l, $cols), $lines));
}

/**
 * Centre un cadre ou un bloc à largeur fixe : toutes les lignes sont alignées comme un seul rectangle.
 */
function showcase_center_uniform_block(string $block, int $cols): string
{
    $lines = explode("\n", $block);
    if ($lines === []) {
        return '';
    }

    $widths = array_map(static fn (string $l): int => Ansi::displayWidth($l), $lines);
    $maxW = max($widths);
    $leftPad = max(0, intdiv($cols - $maxW, 2));
    $prefix = str_repeat(' ', $leftPad);
    $out = [];

    foreach ($lines as $i => $line) {
        $padRight = str_repeat(' ', max(0, $maxW - $widths[$i]));
        $out[] = $prefix . $line . $padRight;
    }

    return implode("\n", $out);
}

$size = Terminal::screenSize();
$cols = $size['cols'] ?? 80;
$rows = $size['rows'] ?? 24;

$bannerPlain = AsciiText::toString(AsciiText::get('tui'));
$banner = showcase_center_block(
    TermColor::LightCyan->fmt($bannerPlain),
    $cols,
);
$tagline = showcase_center_line(
    TermColor::Gray->fmt('php · cadres unicode · couleurs ansi · ascii art'),
    $cols,
);

$statsLines = [
    'dimensions : ' . TermColor::LightYellow->fmt((string) $rows) . ' × ' . TermColor::LightYellow->fmt((string) $cols),
    'tampon : ' . TermColor::LightGreen->fmt('alternatif') . ' (plein écran)',
    'API : ' . TermColor::White->fmt('Terminal') . ' · ' . TermColor::White->fmt('Frame') . ' · ' . TermColor::White->fmt('AsciiText'),
];
$statsFrame = (string) Frame::from(implode("\n", $statsLines))
    ->borderStyle(Frame::STYLE_ROUNDED)
    ->borderColor(TermColor::Cyan)
    ->contentColor(TermColor::LightGray)
    ->title('terminal', Frame::ALIGN_LEFT)
    ->titleColor(TermColor::LightYellow)
    ->bottomTitle($rows >= 28 ? 'screenSize()' : 'size()', Frame::ALIGN_RIGHT)
    ->paddingVertical(1)
    ->paddingHorizontal(1);

/* largeur interne commune : le mot le plus long est « rounded » (7 colonnes). */
$framesDemoInnerMin = 7;
$framesDemo = implode("\n", [
    Frame::from('single')->borderStyle(Frame::STYLE_SINGLE)->borderColor(TermColor::Blue)->minInnerWidth($framesDemoInnerMin)->render(),
    Frame::from('double')->borderStyle(Frame::STYLE_DOUBLE)->borderColor(TermColor::Magenta)->minInnerWidth($framesDemoInnerMin)->render(),
    Frame::from('rounded')->borderStyle(Frame::STYLE_ROUNDED)->borderColor(TermColor::Green)->minInnerWidth($framesDemoInnerMin)->render(),
    Frame::from('heavy')->borderStyle(Frame::STYLE_HEAVY)->borderColor(TermColor::Red)->minInnerWidth($framesDemoInnerMin)->render(),
]);

$framesBlock = (string) Frame::from($framesDemo)
    ->borderStyle(Frame::STYLE_DOUBLE)
    ->borderColor(TermColor::LightBlue)
    ->contentColor(null)
    ->title('styles de bordure', Frame::ALIGN_CENTER)
    ->titleColor(TermColor::LightMagenta)
    ->paddingVertical(1);

$asciiHint = (string) Frame::from(
    "jeu de glyphes « future smooth » (3 lignes)\n"
        . 'charset : ' . TermColor::LightGreen->fmt(AsciiText::CHARSET),
)
    ->borderStyle(Frame::STYLE_HEAVY)
    ->borderColor(TermColor::Yellow)
    ->contentColor(TermColor::White)
    ->title('ascii text', Frame::ALIGN_CENTER)
    ->bottomTitle('AsciiText::get() + toString()', Frame::ALIGN_LEFT)
    ->padding(1);

$cookPlain = AsciiText::toString(AsciiText::get('hello 2026!'));
$cookBanner = showcase_center_block(TermColor::LightGreen->fmt($cookPlain), $cols);

$stack = implode("\n\n", [
    $banner,
    $tagline,
    '',
    showcase_center_uniform_block($statsFrame, $cols),
    showcase_center_uniform_block($framesBlock, $cols),
    showcase_center_uniform_block($asciiHint, $cols),
    '',
    $cookBanner,
]);

$linesInStack = substr_count($stack, "\n") + 1;
$extraNl = max(0, $rows - $linesInStack - 2);
$bottomHint = showcase_center_line(
    TermColor::Gray->fmt(Console::stdinIsTty() ? 'entrée pour quitter' : '(stdin non interactif — fin immédiate)'),
    $cols,
);

Terminal::enterAlternateScreen();
Terminal::cursorHide();
Terminal::clear();

try {
    echo $stack;
    echo str_repeat("\n", $extraNl);
    echo $bottomHint;

    if (Console::stdinIsTty()) {
        Console::readLine();
    }
} finally {
    Terminal::cursorShow();
    Terminal::leaveAlternateScreen();
}
