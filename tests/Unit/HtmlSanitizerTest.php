<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_it_strips_script_tags(): void
    {
        $result = HtmlSanitizer::clean('<p>Halo</p><script>alert(1)</script>');

        $this->assertStringContainsString('Halo', $result);
        $this->assertStringNotContainsString('<script', $result);
        $this->assertStringNotContainsString('alert', $result);
    }

    public function test_it_removes_event_handler_attributes(): void
    {
        $result = HtmlSanitizer::clean('<p onclick="steal()">Teks</p>');

        $this->assertStringNotContainsString('onclick', $result);
        $this->assertStringContainsString('Teks', $result);
    }

    public function test_it_drops_javascript_hrefs(): void
    {
        $result = HtmlSanitizer::clean('<a href="javascript:alert(1)">tautan</a>');

        $this->assertStringNotContainsString('javascript:', $result);
    }

    public function test_it_keeps_safe_links_and_hardens_them(): void
    {
        $result = HtmlSanitizer::clean('<a href="https://example.com">tautan</a>');

        $this->assertStringContainsString('href="https://example.com"', $result);
        $this->assertStringContainsString('rel="noopener nofollow noreferrer"', $result);
    }

    public function test_it_preserves_allowed_formatting_tags(): void
    {
        $result = HtmlSanitizer::clean('<h2>Judul</h2><ul><li>Satu</li></ul><strong>tebal</strong>');

        $this->assertStringContainsString('<h2>Judul</h2>', $result);
        $this->assertStringContainsString('<li>Satu</li>', $result);
        $this->assertStringContainsString('<strong>tebal</strong>', $result);
    }

    public function test_it_normalises_trix_div_blocks_into_paragraphs(): void
    {
        // Trix serialises paragraph blocks as <div>...</div>.
        $result = HtmlSanitizer::clean('<div>Paragraf satu</div><div>Paragraf dua</div>');

        $this->assertStringContainsString('<p>Paragraf satu</p>', $result);
        $this->assertStringContainsString('<p>Paragraf dua</p>', $result);
        $this->assertStringNotContainsString('<div>', $result);
    }

    public function test_it_downgrades_trix_h1_to_h2(): void
    {
        // Trix has a single heading level (<h1>); keep the page's title as the only <h1>.
        $result = HtmlSanitizer::clean('<h1>Sub Judul</h1>');

        $this->assertStringContainsString('<h2>Sub Judul</h2>', $result);
        $this->assertStringNotContainsString('<h1', $result);
    }

    public function test_it_keeps_lists_from_trix_intact(): void
    {
        $result = HtmlSanitizer::clean('<ul><li>Satu</li><li>Dua</li></ul>');

        $this->assertStringContainsString('<ul><li>Satu</li><li>Dua</li></ul>', $result);
    }
}
