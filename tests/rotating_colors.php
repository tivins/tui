<?php

declare(strict_types=1);

/**
 * Vérifications pour RotatingColors (sans phpunit).
 *
 * Exécuter : php tests/rotating_colors.php
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Tivins\Tui\Ansi;
use Tivins\Tui\RotatingColors;
use Tivins\Tui\TermColor;
use Tivins\Tui\Throbber;

function fail(string $msg): void
{
    fwrite(STDERR, $msg . PHP_EOL);
    exit(1);
}

$pal = RotatingColors::defaultPalette();
if (\count($pal) !== 7) {
    fail('defaultPalette doit avoir 7 entrées');
}

$ab0 = Ansi::fmtForeground256(237, 'a') . Ansi::fmtForeground256(240, 'b');
if (RotatingColors::render('ab', 0) !== $ab0) {
    fail('ab offset 0 : ' . RotatingColors::render('ab', 0));
}

$ab1 = Ansi::fmtForeground256(240, 'a') . Ansi::fmtForeground256(244, 'b');
if (RotatingColors::render('ab', 1) !== $ab1) {
    fail('ab offset 1');
}

$abm1 = Ansi::fmtForeground256(237, 'a') . Ansi::fmtForeground256(237, 'b');
if (RotatingColors::render('ab', -1) !== $abm1) {
    fail('ab offset -1');
}

if (RotatingColors::render('', 0) !== '') {
    fail('chaîne vide');
}

if (Ansi::displayWidth(RotatingColors::render('é', 0)) !== 1) {
    fail('largeur UTF-8');
}

try {
    RotatingColors::render('x', 0, []);
    fail('palette vide aurait dû lever');
} catch (\InvalidArgumentException $e) {
    // ok
}

try {
    RotatingColors::render('x', 0, [999]);
    fail('code 256 hors plage');
} catch (\InvalidArgumentException $e) {
    // ok
}

$mixed = RotatingColors::render('xy', 0, [TermColor::Red, TermColor::Blue]);
if ($mixed !== TermColor::Red->fmt('x') . TermColor::Blue->fmt('y')) {
    fail('palette mixte TermColor');
}

$thr = (new Throbber())->message('Hi')->template('{rotating_message}');
$r0 = $thr->render();
$thr->tick();
$r1 = $thr->render();
if ($r0 === $r1) {
    fail('{rotating_message} devrait changer après tick');
}
if (!str_contains($r0, 'H') || !str_contains($r0, 'i')) {
    fail('{rotating_message} contenu : ' . $r0);
}
if ($thr->rotatingColorOffset() !== 1) {
    fail('rotatingColorOffset après 1 tick');
}

$long = new Throbber()->style(Throbber::STYLE_BRAILLE);
for ($i = 0; $i < 10; $i++) {
    $long->tick();
}
if ($long->frameIndex() !== 0) {
    fail('après 10 ticks braille frameIndex doit repasser à 0');
}
if ($long->rotatingColorOffset() !== 10) {
    fail('rotatingColorOffset doit croître sans sauter au reboot du spinner');
}

echo "ok\n";
