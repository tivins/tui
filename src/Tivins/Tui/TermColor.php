<?php

declare(strict_types=1);

namespace Tivins\Tui;

/**
 * Colors for terminal output
 */
enum TermColor: string
{
    case Reset = '0';
    case Green = '32';
    case Red = '31';
    case Yellow = '33';
    case Blue = '34';
    case Magenta = '35';
    case Cyan = '36';
    case White = '97';
    case Black = '30';
    case Gray = '90';
    case LightGray = '37';
    case LightRed = '91';
    case LightGreen = '92';
    case LightYellow = '93';
    case LightBlue = '94';
    case LightMagenta = '95';
    case LightCyan = '96';

    public function fmt(string $string): string
    {
        return "\e[{$this->value}m" . $string . "\e[0m";
    }
}
