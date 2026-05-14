<?php

declare(strict_types=1);

/**
 * Exécuter : php tests/progress_bar.php
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Tivins\Tui\Ansi;
use Tivins\Tui\ProgressBar;

function fail(string $msg): void
{
    fwrite(STDERR, $msg . PHP_EOL);
    exit(1);
}

$b = ProgressBar::render(0.0, 10);
if ($b !== str_repeat('░', 10)) {
    fail('0% should be all empty');
}

$b = ProgressBar::render(100.0, 10);
if ($b !== str_repeat('█', 10)) {
    fail('100% should be all filled');
}

if (Ansi::displayWidth(ProgressBar::render(50.0, 12)) !== 12) {
    fail('bar display width should match requested width');
}

if (ProgressBar::render(-5.0, 5) !== ProgressBar::render(0.0, 5)) {
    fail('negative percent clamps to 0');
}

if (ProgressBar::render(200.0, 5) !== ProgressBar::render(100.0, 5)) {
    fail('percent above 100 clamps');
}

fwrite(STDOUT, "progress_bar: ok\n");
