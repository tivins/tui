<?php

declare(strict_types=1);

/**
 * Exemples d'utilisation de {@see \Tivins\Tui\Frame} : styles, couleurs, titres,
 * contenu multiligne, cas limites (Unicode, fins de ligne, titres longs, padding).
 *
 * Exécuter : php examples/frame.php
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Tivins\Tui\Frame;
use Tivins\Tui\TermColor;

$sections = [];

$sections[] = [
    'title' => '1. Basique — arrondi, couleurs',
    'text' => (string) Frame::from("Ligne 1\nLigne 2")
        ->borderStyle(Frame::STYLE_ROUNDED)
        ->borderColor(TermColor::Cyan)
        ->contentColor(TermColor::Yellow)
        ->title('Démo', Frame::ALIGN_CENTER)
        ->bottomTitle('bas', Frame::ALIGN_RIGHT)
        ->padding(1),
];

$sections[] = [
    'title' => '2. Titres — gauche / centre / droite',
    'text' =>
        Frame::from('')->title('Gauche', Frame::ALIGN_LEFT)->borderStyle(Frame::STYLE_SINGLE)->render()
        . "\n"
        . Frame::from('')->title('Centre', Frame::ALIGN_CENTER)->borderStyle(Frame::STYLE_SINGLE)->render()
        . "\n"
        . Frame::from('')->title('Droite', Frame::ALIGN_RIGHT)->borderStyle(Frame::STYLE_SINGLE)->render(),
];

$sections[] = [
    'title' => '3. Sans couleur ANSI',
    'text' => Frame::from("OK\n...")
        ->borderStyle(Frame::STYLE_DOUBLE)
        ->title('Log')
        ->borderColor(null)
        ->contentColor(null)
        ->render(),
];

$sections[] = [
    'title' => '4. Contenu centré + titre couleur différente',
    'text' => (string) Frame::from("alpha\nbeta\ngamma")
        ->borderStyle(Frame::STYLE_HEAVY)
        ->borderColor(TermColor::Blue)
        ->titleColor(TermColor::LightYellow)
        ->contentColor(TermColor::LightGreen)
        ->title('Journal', Frame::ALIGN_CENTER)
        ->contentAlignment(Frame::ALIGN_CENTER)
        ->paddingHorizontal(2),
];

$sections[] = [
    'title' => '5. Bordure partiellement personnalisée (fusion avec single)',
    'text' => (string) new Frame('…', ['v' => '┆'])
        ->borderStyle(Frame::STYLE_SINGLE)
        ->title("Barres │ → ┆")
        ->borderColor(TermColor::Magenta),
];

$sections[] = [
    'title' => '6. Unicode + lignes vides + fin de ligne Windows',
    'text' => (string) Frame::from("café\r\ntab\tici\n\nvide au-dessus")
        ->borderStyle(Frame::STYLE_ROUNDED)
        ->borderColor(TermColor::White)
        ->contentColor(TermColor::Gray)
        ->title('UTF-8 / CRLF'),
];

$sections[] = [
    'title' => '7. Titre long (troncature) + minInnerWidth',
    'text' => (string) Frame::from('contenu')
        ->borderStyle(Frame::STYLE_SINGLE)
        ->title('Ce titre est volontairement très long pour montrer la troncature')
        ->minInnerWidth(28)
        ->borderColor(TermColor::Red)
        ->contentColor(TermColor::LightGray),
];

$sections[] = [
    'title' => '8. Titre ambigu (répétition de tirets) — colorisation stable',
    'text' => (string) Frame::from('test')
        ->borderStyle(Frame::STYLE_SINGLE)
        ->title('──')
        ->titleColor(TermColor::LightRed)
        ->borderColor(TermColor::Green)
        ->contentColor(TermColor::Cyan),
];

$sections[] = [
    'title' => '9. Padding négatif ignoré (clamp à 0)',
    'text' => (string) Frame::from('ok')
        ->paddingRect(1, -3, 0, 2)
        ->borderColor(TermColor::Yellow),
];

foreach ($sections as $i => $block) {
    if ($i > 0) {
        echo "\n";
    }

    echo TermColor::LightCyan->fmt('=== ' . $block['title'] . ' ===') . PHP_EOL;
    echo $block['text'] . PHP_EOL;
}
