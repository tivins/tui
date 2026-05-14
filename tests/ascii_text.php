<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Tivins\Tui\AsciiText;

$glyphs = require __DIR__ . '/../src/Tivins/Tui/AsciiTextGlyphs.generated.php';

assert(count($glyphs) === mb_strlen(AsciiText::CHARSET));

$hello = AsciiText::get('hello');
assert(count($hello) === 5);
assert($hello[0] === $glyphs['h']);

$banner = AsciiText::toString(AsciiText::get('hi'));
$lines = explode("\n", $banner);
assert(count($lines) === 3);
foreach ($lines as $line) {
    assert(str_contains($line, ' ')); // space between h and i
}

echo "ascii_text tests OK\n";
