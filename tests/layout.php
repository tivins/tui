<?php

declare(strict_types=1);

/**
 * Exécuter : php tests/layout.php
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Tivins\Tui\Ansi;
use Tivins\Tui\Layout;
use Tivins\Tui\TermColor;

function fail(string $msg): void
{
    fwrite(STDERR, $msg . PHP_EOL);
    exit(1);
}

$row = Layout::horizontal(['a', 'bb'], 2);
if ($row !== 'a  bb') {
    fail('single-line horizontal: expected padded columns with gap');
}

$col = Layout::horizontal(["a\nc", 'x'], 1);
$lines = explode("\n", $col);
if (count($lines) !== 2) {
    fail('expected 2 lines for unequal height blocks');
}
if (Ansi::displayWidth($lines[0]) !== Ansi::displayWidth($lines[1])) {
    fail('rows should share the same total display width');
}

$ansi = Layout::horizontal([
    TermColor::Green->fmt('ok'),
    'x',
], 0);
if (!str_contains($ansi, 'ok')) {
    fail('colored block should be preserved');
}
if (Ansi::displayWidth(explode("\n", $ansi)[0]) !== Ansi::displayWidth('ok') + Ansi::displayWidth('x')) {
    fail('display widths should compose: colored + plain');
}

fwrite(STDOUT, "layout: ok\n");
