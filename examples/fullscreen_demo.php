<?php

declare(strict_types=1);

/**
 * Démo plein écran : tampon alternatif, rafraîchissement par {@see \Tivins\Tui\Terminal::cursorHome()},
 * {@see \Tivins\Tui\Layout} (cadres côte à côte), {@see \Tivins\Tui\Frame} (dont cadre dans cadre),
 * {@see \Tivins\Tui\Throbber}, {@see \Tivins\Tui\RotatingColors}, {@see \Tivins\Tui\ProgressBar}.
 *
 * Exécuter : php examples/fullscreen_demo.php
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Tivins\Tui\Ansi;
use Tivins\Tui\Common;
use Tivins\Tui\Console;
use Tivins\Tui\Frame;
use Tivins\Tui\Layout;
use Tivins\Tui\ProgressBar;
use Tivins\Tui\RotatingColors;
use Tivins\Tui\Terminal;
use Tivins\Tui\TermColor;
use Tivins\Tui\Throbber;

/** @var list<TermColor|int> */
const DEMO_TITLE_PALETTE = [
    TermColor::LightCyan,
    TermColor::Cyan,
    TermColor::LightBlue,
    TermColor::LightMagenta,
    TermColor::LightYellow,
    TermColor::White,
];

function demo_center_line(string $line, int $cols): string
{
    $w = Ansi::displayWidth($line);
    $pad = max(0, intdiv($cols - $w, 2));

    return str_repeat(' ', $pad) . $line;
}

$size = Terminal::screenSize();
$cols = $size['cols'] ?? 80;
$rows = $size['rows'] ?? 24;

$gapBetweenFrames = 2;
$pairInner = max(14, intdiv($cols - 4 - $gapBetweenFrames, 2));
$wideInner = max(28, $cols - 4);
$barLen = max(12, min(36, $wideInner - 28));

$pairPadV = $rows >= 28 ? 1 : 0;
$pairPadH = 1;

$thLeft = (new Throbber())
    ->style(Throbber::STYLE_BRAILLE)
    ->message('embed · rotate · frames')
    ->template('{spinner}  {rotating_message}')
    ->start();

$thRight = (new Throbber())
    ->style(Throbber::STYLE_DOTS)
    ->message('side by side')
    ->percent(0.0)
    ->barWidth(max(8, intdiv($pairInner, 3)))
    ->template('{spinner}  {message}  ' . TermColor::LightGreen->fmt('{bar}') . '  ' . TermColor::White->fmt('{percent}'))
    ->start();

$thNested = (new Throbber())
    ->style(Throbber::STYLE_LINE)
    ->message('nested throbber')
    ->template(TermColor::LightYellow->fmt('{spinner}') . '  {message}{trail}')
    ->start();

$iterations = Console::stdinIsTty() ? 280 : 25;
$frameSleepUs = 55_000;

Terminal::enterAlternateScreen();
Terminal::cursorHide();

try {
    for ($i = 0; $i < $iterations; $i++) {
        $wave = RotatingColors::render('··· fluid gradient ···', $i * 2);

        $leftBody = implode("\n", [
            $thLeft->render(),
            '',
            $wave,
        ]);

        $rightPct = fmod($i * 0.7, 101.0);
        $thRight->percent($rightPct);

        $rightBody = implode("\n", [
            $thRight->render(),
            '',
            TermColor::Gray->fmt('tick : ') . TermColor::LightGray->fmt((string) $i),
        ]);

        $leftFrame = (string) Frame::from($leftBody)
            ->borderStyle(Frame::STYLE_DOUBLE)
            ->borderColor(TermColor::Blue)
            ->contentColor(TermColor::LightGray)
            ->title('throbber + rotating', Frame::ALIGN_LEFT)
            ->titleColor(TermColor::LightCyan)
            ->paddingVertical($pairPadV)
            ->paddingHorizontal($pairPadH)
            ->minInnerWidth($pairInner);

        $rightFrame = (string) Frame::from($rightBody)
            ->borderStyle(Frame::STYLE_HEAVY)
            ->borderColor(TermColor::Magenta)
            ->contentColor(TermColor::LightGray)
            ->title('throbber + bar', Frame::ALIGN_RIGHT)
            ->titleColor(TermColor::LightMagenta)
            ->paddingVertical($pairPadV)
            ->paddingHorizontal($pairPadH)
            ->minInnerWidth($pairInner);

        $topRow = Layout::horizontal([$leftFrame, $rightFrame], $gapBetweenFrames);
        $topRow = demo_center_line($topRow, $cols);

        $gpuPct = fmod(18.0 + $i * 1.1, 101.0);
        $queuePct = fmod(62.0 + $i * 0.55, 101.0);

        $nestedLine = (string) Frame::from($thNested->render())
            ->borderStyle(Frame::STYLE_SINGLE)
            ->borderColor(TermColor::Gray)
            ->contentColor(TermColor::White)
            ->title('inner', Frame::ALIGN_CENTER)
            ->titleColor(TermColor::Yellow)
            ->padding(0)
            ->minInnerWidth(max(22, $wideInner - 8));

        $wideContent = implode("\n", [
            TermColor::LightGreen->fmt('build ') . TermColor::LightGreen->fmt(ProgressBar::render($gpuPct, $barLen))
                . '  ' . TermColor::White->fmt(sprintf('%3d%%', (int) round($gpuPct))),
            TermColor::LightBlue->fmt('queue ') . TermColor::LightBlue->fmt(ProgressBar::render($queuePct, $barLen))
                . '  ' . TermColor::White->fmt(sprintf('%3d%%', (int) round($queuePct))),
            '',
            $nestedLine,
        ]);

        $wideFrame = (string) Frame::from($wideContent)
            ->borderStyle(Frame::STYLE_ROUNDED)
            ->borderColor(TermColor::Cyan)
            ->contentColor(null)
            ->title('progress bars · cadre imbriqué', Frame::ALIGN_CENTER)
            ->titleColor(TermColor::LightYellow)
            ->bottomTitle('ProgressBar::render()', Frame::ALIGN_LEFT)
            ->bottomTitleColor(TermColor::Gray)
            ->paddingVertical($rows >= 30 ? 1 : 0)
            ->paddingHorizontal(1)
            ->minInnerWidth($wideInner);

        $wideCentered = demo_center_line($wideFrame, $cols);

        $titleLine = demo_center_line(
            RotatingColors::render(' tui · fullscreen · frames · throbbers ', $i, DEMO_TITLE_PALETTE),
            $cols,
        );

        $meta = demo_center_line(
            TermColor::Gray->fmt(
                sprintf('rows %d × cols %d  ·  ', $rows, $cols)
                    . 'Layout::horizontal()  ·  alternate screen'
            ),
            $cols,
        );

        $screen = implode("\n", [
            $titleLine,
            $meta,
            '',
            $topRow,
            '',
            $wideCentered,
        ]);

        Terminal::cursorHome();
        echo $screen;

        $footer = demo_center_line(
            TermColor::Gray->fmt(
                Console::stdinIsTty()
                    ? 'Entrée à la fin de l’animation pour quitter'
                    : 'stdin non interactif — sortie automatique'
            ),
            $cols,
        );
        Terminal::cursorMove($rows, 1);
        Terminal::eraseLine();
        echo $footer;

        Common::flushOutput();

        $thLeft->tick();
        $thRight->tick();
        $thNested->tick();

        usleep($frameSleepUs);
    }

    Terminal::cursorShow();
    Terminal::cursorMove($rows, 1);
    Terminal::eraseLine();
    echo demo_center_line(TermColor::LightGray->fmt('Fin de la démo — '), $cols) . "\n";
    echo demo_center_line(
        TermColor::Gray->fmt(Console::stdinIsTty() ? 'Entrée pour fermer' : ''),
        $cols,
    );

    if (Console::stdinIsTty()) {
        Console::readLine();
    }
} finally {
    Terminal::cursorShow();
    Terminal::leaveAlternateScreen();
}
