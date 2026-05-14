<?php

declare(strict_types=1);

namespace Tivins\Tui;

/**
 * Budget colonnes pour préparer du texte brut (sans séquences SGR) avant rendu
 * dans un {@see Frame} ou un {@see Layout}.
 *
 * Le retour à la ligne s’appuie sur {@see Ansi::displayWidth} (largeur terminal),
 * pas sur la simple longueur en points de code.
 */
final class TextViewport
{
    /**
     * @param int $columns largeur utile en colonnes pour {@see wrapPlainParagraph} (≥ 1)
     * @param int $tabWidth pas des tabulations pour {@see expandTabsInLine} (≥ 1)
     */
    public function __construct(
        private readonly int $columns,
        private readonly int $tabWidth = 8,
        private readonly bool $breakLongWords = true,
    ) {
        if ($columns < 1) {
            throw new \InvalidArgumentException('columns doit être ≥ 1, reçu : ' . $columns);
        }
        if ($tabWidth < 1) {
            throw new \InvalidArgumentException('tabWidth doit être ≥ 1, reçu : ' . $tabWidth);
        }
    }

    public function columns(): int
    {
        return $this->columns;
    }

    public function tabWidth(): int
    {
        return $this->tabWidth;
    }

    public function breakLongWords(): bool
    {
        return $this->breakLongWords;
    }

    /**
     * Duplique le viewport avec une autre largeur (même tabulations / césure).
     */
    public function withColumns(int $columns): self
    {
        return new self($columns, $this->tabWidth, $this->breakLongWords);
    }

    /**
     * Espaces pour aligner les lignes de suite sous une première ligne « étiquette + espace ».
     */
    public static function continuationPadPlain(string $leaderPlain): string
    {
        return str_repeat(' ', max(0, Ansi::displayWidth($leaderPlain) + 1));
    }

    /**
     * Remplace les tabulations par des espaces jusqu’au prochain multiple de {@see tabWidth()},
     * mesure via {@see Ansi::displayWidth}.
     */
    public function expandTabsInLine(string $line): string
    {
        $tw = $this->tabWidth;
        $parts = explode("\t", $line);
        if ($parts === []) {
            return '';
        }

        $acc = array_shift($parts);
        foreach ($parts as $next) {
            $len = Ansi::displayWidth($acc);
            $pad = $tw - ($len % $tw);
            if ($pad === 0) {
                $pad = $tw;
            }
            $acc .= str_repeat(' ', $pad) . $next;
        }

        return $acc;
    }

    /**
     * Plusieurs paragraphes séparés par `\n` : chaque segment est enveloppé séparément
     * (retours ligne dans la chaîne d’entrée préservés comme ruptures logiques).
     *
     * @return list<string>
     */
    public function wrapPlain(string $plain): array
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $plain);
        $out = [];
        foreach (explode("\n", $normalized) as $segment) {
            foreach ($this->wrapPlainParagraph($segment) as $w) {
                $out[] = $w;
            }
        }

        return $out;
    }

    /**
     * Un paragraphe (pas de `\n` attendus ; si présents, ils sont traités comme espaces
     * via {@see wrapPlain}).
     *
     * @return list<string>
     */
    public function wrapPlainParagraph(string $plain): array
    {
        $line = $this->expandTabsInLine($plain);
        $line = trim($line);
        if ($line === '') {
            return [''];
        }

        $width = $this->columns;
        $words = preg_split('/\s+/u', $line, -1, PREG_SPLIT_NO_EMPTY);
        if ($words === false || $words === []) {
            return [$line];
        }

        $out = [];
        $cur = '';

        foreach ($words as $word) {
            if ($this->breakLongWords) {
                while ($word !== '' && Ansi::displayWidth($word) > $width) {
                    if ($cur !== '') {
                        $out[] = rtrim($cur);
                        $cur = '';
                    }
                    $chunk = Ansi::slicePlainToDisplayWidth($word, $width);
                    if ($chunk === '' && $word !== '') {
                        $chunk = mb_substr($word, 0, 1, 'UTF-8');
                    }
                    if ($chunk === '') {
                        break;
                    }
                    $out[] = $chunk;
                    $take = mb_strlen($chunk, 'UTF-8');
                    $word = mb_substr($word, $take, null, 'UTF-8');
                }
                if ($word === '') {
                    continue;
                }
            } elseif (Ansi::displayWidth($word) > $width) {
                if ($cur !== '') {
                    $out[] = rtrim($cur);
                    $cur = '';
                }
                $out[] = $word;

                continue;
            }

            $sep = $cur === '' ? '' : ' ';
            $candidate = $cur . $sep . $word;
            if (Ansi::displayWidth($candidate) <= $width) {
                $cur = $candidate;
            } else {
                if ($cur !== '') {
                    $out[] = rtrim($cur);
                }
                $cur = $word;
            }
        }

        if ($cur !== '') {
            $out[] = rtrim($cur);
        }

        return $out;
    }

    /**
     * Enveloppe un paragraphe puis préfixe la première ligne ; les suivantes sont indentées
     * pour s’aligner sous le texte après l’étiquette (largeur {@see Ansi::displayWidth}).
     *
     * La césure utilise une largeur réduite : {@see columns()} moins l’étiquette et l’espace,
     * pour que chaque ligne résultante tienne dans le budget global.
     *
     * @return list<string>
     */
    public function wrapPlainParagraphWithLeader(string $plain, string $leaderPlain): array
    {
        $budget = $this->columns - Ansi::displayWidth($leaderPlain) - 1;
        if ($budget < 1) {
            $budget = 1;
        }

        $inner = $this->withColumns($budget);
        $wrapped = $inner->wrapPlainParagraph($plain);
        $pad = self::continuationPadPlain($leaderPlain);
        $out = [];

        foreach ($wrapped as $i => $fragment) {
            $out[] = $i === 0 ? $leaderPlain . ' ' . $fragment : $pad . $fragment;
        }

        return $out;
    }

    /**
     * Joint les lignes comme {@see wrapPlain} puis une seule chaîne séparée par `\n`.
     */
    public function wrapPlainAsString(string $plain): string
    {
        return implode("\n", $this->wrapPlain($plain));
    }
}
