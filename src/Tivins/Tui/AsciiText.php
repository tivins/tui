<?php

declare(strict_types=1);

namespace Tivins\Tui;
/*
Glyphs are generated from the FIGlet / TOIlet font Future Smooth (Future_Smooth.flf in figlet.js): php tools/generate_future_smooth.php
*/

class AsciiText
{
    /** @var array<string, array{string,string,string}>|null */
    private static ?array $glyphCache = null;

    private const FALLBACK = [' ', ' ', ' '];

    /** Subset rendered with Future Smooth (lowercase letters; Latin symbols as in the banner above). */
    public const CHARSET = 'abcdefghijklmnopqrstuvwxyz0123456789!:;,*$%&~"' . "'" . '{([-|`\\@)]}/+.';

    /**
     * @return list<array{string,string,string}>
     */
    public static function get(string $text): array
    {
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        if ($chars === false) {
            return [];
        }

        $map = self::glyphs();
        $out = [];
        foreach ($chars as $ch) {
            $out[] = $map[mb_strtolower($ch)] ?? self::FALLBACK;
        }

        return $out;
    }

    /**
     * @param list<array{string,string,string}> $letters
     */
    public static function toString(array $letters): string
    {
        $lines = ['', '', ''];
        foreach ($letters as $letter) {
            for ($i = 0; $i < 3; $i++) {
                $lines[$i] .= ($lines[$i] !== '' ? ' ' : '') . $letter[$i];
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<string, array{string,string,string}>
     */
    private static function glyphs(): array
    {
        self::$glyphCache ??= require __DIR__ . '/AsciiTextGlyphs.generated.php';

        return self::$glyphCache;
    }
}
