<?php

namespace App\Support;

/**
 * Minimal Markdown → HTML converter for seeded page bodies.
 *
 * Deliberately narrow: it supports only the constructs the content uses, and
 * its output is always passed through HtmlSanitizer afterwards. It never emits
 * raw input — every fragment is escaped before being wrapped in a tag.
 */
class Markdown
{
    public static function toHtml(string $markdown): string
    {
        $escaped = htmlspecialchars($markdown, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $inline = static fn (string $text): string => preg_replace(
            ['/\*\*(.+?)\*\*/s', '/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '/`(.+?)`/s'],
            ['<strong>$1</strong>', '<em>$1</em>', '<code>$1</code>'],
            $text
        );

        $blocks = preg_split('/\n{2,}/', trim($escaped)) ?: [];

        $html = [];

        foreach ($blocks as $block) {
            $block = trim($block);

            if ($block === '') {
                continue;
            }

            $lines = preg_split('/\n/', $block) ?: [];

            $isList = $lines !== [] && count(array_filter(
                $lines,
                fn ($line) => ! str_starts_with(ltrim($line), '- '),
            )) === 0;

            if ($isList) {
                $items = array_map(
                    fn ($line) => '<li>'.$inline(ltrim($line, " -\t")).'</li>',
                    $lines
                );

                $html[] = '<ul>'.implode('', $items).'</ul>';

                continue;
            }

            if (preg_match('/^(#{1,3})\s+(.*)$/s', $block, $matches) === 1) {
                $level = min(4, strlen($matches[1]) + 1);
                $html[] = "<h{$level}>".$inline($matches[2])."</h{$level}>";

                continue;
            }

            $html[] = '<p>'.$inline(str_replace("\n", '<br />', $block)).'</p>';
        }

        return implode("\n", $html);
    }
}
