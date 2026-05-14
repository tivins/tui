<?php

declare(strict_types=1);

namespace Tivins\Tui;

final class Common
{
    public static function flushOutput(): void
    {
        if (ob_get_level() > 0) {
            ob_flush();
        }
    }
    public static function success(string $message): string
    {
        return TermColor::Green->fmt("✓ $message");
    }
    public static function error(string $message): string
    {
        // ✗ is too wide
        return TermColor::Red->fmt("× $message");
    }
    public static function warning(string $message): string
    {
        // "⚠" is too wide
        return TermColor::Yellow->fmt("! $message");
    }
    public static function info(string $message): string
    {
        // "ℹ" is too wide
        return TermColor::Blue->fmt("i $message");
    }
}