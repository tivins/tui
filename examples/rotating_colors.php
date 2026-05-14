<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Tivins\Tui\Common;
use Tivins\Tui\RotatingColors;
use Tivins\Tui\Terminal;
use Tivins\Tui\Throbber;

$text = 'thinking...';

echo RotatingColors::render($text, 0) . PHP_EOL;
echo RotatingColors::render($text, 1) . PHP_EOL;
echo RotatingColors::render($text, 2) . PHP_EOL;

echo PHP_EOL . 'Throbber + {rotating_message} (Ctrl+C pour quitter) :' . PHP_EOL;

$t = new Terminal();
$t->cursorHide();
try {
    $th = (new Throbber())
        ->style(Throbber::STYLE_PIPE)
        ->message($text)
        ->template('{spinner} {rotating_message}');

    $end = microtime(true) + 4.0;
    while (microtime(true) < $end) {
        echo $t->carriageReturn() . $t->eraseLine() . $th->render();
        Common::flushOutput();
        flush();
        $th->tick();
        usleep(120_000);
    }
    echo PHP_EOL;
} finally {
    $t->cursorShow();
}
