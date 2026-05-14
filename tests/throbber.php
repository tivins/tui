<?php

declare(strict_types=1);

/**
 * Vérifications pour Throbber (sans phpunit).
 *
 * Exécuter : php tests/throbber.php
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Tivins\Tui\Throbber;

function fail(string $msg): void
{
    fwrite(STDERR, $msg . PHP_EOL);
    exit(1);
}

if (Throbber::formatDuration(24.7) !== '0:24') {
    fail('formatDuration 24s => 0:24');
}
if (Throbber::formatDuration(65.0) !== '1:05') {
    fail('formatDuration 65s => 1:05');
}
if (Throbber::formatDuration(3665.0) !== '1:01:05') {
    fail('formatDuration > 1h');
}

$t0 = (new Throbber())->style(Throbber::STYLE_PIPE)->message('x');
$t0->tick()->tick();
if ($t0->spinner() !== '-') {
    fail('STYLE_PIPE tick avance les images');
}

$b = new Throbber();
$b->template('{spinner}{message}{trail}')->message('OK')->start();
$rb = new \ReflectionClass($b);
$pb = $rb->getProperty('startedAt');
$pb->setAccessible(true);
$pb->setValue($b, microtime(true) - 13.0);
$b->percent(45.0);
$rend = $b->render();
if (!str_contains($rend, '45%') || !str_contains($rend, '(0:13)')) {
    fail('render inclut pourcentage et durée : ' . $rend);
}

$c = new Throbber();
$c->message('Thinking...')->template('{spinner} {message}{trail}')->start();
$rc = new \ReflectionClass($c);
$pc = $rc->getProperty('startedAt');
$pc->setAccessible(true);
$pc->setValue($c, microtime(true) - 24.0);
$tline = $c->render();
if (!str_contains($tline, 'Thinking...') || !str_contains($tline, '(0:24)')) {
    fail('ligne Thinking + elapsed : ' . $tline);
}

Throbber::registerStyle('one', ['●']);
$one = (new Throbber())->style('one');
if ($one->spinner() !== '●') {
    fail('registerStyle');
}
try {
    $one->style('nope');
    fail('style inconnu aurait dû lever');
} catch (\InvalidArgumentException $e) {
    // ok
}

try {
    Throbber::registerStyle('empty', []);
    fail('style vide');
} catch (\InvalidArgumentException $e) {
    // ok
}

// ── {bar} ────────────────────────────────────────────────────────────────────

// Sans percent : {bar} = chaîne vide
$noBar = (new Throbber())->template('{bar}');
if ($noBar->render() !== '') {
    fail('{bar} sans percent devrait être vide, obtenu : ' . $noBar->render());
}

// 0 % → tout vide
$b0 = (new Throbber())->percent(0.0)->template('{bar}')->barWidth(10);
if ($b0->render() !== '░░░░░░░░░░') {
    fail('{bar} 0% 10 colonnes : ' . $b0->render());
}

// 100 % → tout rempli
$b100 = (new Throbber())->percent(100.0)->template('{bar}')->barWidth(10);
if ($b100->render() !== '██████████') {
    fail('{bar} 100% 10 colonnes : ' . $b100->render());
}

// 50 % → moitié remplie (arrondi)
$b50 = (new Throbber())->percent(50.0)->template('{bar}')->barWidth(10);
if ($b50->render() !== '█████░░░░░') {
    fail('{bar} 50% 10 colonnes : ' . $b50->render());
}

// barWidth personnalisée
$bw = (new Throbber())->percent(25.0)->template('{bar}')->barWidth(4);
if ($bw->render() !== '█░░░') {
    fail('{bar} 25% 4 colonnes : ' . $bw->render());
}

// ── Nouveaux styles ──────────────────────────────────────────────────────────

$dots = (new Throbber())->style(Throbber::STYLE_DOTS);
if (count($dots->framesForCurrentStyle()) !== 8) {
    fail('STYLE_DOTS doit avoir 8 images');
}

$line = (new Throbber())->style(Throbber::STYLE_LINE);
if (count($line->framesForCurrentStyle()) !== 14) {
    fail('STYLE_LINE doit avoir 14 images');
}

echo "ok\n";
