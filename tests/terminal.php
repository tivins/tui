<?php

declare(strict_types=1);

/**
 * Vérifications pour Terminal (sans phpunit).
 *
 * Exécuter : php tests/terminal.php
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Tivins\Tui\Terminal;

function fail(string $msg): void
{
    fwrite(STDERR, $msg . PHP_EOL);
    exit(1);
}

$cpr = "\e[24;80R";
$p = Terminal::parseCursorPositionResponse($cpr);
if ($p === null || $p['row'] !== 24 || $p['column'] !== 80) {
    fail('parseCursorPositionResponse devrait interpréter la CPR standard');
}

$prefix = "noise\e[3;5R";
$p2 = Terminal::parseCursorPositionResponse($prefix);
if ($p2 === null || $p2['row'] !== 3 || $p2['column'] !== 5) {
    fail('parseCursorPositionResponse devrait accepter un préfixe avant la CPR');
}

if (Terminal::parseCursorPositionResponse('') !== null) {
    fail('réponse vide => null');
}

$size = Terminal::screenSize();
if (Terminal::lineOverwritePrefix() !== "\r\e[2K") {
    fail('lineOverwritePrefix devrait être \\r + effacement de ligne CSI');
}

if ($size !== null) {
    if ($size['rows'] <= 0 || $size['cols'] <= 0) {
        fail('screenSize doit retourner des dimensions positives ou null');
    }
}

echo "terminal tests OK\n";
