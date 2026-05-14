<?php

declare(strict_types=1);

namespace Tivins\Tui;

/**
 * Indicateur d'activité (spinner) pour interfaces terminal : plusieurs styles d'animation,
 * message, pourcentage et durée, texte piloté par un modèle (`{spinner}`, `{message}`, `{rotating_message}`, `{trail}`, `{bar}`, etc.).
 *
 * Chaque instance gère son propre index d'image pour permettre plusieurs indicateurs à l'écran.
 * Pour réécrire une ligne : {@see Terminal::lineOverwritePrefix()} (ou {@see Terminal::carriageReturn()} + {@see Terminal::eraseLine()}), puis {@see render()}.
 */
final class Throbber
{
    public const STYLE_BRAILLE = 'braille';

    /** Rotation ASCII (terminaux sans police braille lisible). */
    public const STYLE_PIPE = 'pipe';

    /** Braille densément rempli – animation circulaire (8 images). */
    public const STYLE_DOTS = 'dots';

    /** Barre montante/descendante en blocs Unicode (14 images). */
    public const STYLE_LINE = 'line';

    /** @var array<string, list<string>> */
    private static array $styleFrames = [
        self::STYLE_BRAILLE => [
            '⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏',
        ],
        self::STYLE_PIPE => [
            '|', '/', '-', '\\',
        ],
        self::STYLE_DOTS => [
            '⣾', '⣽', '⣻', '⢿', '⡿', '⣟', '⣯', '⣷',
        ],
        self::STYLE_LINE => [
            '▁', '▂', '▃', '▄', '▅', '▆', '▇', '█', '▇', '▆', '▅', '▄', '▃', '▂',
        ],
    ];

    private string $style = self::STYLE_BRAILLE;

    private int $frameIndex = 0;

    private string $message = '';

    private ?float $percent = null;

    private ?float $startedAt = null;

    private string $template = '{spinner} {message}{trail}';

    /** Largeur de la barre de progression `{bar}` en caractères (défaut : 10). */
    private int $barWidth = 10;

    /**
     * @param list<string> $frames
     */
    public static function registerStyle(string $style, array $frames): void
    {
        if ($frames === []) {
            throw new \InvalidArgumentException('Un style Throbber doit contenir au moins une image.');
        }
        self::$styleFrames[$style] = array_values($frames);
    }

    /** @return list<string> */
    public static function registeredStyles(): array
    {
        return array_keys(self::$styleFrames);
    }

    /**
     * Préfixe (retour chariot + effacement de ligne CSI) à émettre avant {@see render()}
     * pour remplacer la dernière ligne affichée sans nouvelle ligne supplémentaire.
     *
     * @see Terminal::lineOverwritePrefix()
     */
    public static function lineRefreshPrefix(): string
    {
        return Terminal::lineOverwritePrefix();
    }

    /**
     * Formate une durée en secondes pour l'affichage : `m:ss` si moins d'une heure, sinon `h:mm:ss`.
     */
    public static function formatDuration(float $seconds): string
    {
        if ($seconds < 0.0) {
            $seconds = 0.0;
        }
        $total = (int) floor($seconds);
        $h = intdiv($total, 3600);
        $m = intdiv($total % 3600, 60);
        $s = $total % 60;
        if ($h > 0) {
            return sprintf('%d:%02d:%02d', $h, $m, $s);
        }

        return sprintf('%d:%02d', $m, $s);
    }

    public function style(string $style): self
    {
        if (!isset(self::$styleFrames[$style])) {
            throw new \InvalidArgumentException('Style Throbber inconnu : ' . $style);
        }
        $this->style = $style;
        $this->frameIndex = 0;

        return $this;
    }

    public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /** Entier 0–100 recommandé ; null = ne pas afficher de pourcentage ni de barre. */
    public function percent(?float $value): self
    {
        $this->percent = $value;

        return $this;
    }

    /**
     * Démarre le chronomètre pour `{elapsed}` / `{trail}`. Avec une date absolue (tests ou reprise).
     */
    public function start(?float $referenceTime = null): self
    {
        $this->startedAt = $referenceTime ?? microtime(true);

        return $this;
    }

    public function stopClock(): self
    {
        $this->startedAt = null;

        return $this;
    }

    /**
     * Modèle avec remplacements : `{spinner}`, `{message}`, `{rotating_message}`, `{trail}`, `{percent}`, `{elapsed}`, `{elapsed_paren}`, `{bar}`.
     */
    public function template(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Largeur en caractères de la barre `{bar}` (par défaut 10 ; minimum 1).
     */
    public function barWidth(int $width): self
    {
        $this->barWidth = max(1, $width);

        return $this;
    }

    public function resetFrame(): self
    {
        $this->frameIndex = 0;

        return $this;
    }

    /** Avance l'animation d'un pas (boucle sur les images du style). */
    public function tick(): self
    {
        $frames = $this->framesForStyle();
        $n = \count($frames);
        if ($n === 0) {
            return $this;
        }
        $this->frameIndex = ($this->frameIndex + 1) % $n;

        return $this;
    }

    public function frameIndex(): int
    {
        return $this->frameIndex;
    }

    /** Durée écoulée en secondes depuis {@see start()} ; 0 si l'horloge n'est pas démarrée. */
    public function elapsedSeconds(): float
    {
        if ($this->startedAt === null) {
            return 0.0;
        }

        return microtime(true) - $this->startedAt;
    }

    /** Glyphe d'animation pour l'image courante. */
    public function spinner(): string
    {
        $frames = $this->framesForStyle();
        if ($frames === []) {
            return '';
        }

        return $frames[$this->frameIndex % \count($frames)];
    }

    public function render(): string
    {
        $map = $this->placeholderMap();

        return (string) \preg_replace_callback(
            '/\{([a-z_]+)\}/',
            static function (array $m) use ($map): string {
                $key = $m[1] ?? '';

                return $map[$key] ?? $m[0];
            },
            $this->template
        );
    }

    /**
     * Suffixe « intelligent » : espace + `45%` et/ou `(m:ss)` selon ce qui est défini.
     * Ex. ` ⠿ Thinking... (0:24)` ou ` ⠾ downloading 45% (0:13)`.
     */
    public function trail(): string
    {
        $parts = [];
        if ($this->percent !== null) {
            $parts[] = sprintf('%d%%', (int) \round($this->percent));
        }
        if ($this->startedAt !== null) {
            $parts[] = '(' . self::formatDuration($this->elapsedSeconds()) . ')';
        }
        if ($parts === []) {
            return '';
        }

        return ' ' . \implode(' ', $parts);
    }

    /** @return list<string> */
    public function framesForCurrentStyle(): array
    {
        return $this->framesForStyle();
    }

    /** @return array<string, string> */
    private function placeholderMap(): array
    {
        $pct = '';
        if ($this->percent !== null) {
            $pct = sprintf('%d%%', (int) \round($this->percent));
        }
        $elapsed = '';
        $elapsedParen = '';
        if ($this->startedAt !== null) {
            $elapsed = self::formatDuration($this->elapsedSeconds());
            $elapsedParen = '(' . $elapsed . ')';
        }

        return [
            'spinner'          => $this->spinner(),
            'message'          => $this->message,
            'rotating_message'  => RotatingColors::render($this->message, $this->frameIndex),
            'trail'            => $this->trail(),
            'percent'          => $pct,
            'elapsed'          => $elapsed,
            'elapsed_paren'    => $elapsedParen,
            'bar'              => $this->renderBar(),
        ];
    }

    /**
     * Barre de progression Unicode (`█` rempli / `░` vide) de largeur {@see $barWidth}.
     * Retourne une chaîne vide si `percent` est null.
     */
    private function renderBar(): string
    {
        if ($this->percent === null) {
            return '';
        }
        $pct = max(0.0, min(100.0, $this->percent));
        $filled = (int) round($pct / 100.0 * $this->barWidth);
        $empty = $this->barWidth - $filled;

        return str_repeat('█', $filled) . str_repeat('░', $empty);
    }

    /** @return list<string> */
    private function framesForStyle(): array
    {
        return self::$styleFrames[$this->style] ?? [];
    }
}
