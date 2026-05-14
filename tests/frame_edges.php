<?php

declare(strict_types=1);

/**
 * Assertions légères pour les cas limites de Frame (sans phpunit).
 *
 * Exécuter : php tests/frame_edges.php
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Tivins\Tui\Frame;
use Tivins\Tui\TermColor;

function fail(string $msg): void
{
    fwrite(STDERR, $msg . PHP_EOL);
    exit(1);
}

/** @return list<int> */
function visibleWidths(string $rendered): array
{
    $lines = explode("\n", $rendered);
    $out = [];

    foreach ($lines as $line) {
        $plain = preg_replace("/\x1b\[[0-9;]*m/", '', $line) ?? $line;
        $plain = str_replace("\r", '', $plain);
        $out[] = function_exists('mb_strlen') ? mb_strlen($plain, 'UTF-8') : strlen($plain);
    }
    return $out;
}

$f = Frame::from("a\r\nb\rc")->borderColor(null);
if (!str_contains($f->render(), 'b')) {
    fail('newline normalization should preserve lines as a, b, c');
}

$f2 = (new Frame('x'))->paddingRect(-1, -1, 0, 0)->render();
if (!str_contains($f2, 'x')) {
    fail('negative padding should clamp and still render');
}

$ambig = Frame::from('y')
    ->title('──')
    ->titleColor(TermColor::Yellow)
    ->borderColor(TermColor::Cyan)
    ->render();

if (!preg_match('/\x1b\[33m/', $ambig)) {
    fail('ambiguous dash title should still apply title color (slice-based paint)');
}

$long = Frame::from('')
    ->title(str_repeat('W', 80))
    ->minInnerWidth(20)
    ->borderColor(null)
    ->render();

if (count(explode("\n", $long)) < 2) {
    fail('long title frame should render');
}

$w = visibleWidths($long);
if ($w === []) {
    fail('expected at least one line');
}

$first = $w[0];
foreach ($w as $i => $len) {
    if ($len !== $first) {
        fail("line $i width $len !== $first (consistent box width)");
    }
}

$empty = Frame::from('')->borderStyle(Frame::STYLE_DOUBLE)->render();
if (!str_contains($empty, '═')) {
    fail('empty frame should still draw horizontal rule');
}

echo "frame_edges: OK\n";
