<?php

declare(strict_types=1);

/**
 * Démo : {@see \Tivins\Tui\Throbber} (animation braille, message, %, durée).
 *
 * 1. Bloc multi-lignes (défilement) : deux indicateurs indépendants ; la durée `(m:ss)` ne change
 *    qu’après environ une seconde écoulée (pause 250 ms entre images).
 * 2. Une seule ligne : {@see \Tivins\Tui\Terminal::lineOverwritePrefix()} + rendu + `flush()`.
 * 3. Deux lignes : depuis la fin de la 2ᵉ ligne, {@see \Tivins\Tui\Terminal::cursorPreviousLine(1)}
 *    (équivalent `\e[1A\r`, début de ligne supérieure), puis {@see \Tivins\Tui\Terminal::eraseLine()} sur chaque ligne — pas `cursorUp(2)` si un titre est au-dessus du bloc.
 *
 * Exécuter : php examples/throbber.php
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Tivins\Tui\Terminal;
use Tivins\Tui\Throbber;

echo "— Plusieurs lignes (défilement) —" . PHP_EOL;

$think = (new Throbber())
    ->message('Thinking...')
    ->template('{spinner} {message}{trail}')
    ->start();

$dl = (new Throbber())
    ->message('downloading')
    ->percent(45.0)
    ->template('{spinner} {message}{trail}')
    ->start();
/*
foreach (range(1, 8) as $_) {
    echo $think->render() . PHP_EOL;
    echo $dl->render() . PHP_EOL;
    echo str_repeat('-', 40) . PHP_EOL;
    $think->tick();
    $dl->tick();
    usleep(250_000);
}
*/
echo PHP_EOL . "— Une ligne (réécriture en place) —" . PHP_EOL;

$inline = (new Throbber())
    ->message('Working')
    ->percent(33.0)
    ->template('{spinner} {message}{trail}')
    ->start();

$lineSteps = 48;
foreach (range(1, $lineSteps) as $_) {
    echo Terminal::lineOverwritePrefix() . $inline->render();
    flush();
    $inline->tick();
    usleep(90_000);
}
echo PHP_EOL;

echo PHP_EOL . "— Deux lignes (réécriture en place) —" . PHP_EOL;

$row1 = (new Throbber())
    ->message('Indexing…')
    ->template('{spinner} {message}{trail}')
    ->start();
$row2 = (new Throbber())
    ->message('Fetching')
    ->percent(62.0)
    ->template('{spinner} {message}{trail}')
    ->start();

echo $row1->render() . "\n";
echo $row2->render();
flush();

$twoLineSteps = 20;
foreach (range(1, $twoLineSteps) as $_) {
    Terminal::cursorPreviousLine(1);
    Terminal::eraseLine();
    echo $row1->render() . "\n";
    Terminal::eraseLine();
    echo $row2->render();
    flush();
    $row1->tick();
    $row2->tick();
    usleep(100_000);
}
echo PHP_EOL;
