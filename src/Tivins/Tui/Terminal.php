<?php

declare(strict_types=1);

namespace Tivins\Tui;

class Terminal
{
    public static function clear(): void
    {
        echo "\e[2J\e[H";
    }

    public static function cursorHome(): void
    {
        echo "\e[H";
    }

    public static function cursorUp(int $lines = 1): void
    {
        echo "\e[{$lines}A";
    }

    public static function cursorDown(int $lines = 1): void
    {
        echo "\e[{$lines}B";
    }

    public static function cursorForward(int $columns = 1): void
    {
        echo "\e[{$columns}C";
    }

    public static function cursorBack(int $columns = 1): void
    {
        echo "\e[{$columns}D";
    }

    public static function cursorNextLine(int $lines = 1): void
    {
        echo "\e[{$lines}E";
    }

    /**
     * CSI CPL : remonte le curseur de « n » lignes puis le place en colonne 1 (plus pratique que {@see cursorUp()}
     * + {@see carriageReturn()} pour réécrire un bloc multi-lignes comme dans `examples/throbber.php`).
     */
    public static function cursorPreviousLine(int $lines = 1): void
    {
        echo "\e[{$lines}F";
    }

    /** Déplace le curseur (ligne ; colonne), indices ANSI en base 1 (comme `\e[row;colH`). */
    public static function cursorMove(int $lines = 0, int $columns = 0): void
    {
        echo "\e[{$lines};{$columns}H";
    }

    public static function cursorSavePosition(): void
    {
        echo "\e[s";
    }

    public static function cursorRestorePosition(): void
    {
        echo "\e[u";
    }

    public static function cursorHide(): void
    {
        echo "\e[?25l";
    }

    public static function cursorShow(): void
    {
        echo "\e[?25h";
    }

    public static function cursorDisable(): void
    {
        self::cursorHide();
    }

    public static function cursorEnable(): void
    {
        self::cursorShow();
    }

    /**
     * Demande la position du curseur au terminal (séquence CPR).
     *
     * Le terminal répond sur l’entrée standard par une séquence du type `\e[row;colR`.
     * En mode ligne (canonique), la réponse peut rester bloquée ou être incomplète :
     * il faut généralement désactiver le buffering (`stty -icanon -echo min 1 time 0` sous Unix).
     *
     * @param resource|null $input Flux à lire (par défaut : `/dev/tty` sous Unix si disponible, sinon `STDIN`).
     * @return array{row: int, column: int}|null
     */
    public static function cursorPosition($input = null): ?array
    {
        $close = null;
        if ($input === null) {
            if (\PHP_OS_FAMILY !== 'Windows') {
                $tty = @\fopen('/dev/tty', 'rb');
                if (\is_resource($tty)) {
                    $input = $tty;
                    $close = $tty;
                }
            }
            if ($input === null) {
                $input = \STDIN;
            }
        }

        echo "\e[6n";
        if (\is_resource(\STDOUT)) {
            \fflush(\STDOUT);
        }

        $buffer = '';
        while (\strlen($buffer) < 64) {
            $c = \fread($input, 1);
            if ($c === false || $c === '') {
                break;
            }
            $buffer .= $c;
            if ($c === 'R') {
                break;
            }
        }

        if ($close !== null) {
            \fclose($close);
        }

        return self::parseCursorPositionResponse($buffer);
    }

    /**
     * Interprète une réponse CPR complète ou partielle (`…[ligne;colonneR`).
     *
     * @return array{row: int, column: int}|null
     */
    public static function parseCursorPositionResponse(string $response): ?array
    {
        if (!\preg_match('/\e\\[([0-9]+);([0-9]+)R/', $response, $m)) {
            return null;
        }

        return [
            'row' => (int) $m[1],
            'column' => (int) $m[2],
        ];
    }

    /**
     * Taille du terminal en lignes et colonnes, ou null si aucune méthode n’a réussi.
     *
     * Ordre : variables d’environnement `LINES` / `COLUMNS`, puis `posix_ioctl` (si disponible),
     * puis selon l’OS : `stty size` / `tput`, ou sous Windows analyse de la sortie de `mode con`.
     *
     * @return array{rows: int, cols: int}|null
     */
    public static function screenSize(): ?array
    {
        $fromEnv = self::screenSizeFromEnv();
        if ($fromEnv !== null) {
            return $fromEnv;
        }

        $fromIoctl = self::screenSizeFromIoctl();
        if ($fromIoctl !== null) {
            return $fromIoctl;
        }

        if (\PHP_OS_FAMILY === 'Windows') {
            $fromMode = self::screenSizeFromWindowsMode();
            if ($fromMode !== null) {
                return $fromMode;
            }
        }

        $nul = self::stderrToNullRedirect();
        $stty = self::shellOutput('stty size ' . $nul);
        if ($stty !== null && \preg_match('/^(\d+)\s+(\d+)/', \trim($stty), $m)) {
            $rows = (int) $m[1];
            $cols = (int) $m[2];
            if ($rows > 0 && $cols > 0) {
                return ['rows' => $rows, 'cols' => $cols];
            }
        }

        $lines = self::shellOutput('tput lines ' . $nul);
        $colsOut = self::shellOutput('tput cols ' . $nul);
        if ($lines !== null && $colsOut !== null) {
            $rows = (int) \trim($lines);
            $cols = (int) \trim($colsOut);
            if ($rows > 0 && $cols > 0) {
                return ['rows' => $rows, 'cols' => $cols];
            }
        }

        return null;
    }

    /** @return array{rows: int, cols: int}|null */
    private static function screenSizeFromEnv(): ?array
    {
        $lines = \getenv('LINES');
        $cols = \getenv('COLUMNS');
        if ($lines === false || $cols === false) {
            return null;
        }
        $rows = (int) $lines;
        $c = (int) $cols;
        if ($rows <= 0 || $c <= 0) {
            return null;
        }

        return ['rows' => $rows, 'cols' => $c];
    }

    /** @return array{rows: int, cols: int}|null */
    private static function screenSizeFromIoctl(): ?array
    {
        if (!\extension_loaded('posix') || !\defined('TIOCGWINSZ')) {
            return null;
        }
        $fd = @\fopen('/dev/tty', 'r');
        if ($fd === false) {
            $fd = @\fopen('php://stdin', 'r');
        }
        if ($fd === false || !\is_resource($fd)) {
            return null;
        }

        $winsize = '';
        $ok = @\posix_ioctl($fd, (int) \constant('TIOCGWINSZ'), $winsize);
        \fclose($fd);
        if (!$ok || $winsize === '') {
            return null;
        }

        /** @var false|array<int|string, int> $u */
        $u = \unpack('Srow/Scol/Sx/Spy', $winsize);
        if ($u === false || !isset($u['row'], $u['col']) || $u['row'] <= 0 || $u['col'] <= 0) {
            return null;
        }

        return ['rows' => $u['row'], 'cols' => $u['col']];
    }

    /** @return array{rows: int, cols: int}|null */
    private static function screenSizeFromWindowsMode(): ?array
    {
        $out = self::shellOutput('mode con');
        if ($out === null) {
            return null;
        }
        $rows = null;
        $cols = null;
        if (\preg_match('/^\s*(?:Lines|Lignes)\s*:\s*(\d+)/mi', $out, $m)) {
            $rows = (int) $m[1];
        }
        if (\preg_match('/^\s*(?:Columns|Colonnes)\s*:\s*(\d+)/mi', $out, $m)) {
            $cols = (int) $m[1];
        }
        if ($rows === null || $cols === null || $rows <= 0 || $cols <= 0) {
            return null;
        }

        return ['rows' => $rows, 'cols' => $cols];
    }

    /** Redirection stderr « vers le néant » : sous cmd.exe, `2>/dev/null` cible un chemin invalide et affiche une erreur. */
    private static function stderrToNullRedirect(): string
    {
        return \PHP_OS_FAMILY === 'Windows' ? '2>nul' : '2>/dev/null';
    }

    private static function shellOutput(string $command): ?string
    {
        $out = \shell_exec($command);
        if (!\is_string($out) || $out === '') {
            return null;
        }

        return $out;
    }

    /** Retour chariot : curseur en colonne 1 de la ligne courante (`\r`). */
    public static function carriageReturn(): void
    {
        echo "\r";
    }

    /**
     * Préfixe pour réécrire la ligne courante sans ajouter de ligne : voir {@see carriageReturn()} et effacement `\e[2K`.
     * À concaténer avant le nouveau texte puis `flush()`.
     */
    public static function lineOverwritePrefix(): string
    {
        return "\r\e[2K";
    }

    /** Efface toute la ligne courante (curseur vertical inchangé). */
    public static function eraseLine(): void
    {
        echo "\e[2K";
    }

    /** Efface depuis le curseur jusqu’à la fin de la ligne. */
    public static function eraseLineEnd(): void
    {
        echo "\e[K";
    }

    /** Bascule vers le tampon d’écran alternatif (plein écran type vim). */
    public static function enterAlternateScreen(): void
    {
        echo "\e[?1049h";
    }

    /** Revient au tampon principal. */
    public static function leaveAlternateScreen(): void
    {
        echo "\e[?1049l";
    }
}
