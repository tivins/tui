<?php

declare(strict_types=1);

namespace Tivins\Tui;

/**
 * Chaînes avec séquences CSI : suppression des codes couleur / style (SGR) et largeur affichée.
 *
 * @see TermColor Les codes émis au format `\e[Nm` ou `\e[N;…m`.
 */
final class Ansi
{
    /**
     * Retire les séquences CSI de type « Select Graphic Rendition » (`\e[…m`).
     */
    public static function stripSgr(string $s): string
    {
        $out = preg_replace("/\x1b\[[0-9;]*m/", '', $s);

        return $out ?? $s;
    }

    /** Largeur à l’écran (sans compter les séquences SGR retirées par {@see stripSgr}). */
    public static function displayWidth(string $s): int
    {
        $plain = self::stripSgr($s);

        if (function_exists('mb_strlen')) {
            return mb_strlen($plain, 'UTF-8');
        }

        return strlen($plain);
    }

    /**
     * Avant-plan en 256 couleurs (`\e[38;5;nm`).
     *
     * @param int $code 0–255 (repères gris usuels : 232–255).
     */
    public static function fmtForeground256(int $code, string $text): string
    {
        if ($code < 0 || $code > 255) {
            throw new \InvalidArgumentException('Code couleur 256 hors plage : ' . $code);
        }

        return "\e[38;5;{$code}m" . $text . "\e[0m";
    }
}
