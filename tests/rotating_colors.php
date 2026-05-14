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
if (\count($pal) !== 5) {
    fail('defaultPalette doit avoir 5 couleurs');
}

$ab0 = TermColor::Gray->fmt('a') . TermColor::LightGray->fmt('b');
if (RotatingColors::render('ab', 0) !== $ab0) {
    fail('ab offset 0 : ' . RotatingColors::render('ab', 0));
}

$ab1 = TermColor::LightGray->fmt('a') . TermColor::White->fmt('b');
if (RotatingColors::render('ab', 1) !== $ab1) {
    fail('ab offset 1');
}

$abm1 = TermColor::Gray->fmt('a') . TermColor::Gray->fmt('b');
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

echo "ok\n";
