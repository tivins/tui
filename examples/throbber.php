<?php

declare(strict_types=1);

/**
 * Démo : {@see \Tivins\Tui\Throbber} (animation, barre de progression, couleurs, durée).
 *
 * 1. Défilement (multi-lignes) : sorties successives avec deux spinners colorés.
 * 2. Barre de progression (une ligne, réécriture en place, avancement 0 → 100 %).
 * 3. Deux tâches en parallèle (deux lignes, réécriture en place, curseur caché).
 *    Chaque frame est bufferisée en une seule chaîne avant émission pour minimiser
 *    les états partiels à l'écran. La vidange utilise {@see Common::flushOutput()}
 *    (`ob_flush()` uniquement si un tampon PHP est actif).
 *
 * Exécuter : php examples/throbber.php
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Tivins\Tui\Common;
use Tivins\Tui\TermColor;
use Tivins\Tui\Terminal;
use Tivins\Tui\Throbber;

if (in_array('--example1', $argv)) {
    // ─── Section 1 : défilement ──────────────────────────────────────────────────
    echo TermColor::Gray->fmt('─── défilement (multi-lignes) ───') . "\n\n";

    $think = (new Throbber())
        ->message('Thinking...')
        ->template(TermColor::Cyan->fmt('{spinner}') . ' {message}{trail}')
        ->start();

    $dl = (new Throbber())
        ->message('downloading')
        ->percent(45.0)
        ->style(Throbber::STYLE_DOTS)
        ->template(TermColor::Yellow->fmt('{spinner}') . ' {message}{trail}')
        ->start();

    foreach (range(1, 6) as $_) {
        echo $think->render() . "\n";
        echo $dl->render() . "\n";
        echo TermColor::Gray->fmt(str_repeat('─', 30)) . "\n";
        $think->tick();
        $dl->tick();
        usleep(200_000);
    }
}

if (in_array('--example2', $argv)) {
    // ─── Section 2 : barre de progression ────────────────────────────────────────
    echo "\n" . TermColor::Gray->fmt('─── barre de progression (une ligne) ───') . "\n\n";

    $bar = (new Throbber())
        ->message('Building')
        ->percent(0.0)
        ->template(
            TermColor::Green->fmt('{spinner}')
            . ' {message}  '
            . TermColor::LightGreen->fmt('{bar}')
            . '  '
            . TermColor::White->fmt('{percent}')
            . '  '
            . TermColor::Gray->fmt('{elapsed_paren}')
        )
        ->barWidth(20)
        ->start();

    foreach (range(0, 100) as $pct) {
        $bar->percent((float)$pct);
        echo Terminal::lineOverwritePrefix() . $bar->render();
        Common::flushOutput();
        $bar->tick();
        usleep(35_000);
    }
    echo "\n";
}

if (in_array('--example3', $argv)) {
    // ─── Section 3 : deux tâches en parallèle ────────────────────────────────────
    echo "\n" . TermColor::Gray->fmt('─── deux tâches en parallèle (deux lignes) ───') . "\n\n";

    $task1 = (new Throbber())
        ->message('Indexing ')
        ->percent(0.0)
        ->style(Throbber::STYLE_DOTS)
        ->template(
            TermColor::LightMagenta->fmt('{spinner}')
            . ' {message}  '
            . TermColor::LightMagenta->fmt('{bar}')
            . '  '
            . TermColor::White->fmt('{percent}')
        )
        ->barWidth(16)
        ->start();

    $task2 = (new Throbber())
        ->message('Fetching ')
        ->percent(0.0)
        ->template(
            TermColor::LightBlue->fmt('{spinner}')
            . ' {message}  '
            . TermColor::LightBlue->fmt('{bar}')
            . '  '
            . TermColor::White->fmt('{percent}')
            . '  '
            . TermColor::Gray->fmt('{elapsed_paren}')
        )
        ->barWidth(16)
        ->start();

    // Rendu initial (deux lignes déjà à l'écran avant la boucle)
    echo $task1->render() . "\n";
    echo $task2->render();
    Common::flushOutput();

    Terminal::cursorHide();
    try {
        foreach (range(1, 68) as $step) {
            $task1->percent(min(100.0, $step * 1.5));
            $task2->percent(min(100.0, $step * 1.0));

            // Tout le rendu est bufferisé en une seule chaîne avant l'écriture :
            // on remonte d'une ligne (CSI CPL), on efface et on réécrit les deux lignes
            // en un seul echo + flush → aucun état partiel visible à l'écran.
            $frame = "\e[1F"
                . "\e[2K" . $task1->render() . "\n"
                . "\e[2K" . $task2->render();
            echo $frame;
            Common::flushOutput();

            $task1->tick();
            $task2->tick();
            usleep(80_000);
        }
    } finally {
        Terminal::cursorShow();
    }
    echo "\n";
}

if (in_array('--example4', $argv)) {
    // ─── Section 4 : Style Line ────────────────────────────────────────
    echo "\n" . TermColor::Gray->fmt('─── Style Line ───') . "\n\n";

    $bar = (new Throbber())
        ->message('Building')
        ->style(Throbber::STYLE_LINE)
        ->percent(0.0)
        ->template(
            TermColor::Green->fmt('{spinner}')
            . ' {message}  '
            . TermColor::LightGreen->fmt('{bar}')
            . '  '
            . TermColor::White->fmt('{percent}')
            . '  '
            . TermColor::Gray->fmt('{elapsed_paren}')
        )
        ->barWidth(20)
        ->start();

    foreach (range(0, 100) as $pct) {
        $bar->percent((float)$pct);
        echo Terminal::lineOverwritePrefix() . $bar->render();
        Common::flushOutput();
        $bar->tick();
        usleep(35_000);
    }
    echo Terminal::lineOverwritePrefix();
    echo Common::success("Building completed.") . "\n";
    echo "\n";
}

if (in_array('--example5', $argv)) {
    // ─── Section 5 : Common ────────────────────────────────────────
    echo "\n" . TermColor::Gray->fmt('─── Common ───') . "\n\n";

    echo Common::success("Building completed.") . "\n";
    echo Common::info("Info message.") . "\n";
    echo Common::warning("Warning message.") . "\n";
    echo Common::error("Error message.") . "\n";
    echo "\n";
}