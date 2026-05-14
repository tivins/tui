<?php

declare(strict_types=1);

namespace Tivins\Tui;

/**
 * Composition de blocs multi-lignes (cadres déjà rendus, bannières, etc.).
 */
final class Layout
{
    /**
     * Place des blocs côte à côte : chaque ligne est la concaténation des lignes
     * correspondantes, complétées par des espaces pour aligner les colonnes (largeur
     * affichée via {@see Ansi::displayWidth}).
     *
     * Les blocs plus courts sont complétés par des lignes vides en bas.
     *
     * @param list<string> $blocks chaînes multi-lignes (séparateur `\n`)
     */
    public static function horizontal(array $blocks, int $gap = 1): string
    {
        if ($blocks === []) {
            return '';
        }

        $gap = max(0, $gap);
        $gapStr = str_repeat(' ', $gap);

        /** @var list<list<string>> $lineArrays */
        $lineArrays = [];
        /** @var list<int> $widths */
        $widths = [];

        foreach ($blocks as $block) {
            $normalized = str_replace(["\r\n", "\r"], "\n", $block);
            $lines = $normalized === '' ? [] : explode("\n", $normalized);
            $lineArrays[] = $lines;

            $w = 0;
            foreach ($lines as $line) {
                $w = max($w, Ansi::displayWidth($line));
            }
            $widths[] = $w;
        }

        $maxLines = 0;
        foreach ($lineArrays as $lines) {
            $maxLines = max($maxLines, count($lines));
        }

        $out = [];
        for ($i = 0; $i < $maxLines; $i++) {
            $parts = [];
            foreach ($lineArrays as $bi => $lines) {
                $line = $lines[$i] ?? '';
                $pad = $widths[$bi] - Ansi::displayWidth($line);
                $parts[] = $line . str_repeat(' ', max(0, $pad));
            }
            $out[] = implode($gapStr, $parts);
        }

        return implode("\n", $out);
    }
}
