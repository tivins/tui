<?php

declare(strict_types=1);

namespace Tivins\Tui;
/*
╭─╮  ╭╮   ╭─╴╶┬╮╭─╴╭─╴╭─╴╷ ╷╷ ╭╮╷╭ ╷  ╭┬╮╭╮╷╭─╮╭─╮╭─╮╭─╮╭─╮╶┬╴╷ ╷╷ ╷╷ ╷╷ ╷╷ ╷╶─╮╭─╮╶╮ ╭─╮╭─╮╷ ╷╭─╴╭─╮╭─╮╭─╮╭─╮╷     ╷ ╷╭┬╮╭╮╷╭╮     ╷╷╷ ╭╴╭╴╭─    ╷ ╮╷ ╭─╮╶╮ ─╮╶╮  ╷ ╷  
├─┤  ├┴╮  │   ││├╴ ├╴ │╶╮├─┤│  │├┴╮│  ││││╰┤│ │├─╯│╮│├┬╯╰─╮ │ │ ││╭╯│╷│╭┼╯╰┬╯╭─╯│││ │ ╭─╯╶─┤╰─┤╰─╮├─╮  │├─┤╰─┤╵╵ ╵  ╶┼╴╰┼╮╭─╯│╶┼╴╭─╯   ╶┤ │ │  ╶─╴│  ╰╮│├╯ │  │ ├╴╭╯╶┼╴ 
╵ ╵  ╰─╯  ╰─╴╶┴╯╰─╴╵  ╰─╯╵ ╵╵╰─╯╵ ╵╰─╴╵ ╵╵ ╵╰─╯╵  ╰┴╯╵╰╴╰─╯ ╵ ╰─╯╰╯ ╰┴╯╵ ╵ ╵ ╰─╴╰─╯╶┴╴╰─╴╰─╯  ╵╰─╯╰─╯  ╵╰─╯╰─╯╵╵ ╯ ╯╵ ╵╰┴╯╵╰╯╰─╯        ╰╴╰╴╰─    ╵   ╵╰─╴╶╯ ─╯╶╯ ╵  ╵ ╵
abcdefghijklmnopqrstuvwxyz0123456789!:;,*$%&~"'{([-|`\@)]}/+.


Source : https://patorjk.com/software/taag/#p=display&f=Future+Smooth&t=abcdefghijklmnopqrstuvwxyz0123456789%21%3A%3B%2C*%24%25%26%7E%22%27%7B%28%5B-%7C%60%5C%40%29%5D%7D%2F%2B.&x=none&v=4&h=4&w=80&we=false
*/

class AsciiText
{
    public const A = ["╭─╮", "├─┤", "╵ ╵"];
    public const B = ["╭╮ ", "├┴╮", "╰─╯"];
    public const C = ["╭─╴", "│  ", "╰─╴"];

    public static function get(string $name): array
    {
        $letters = str_split($name);
        $letters = array_map(fn($letter) => self::{$letter} ?? ['','',''], $letters);
        return $letters;
    }

    public static function toString(array $letters): string
    {
        for ($i=0;$i<3;$i++) {
            $line = '';
            foreach ($letters as $letter) {
                $line .= $letter[$i];
            }
            $lines[] = $line;
        }
        return implode(PHP_EOL, $lines);
    }
}