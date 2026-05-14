<?php

declare(strict_types=1);

namespace Tivins\Tui;

/**
 * Effet « couleurs tournantes » : palette décalée le long du texte (flash qui se déplace).
 *
 * Chaque glyphe reçoit une couleur selon sa position et un décalage entier (typiquement
 * incrémenté à chaque frame). La palette par défaut utilise la rampe de gris du jeu
 * 256 couleurs (échelons plus fins que les seuls gris ANSI 16 couleurs).
 */
final class RotatingColors
{
    /**
     * Vague grise centrée sur du blanc (indices xterm 237 → 255 → 237).
     *
     * @return list<int> codes 256 couleurs (0–255)
     */
    public static function defaultPalette(): array
    {
        return [245, 250, 250, 255, 250, 250, 245];
        //return [237, 240, 244, 255, 244, 240, 237];
    }

    /**
     * @param list<TermColor|int>|null $palette entrées `int` = avant-plan 256 couleurs ; null = {@see defaultPalette()}
     */
    public static function render(string $text, int $offset = 0, ?array $palette = null): string
    {
        $palette = $palette ?? self::defaultPalette();
        if ($palette === []) {
            throw new \InvalidArgumentException('La palette RotatingColors ne peut pas être vide.');
        }

        $chars = self::utf8Chars($text);
        $n = \count($palette);
        $out = '';

        foreach ($chars as $i => $ch) {
            $pi = self::mod($i + $offset, $n);
            $out .= self::applyEntry($palette[$pi], $ch);
        }

        return $out;
    }

    private static function applyEntry(TermColor|int $entry, string $ch): string
    {
        if ($entry instanceof TermColor) {
            return $entry->fmt($ch);
        }

        return Ansi::fmtForeground256($entry, $ch);
    }

    /** @return list<string> */
    private static function utf8Chars(string $text): array
    {
        if ($text === '') {
            return [];
        }

        if (\function_exists('mb_str_split')) {
            /** @var list<string> */
            return \mb_str_split($text);
        }

        $parts = \preg_split('//u', $text, -1, \PREG_SPLIT_NO_EMPTY);

        return $parts === false ? [] : $parts;
    }

    private static function mod(int $a, int $n): int
    {
        if ($n <= 0) {
            return 0;
        }

        $r = $a % $n;

        return $r < 0 ? $r + $n : $r;
    }
}
