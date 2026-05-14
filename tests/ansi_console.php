<?php

declare(strict_types=1);

/**
 * Vérifications pour {@see \Tivins\Tui\Ansi} et {@see \Tivins\Tui\Console} (sans phpunit).
 *
 * Exécuter : php tests/ansi_console.php
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Tivins\Tui\Ansi;
use Tivins\Tui\Console;

function fail(string $msg): void
{
    fwrite(STDERR, $msg . PHP_EOL);
    exit(1);
}

if (Ansi::stripSgr("\e[31mab\e[0m") !== 'ab') {
    fail('Ansi::stripSgr devrait retirer les SGR et garder le texte');
}

if (Ansi::displayWidth("\e[92mhello\e[0m") !== 5) {
    fail('Ansi::displayWidth doit ignorer les séquences pour compter les colonnes');
}

if (function_exists('mb_strwidth') && Ansi::displayWidth("\e[92m🌿\e[0m") !== 2) {
    fail('Ansi::displayWidth emoji width (2 cols) with mb_strwidth');
}

$f256 = Ansi::fmtForeground256(244, 'z');
if (!str_starts_with($f256, "\e[38;5;244m") || !str_ends_with($f256, "\e[0m") || !str_contains($f256, 'z')) {
    fail('Ansi::fmtForeground256 244 + z');
}
if (Ansi::stripSgr($f256) !== 'z') {
    fail('stripSgr après fmtForeground256');
}
try {
    Ansi::fmtForeground256(-1, 'x');
    fail('fmtForeground256 -1');
} catch (\InvalidArgumentException $e) {
    // ok
}
try {
    Ansi::fmtForeground256(256, 'x');
    fail('fmtForeground256 256');
} catch (\InvalidArgumentException $e) {
    // ok
}

if (!is_bool(Console::stdinIsTty())) {
    fail('Console::stdinIsTty doit retourner un booléen');
}

$h = fopen('php://memory', 'r+');
if ($h === false) {
    fail('php://memory indisponible');
}
fwrite($h, "one line\n");
rewind($h);
$ln = Console::readLine($h);
fclose($h);
if ($ln !== "one line\n") {
    fail('Console::readLine doit lire jusqu’au saut de ligne');
}

echo "ansi_console tests OK\n";
