<?php

declare(strict_types=1);

/**
 * Exécuter : php tests/text_viewport.php
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Tivins\Tui\Ansi;
use Tivins\Tui\TextViewport;

function fail(string $msg): void
{
    fwrite(STDERR, $msg . PHP_EOL);
    exit(1);
}

$vp = new TextViewport(12);
$w = $vp->wrapPlainParagraph('hello world here');
if ($w !== ['hello world', 'here']) {
    fail('wrap two lines expected hello world / here');
}

$vpNarrow = new TextViewport(5);
$chunks = $vpNarrow->wrapPlainParagraph('abcdefghij');
if ($chunks !== ['abcde', 'fghij']) {
    fail('long token split by display width (ASCII)');
}

$vpEmoji = new TextViewport(3);
if (function_exists('mb_strwidth')) {
    $e = $vpEmoji->wrapPlainParagraph('🌿🌿🌿');
    if ($e !== ['🌿', '🌿', '🌿']) {
        fail('emoji 2 cols each in width 3: expected 3 lines');
    }
}

$vpTab = new TextViewport(20, tabWidth: 4);
$tabbed = $vpTab->expandTabsInLine("a\tb");
if ($tabbed !== 'a   b') {
    fail('tab expand: a + 3 spaces + b');
}

$leader = new TextViewport(20);
$lines = $leader->wrapPlainParagraphWithLeader('one two three four five', '[MJ]');
if ($lines === []) {
    fail('leader wrap non-empty');
}
$first = $lines[0];
if (!str_starts_with($first, '[MJ] ')) {
    fail('first line should start with leader + space');
}
if (Ansi::displayWidth($first) > 20) {
    fail('first line should fit in viewport columns');
}
foreach (array_slice($lines, 1) as $cont) {
    $pad = TextViewport::continuationPadPlain('[MJ]');
    if (!str_starts_with($cont, $pad)) {
        fail('continuation should use continuationPadPlain');
    }
}

$multi = $leader->wrapPlain("a\n\nb");
if ($multi !== ['a', '', 'b']) {
    fail('wrapPlain preserves blank line segments');
}

$soft = new TextViewport(5, breakLongWords: false);
$wideLine = $soft->wrapPlainParagraph('hello wideeeee');
if (count($wideLine) !== 2 || $wideLine[1] !== 'wideeeee') {
    fail('breakLongWords false keeps overflowing token on its line');
}

try {
    new TextViewport(0);
    fail('columns 0 should throw');
} catch (\InvalidArgumentException $e) {
    // ok
}

echo "OK text_viewport\n";
