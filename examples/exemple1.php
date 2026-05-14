<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Tivins\Tui\TermColor;

echo TermColor::Green->fmt('Hello, world!') . PHP_EOL;

// Voir aussi : php examples/frame.php (composant Frame).
