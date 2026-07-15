<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Minimal, dependency-free HTML sanitizer for the single-admin rich-text article
 * body (authored with the Trix editor). It parses the HTML, drops any tag not on
 * the allow-list, strips every event handler / style / unknown attribute, permits
 * only http(s)/mailto links, and normalises Trix output (its <div> blocks become
 * <p>, and its single <h1> heading level becomes <h2> so the page keeps one <h1>).
 * Content is authored only by the trusted administrator; this is defense-in-depth
 * against stored XSS.
 */
class HtmlSanitizer
{
    /** @var array<string, list<string>> tag => allowed attributes */
    private const ALLOWED = [
        'p' => [],
        'br' => [],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'u' => [],
        'del' => [],
        's' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'h2' => [],
        'h3' => [],
        'blockquote' => [],
        'pre' => [],
        'code' => [],
        'a' => ['href', 'title', 'target', 'rel'],
    ];

    /** Tags removed entirely, including their text content. */
    private const DROP = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'svg', 'noscript'];

    /** Tags renamed to a safe equivalent. */
    private const RENAME = ['h1' => 'h2'];

    /** Block-level tags that must not be wrapped in a <p>. */
    private const BLOCK = ['ul', 'ol', 'blockquote', 'pre', 'h2', 'h3', 'div', 'p'];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');

        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="__root__">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('__root__');

        if (! $root) {
            return '';
        }

        self::sanitizeChildren($root);

        $output = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $output .= $dom->saveHTML($child);
        }

        return trim($output);
    }

    private static function sanitizeChildren(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                self::sanitizeElement($child);
            } elseif ($child->nodeType === XML_COMMENT_NODE) {
                $child->parentNode?->removeChild($child);
            }
        }
    }

    private static function sanitizeElement(DOMElement $element): void
    {
        $tag = strtolower($element->nodeName);

        if (in_array($tag, self::DROP, true)) {
            $element->parentNode?->removeChild($element);

            return;
        }

        // Trix emits <div> blocks; turn each into a <p>, unless it wraps other
        // block-level elements (in which case just unwrap it).
        if ($tag === 'div') {
            if (self::hasBlockChild($element)) {
                self::sanitizeChildren($element);
                self::unwrap($element);
            } else {
                $renamed = self::renameElement($element, 'p');
                self::sanitizeChildren($renamed);
            }

            return;
        }

        if (isset(self::RENAME[$tag])) {
            $element = self::renameElement($element, self::RENAME[$tag]);
            $tag = strtolower($element->nodeName);
        }

        if (! array_key_exists($tag, self::ALLOWED)) {
            self::sanitizeChildren($element);
            self::unwrap($element);

            return;
        }

        $allowedAttributes = self::ALLOWED[$tag];

        foreach (iterator_to_array($element->attributes ?? []) as $attribute) {
            $name = strtolower($attribute->nodeName);

            if (! in_array($name, $allowedAttributes, true)) {
                $element->removeAttribute($attribute->nodeName);

                continue;
            }

            if ($name === 'href' && ! self::isSafeUrl($attribute->nodeValue)) {
                $element->removeAttribute($attribute->nodeName);
            }
        }

        if ($tag === 'a' && $element->getAttribute('href') !== '') {
            $element->setAttribute('rel', 'noopener nofollow noreferrer');
        }

        self::sanitizeChildren($element);
    }

    private static function hasBlockChild(DOMElement $element): bool
    {
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement && in_array(strtolower($child->nodeName), self::BLOCK, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Replace an element with a new one of a different tag, moving its children.
     */
    private static function renameElement(DOMElement $element, string $newTag): DOMElement
    {
        $new = $element->ownerDocument->createElement($newTag);

        while ($element->firstChild) {
            $new->appendChild($element->firstChild);
        }

        $element->parentNode?->replaceChild($new, $element);

        return $new;
    }

    private static function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (! $parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    private static function isSafeUrl(?string $url): bool
    {
        $url = trim((string) $url);

        if ($url === '') {
            return false;
        }

        if (str_starts_with($url, '#') || str_starts_with($url, '/')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https', 'mailto'], true);
    }
}
