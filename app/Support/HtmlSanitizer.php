<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Allowlist HTML sanitizer.
 *
 * Admin-authored page bodies are rendered as HTML on the public site, which
 * shares an origin with the back office. Any script that survives here can act
 * as the signed-in administrator, so the sanitizer is a security boundary, not
 * a formatting convenience: everything not explicitly permitted is removed.
 */
class HtmlSanitizer
{
    /** Tags that may appear in published content. */
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'code', 'pre',
        'h2', 'h3', 'h4', 'blockquote', 'ul', 'ol', 'li', 'a', 'hr',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
    ];

    /** Attributes permitted on each tag. Everything else is stripped. */
    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'title'],
        'th' => ['colspan', 'rowspan'],
        'td' => ['colspan', 'rowspan'],
    ];

    /** Attributes allowed on any tag (none — deliberately). */
    private const GLOBAL_ATTRIBUTES = [];

    /** URL schemes that may appear in href. */
    private const ALLOWED_SCHEMES = ['http', 'https', 'mailto'];

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');

        // libxml emits warnings for HTML5 tags; suppress and keep parsing.
        $previous = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<?xml encoding="UTF-8"?><div id="cch-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('cch-root');

        if (! $root instanceof DOMElement) {
            return '';
        }

        self::scrub($root);

        $output = '';

        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return trim($output);
    }

    /** Recursively walk the tree, removing anything not on the allowlist. */
    private static function scrub(DOMNode $node): void
    {
        // Iterate over a snapshot: removing nodes while traversing the live
        // NodeList would skip siblings.
        $children = iterator_to_array($node->childNodes);

        foreach ($children as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);

                // Comments and processing instructions are dropped outright.
                if ($child->nodeType !== XML_ELEMENT_NODE) {
                    $node->removeChild($child);
                    continue;
                }

                if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                    // Unwrap unknown elements so their text survives but their
                    // markup (and any event handlers) does not.
                    if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'form'], true)) {
                        $node->removeChild($child);
                        continue;
                    }

                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }

                    $node->removeChild($child);
                    continue;
                }

                self::cleanAttributes($child, $tag);
                self::scrub($child);

                continue;
            }

            if ($child->nodeType === XML_COMMENT_NODE) {
                $node->removeChild($child);
            }
        }
    }

    private static function cleanAttributes(DOMElement $element, string $tag): void
    {
        $allowed = array_merge(self::GLOBAL_ATTRIBUTES, self::ALLOWED_ATTRIBUTES[$tag] ?? []);

        $attributes = iterator_to_array($element->attributes);

        foreach ($attributes as $attribute) {
            $name = strtolower($attribute->name);

            // Drop the entire attribute if it is not explicitly permitted.
            // This is what removes onload=, onerror=, style=, and friends.
            if (! in_array($name, $allowed, true)) {
                $element->removeAttribute($attribute->name);
                continue;
            }

            if ($name === 'href') {
                $value = trim($attribute->value);

                // Reject javascript:, data:, vbscript: and control-character
                // smuggling such as "java\tscript:".
                $normalised = preg_replace('/[\x00-\x20]+/', '', strtolower($value)) ?? '';

                $scheme = str_contains($normalised, ':')
                    ? strstr($normalised, ':', true)
                    : null;

                if ($scheme !== null && ! in_array($scheme, self::ALLOWED_SCHEMES, true)) {
                    $element->removeAttribute('href');
                    continue;
                }

                // External links must not be able to reach back into the app.
                if ($scheme !== null) {
                    $element->setAttribute('rel', 'noopener noreferrer nofollow');
                }
            }
        }
    }

    /**
     * Plain-text extraction, used for meta descriptions and previews.
     */
    public static function toText(?string $html): string
    {
        return trim(html_entity_decode(strip_tags(self::clean($html))));
    }
}
