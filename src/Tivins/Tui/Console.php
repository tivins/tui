<?php

declare(strict_types=1);

namespace Tivins\Tui;

/**
 * Entrée console : détection TTY et lecture de lignes (sans mélanger avec les séquences du terminal).
 */
final class Console
{
    /** Indique si {@see STDIN} est relié à un terminal interactif. */
    public static function stdinIsTty(): bool
    {
        return defined('STDIN') && is_resource(STDIN) && stream_isatty(STDIN);
    }

    /**
     * Lit une ligne du flux (par défaut {@see STDIN}), ou null si fin de fichier / flux indisponible.
     *
     * @param resource|null $stream
     */
    public static function readLine($stream = null): ?string
    {
        if ($stream !== null) {
            if (!is_resource($stream)) {
                return null;
            }
            $line = fgets($stream);

            return $line === false ? null : $line;
        }

        if (!defined('STDIN') || !is_resource(STDIN)) {
            return null;
        }

        $line = fgets(STDIN);

        return $line === false ? null : $line;
    }
}
