<?php

declare(strict_types=1);

namespace Tivins\Tui;

/**
 * Barre de progression texte (blocs Unicode), indépendante de {@see Throbber}.
 */
final class ProgressBar
{
    /**
     * @param float $percent pourcentage affiché (0–100, borne automatiquement)
     * @param int $width largeur en « cellules » (glyphe plein + vide)
     */
    public static function render(
        float $percent,
        int $width,
        string $filled = '█',
        string $empty = '░',
    ): string {
        $width = max(1, $width);
        $pct = max(0.0, min(100.0, $percent));
        $filledCount = (int) round($pct / 100.0 * $width);
        $filledCount = max(0, min($width, $filledCount));
        $emptyCount = $width - $filledCount;

        return str_repeat($filled, $filledCount) . str_repeat($empty, $emptyCount);
    }
}
