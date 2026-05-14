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

    /**
     * Largeur à l’écran (sans compter les séquences SGR retirées par {@see stripSgr}).
     *
     * Utilise {@see mb_strwidth} quand il est disponible (règles type « East Asian width ») :
     * les emojis et caractères « fullwidth » comptent en général 2 colonnes en terminal, contrairement
     * à {@see mb_strlen} qui ne compte que les points de code.
     */
    public static function displayWidth(string $s): int
    {
        $plain = self::stripSgr($s);

        if (function_exists('mb_strwidth')) {
            return mb_strwidth($plain, 'UTF-8');
        }

        if (function_exists('mb_strlen')) {
            return mb_strlen($plain, 'UTF-8');
        }

        return strlen($plain);
    }

    /**
     * Troncature UTF-8 sur une largeur d’affichage en colonnes (sans SGR dans $plain).
     */
    public static function slicePlainToDisplayWidth(string $plain, int $maxWidth): string
    {
        $maxWidth = max(0, $maxWidth);
        if ($maxWidth === 0 || $plain === '') {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            $len = mb_strlen($plain, 'UTF-8');
            $last = 0;
            for ($i = 1; $i <= $len; $i++) {
                $prefix = mb_substr($plain, 0, $i, 'UTF-8');
                $w = function_exists('mb_strwidth')
                    ? mb_strwidth($prefix, 'UTF-8')
                    : mb_strlen($prefix, 'UTF-8');
                if ($w <= $maxWidth) {
                    $last = $i;

                    continue;
                }

                break;
            }

            return $last <= 0 ? '' : mb_substr($plain, 0, $last, 'UTF-8');
        }

        return strlen($plain) <= $maxWidth ? $plain : substr($plain, 0, $maxWidth);
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
