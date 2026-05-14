<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Tivins\Tui\AsciiText;
use Tivins\Tui\Frame;
use Tivins\Tui\TermColor;

echo AsciiText::toString(AsciiText::get('abc')) . PHP_EOL . PHP_EOL;

echo AsciiText::toString(AsciiText::get('Hello 2026!')) . PHP_EOL;

echo Frame::from(AsciiText::toString(AsciiText::get('PHP TUI')) . "\nTerminal User Interface")
    ->borderStyle(Frame::STYLE_ROUNDED)
    ->borderColor(TermColor::Green)
    ->contentColor(TermColor::White)
    ->paddingHorizontal(1)
    ->render() . PHP_EOL;