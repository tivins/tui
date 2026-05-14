<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Tivins\Tui\AsciiText;

echo implode(PHP_EOL, AsciiText::A) . PHP_EOL;
echo implode(PHP_EOL, AsciiText::B) . PHP_EOL;

echo AsciiText::toString(AsciiText::get('ABC')) . PHP_EOL;