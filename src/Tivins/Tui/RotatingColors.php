<?php

declare(strict_types=1);

namespace Tivins\Tui;

/**
 * Effet « couleurs tournantes » : palette décalée le long du texte (flash qui se déplace).
 *
 * Chaque glyphe reçoit une couleur selon sa position et un décalage entier (typiquement
 * incrémenté à chaque frame). La palette par défaut est un dégradé gris symétrique.
 */
final class RotatingColors
{
    /**
     * Dégradé gris → gris clair → blanc → gris clair → gris (une « vague » centrée).
     *
     * @return list<TermColor>
     */
    public static function defaultPalette(): array
    {
        return [
            TermColor::Gray,
            TermColor::LightGray,
            TermColor::LightGray,
            TermColor::White,
            TermColor::LightGray,
            TermColor::LightGray,
            TermColor::Gray,
        ];
    }

    /**
     * @param list<TermColor>|null $palette null = {@see defaultPalette()}
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
            $out .= $palette[$pi]->fmt($ch);
        }

        return $out;
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
