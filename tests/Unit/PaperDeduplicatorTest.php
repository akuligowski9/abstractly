<?php

namespace Tests\Unit;

use App\Services\PaperDeduplicator;
use Tests\TestCase;

class PaperDeduplicatorTest extends TestCase
{
    private PaperDeduplicator $dedup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dedup = new PaperDeduplicator();
    }

    // ------------------------------------------------------------------
    // normalizeUrl — null returns
    // ------------------------------------------------------------------

    public function test_normalize_url_returns_null_for_hash(): void
    {
        $this->assertNull($this->dedup->normalizeUrl('#'));
    }

    public function test_normalize_url_returns_null_for_empty_string(): void
    {
        $this->assertNull($this->dedup->normalizeUrl(''));
    }

    public function test_normalize_url_returns_null_for_whitespace(): void
    {
        $this->assertNull($this->dedup->normalizeUrl('   '));
    }

    // ------------------------------------------------------------------
    // normalizeUrl — transformations
    // ------------------------------------------------------------------

    public function test_normalize_url_lowercases(): void
    {
        $this->assertSame(
            'https://arxiv.org/abs/2501.12345',
            $this->dedup->normalizeUrl('https://ArXiv.org/abs/2501.12345')
        );
    }

    public function test_normalize_url_upgrades_http_to_https(): void
    {
        $this->assertSame(
            'https://arxiv.org/abs/2501.12345',
            $this->dedup->normalizeUrl('http://arxiv.org/abs/2501.12345')
        );
    }

    public function test_normalize_url_strips_trailing_slash(): void
    {
        $this->assertSame(
            'https://arxiv.org/abs/2501.12345',
            $this->dedup->normalizeUrl('https://arxiv.org/abs/2501.12345/')
        );
    }

    public function test_normalize_url_strips_arxiv_version_suffix(): void
    {
        $this->assertSame(
            'https://arxiv.org/abs/2501.12345',
            $this->dedup->normalizeUrl('https://arxiv.org/abs/2501.12345v3')
        );
    }

    public function test_normalize_url_strips_arxiv_v1(): void
    {
        $this->assertSame(
            'https://arxiv.org/abs/2501.12345',
            $this->dedup->normalizeUrl('https://arxiv.org/abs/2501.12345v1')
        );
    }

    public function test_normalize_url_preserves_non_arxiv_url(): void
    {
        $this->assertSame(
            'https://doi.org/10.1101/2024.01.01.000001',
            $this->dedup->normalizeUrl('https://doi.org/10.1101/2024.01.01.000001')
        );
    }

    public function test_normalize_url_preserves_doi_with_version_like_segment(): void
    {
        $this->assertSame(
            'https://doi.org/10.1038/s41586-024-00001-1',
            $this->dedup->normalizeUrl('https://doi.org/10.1038/s41586-024-00001-1')
        );
    }

    // ------------------------------------------------------------------
    // dedup — fast paths
    // ------------------------------------------------------------------

    public function test_dedup_empty_input_returns_empty(): void
    {
        $this->assertSame([], $this->dedup->dedup([]));
    }

    public function test_dedup_single_source_returns_unchanged(): void
    {
        $input = [
            'Source A' => [
                ['title' => 'Paper 1', 'url' => 'https://arxiv.org/abs/2501.00001', 'summary' => 'Abstract 1'],
                ['title' => 'Paper 2', 'url' => 'https://arxiv.org/abs/2501.00002', 'summary' => 'Abstract 2'],
            ],
        ];

        $this->assertSame($input, $this->dedup->dedup($input));
    }

    // ------------------------------------------------------------------
    // dedup — core behavior
    // ------------------------------------------------------------------

    public function test_dedup_removes_single_duplicate(): void
    {
        $input = [
            'Source A' => [
                ['title' => 'Paper 1', 'url' => 'https://arxiv.org/abs/2501.00001', 'summary' => 'A1'],
            ],
            'Source B' => [
                ['title' => 'Paper 1 copy', 'url' => 'https://arxiv.org/abs/2501.00001', 'summary' => 'B1'],
            ],
        ];

        $result = $this->dedup->dedup($input);

        $this->assertCount(1, $result['Source A']);
        $this->assertCount(0, $result['Source B']);
        $this->assertSame('Paper 1', $result['Source A'][0]['title']);
    }

    public function test_dedup_annotates_first_occurrence_with_also_in(): void
    {
        $input = [
            'Source A' => [
                ['title' => 'Paper 1', 'url' => 'https://arxiv.org/abs/2501.00001', 'summary' => 'A1'],
            ],
            'Source B' => [
                ['title' => 'Paper 1 copy', 'url' => 'https://arxiv.org/abs/2501.00001', 'summary' => 'B1'],
            ],
        ];

        $result = $this->dedup->dedup($input);

        $this->assertSame(['Source B'], $result['Source A'][0]['also_in']);
    }

    public function test_dedup_multi_source_duplicate(): void
    {
        $input = [
            'Source A' => [
                ['title' => 'Paper 1', 'url' => 'https://arxiv.org/abs/2501.00001', 'summary' => 'A'],
            ],
            'Source B' => [
                ['title' => 'Paper 1b', 'url' => 'https://arxiv.org/abs/2501.00001', 'summary' => 'B'],
            ],
            'Source C' => [
                ['title' => 'Paper 1c', 'url' => 'https://arxiv.org/abs/2501.00001', 'summary' => 'C'],
            ],
        ];

        $result = $this->dedup->dedup($input);

        $this->assertCount(1, $result['Source A']);
        $this->assertCount(0, $result['Source B']);
        $this->assertCount(0, $result['Source C']);
        $this->assertSame(['Source B', 'Source C'], $result['Source A'][0]['also_in']);
    }

    public function test_dedup_mixed_unique_and_duplicate(): void
    {
        $input = [
            'Source A' => [
                ['title' => 'Unique A', 'url' => 'https://arxiv.org/abs/2501.00001', 'summary' => 'U-A'],
                ['title' => 'Shared',   'url' => 'https://arxiv.org/abs/2501.00099', 'summary' => 'S-A'],
            ],
            'Source B' => [
                ['title' => 'Unique B',   'url' => 'https://arxiv.org/abs/2501.00002', 'summary' => 'U-B'],
                ['title' => 'Shared copy', 'url' => 'https://arxiv.org/abs/2501.00099', 'summary' => 'S-B'],
            ],
        ];

        $result = $this->dedup->dedup($input);

        $this->assertCount(2, $result['Source A']);
        $this->assertCount(1, $result['Source B']);
        $this->assertSame('Unique B', $result['Source B'][0]['title']);
    }

    public function test_dedup_no_also_in_on_unique_items(): void
    {
        $input = [
            'Source A' => [
                ['title' => 'Unique A', 'url' => 'https://arxiv.org/abs/2501.00001', 'summary' => 'A'],
            ],
            'Source B' => [
                ['title' => 'Unique B', 'url' => 'https://arxiv.org/abs/2501.00002', 'summary' => 'B'],
            ],
        ];

        $result = $this->dedup->dedup($input);

        $this->assertArrayNotHasKey('also_in', $result['Source A'][0]);
        $this->assertArrayNotHasKey('also_in', $result['Source B'][0]);
    }

    // ------------------------------------------------------------------
    // dedup — edge cases
    // ------------------------------------------------------------------

    public function test_dedup_hash_urls_always_kept(): void
    {
        $input = [
            'Source A' => [
                ['title' => 'No-URL A', 'url' => '#', 'summary' => 'A'],
            ],
            'Source B' => [
                ['title' => 'No-URL B', 'url' => '#', 'summary' => 'B'],
            ],
        ];

        $result = $this->dedup->dedup($input);

        $this->assertCount(1, $result['Source A']);
        $this->assertCount(1, $result['Source B']);
    }

    public function test_dedup_http_https_normalization(): void
    {
        $input = [
            'Source A' => [
                ['title' => 'Paper HTTP', 'url' => 'http://arxiv.org/abs/2501.00001', 'summary' => 'A'],
            ],
            'Source B' => [
                ['title' => 'Paper HTTPS', 'url' => 'https://arxiv.org/abs/2501.00001', 'summary' => 'B'],
            ],
        ];

        $result = $this->dedup->dedup($input);

        $this->assertCount(1, $result['Source A']);
        $this->assertCount(0, $result['Source B']);
        $this->assertSame(['Source B'], $result['Source A'][0]['also_in']);
    }

    public function test_dedup_arxiv_version_normalization(): void
    {
        $input = [
            'Source A' => [
                ['title' => 'Paper v1', 'url' => 'https://arxiv.org/abs/2501.00001v1', 'summary' => 'A'],
            ],
            'Source B' => [
                ['title' => 'Paper v3', 'url' => 'https://arxiv.org/abs/2501.00001v3', 'summary' => 'B'],
            ],
        ];

        $result = $this->dedup->dedup($input);

        $this->assertCount(1, $result['Source A']);
        $this->assertCount(0, $result['Source B']);
    }

    public function test_dedup_fully_emptied_source(): void
    {
        $input = [
            'Source A' => [
                ['title' => 'Paper 1', 'url' => 'https://arxiv.org/abs/2501.00001', 'summary' => 'A1'],
                ['title' => 'Paper 2', 'url' => 'https://arxiv.org/abs/2501.00002', 'summary' => 'A2'],
            ],
            'Source B' => [
                ['title' => 'Paper 1 dup', 'url' => 'https://arxiv.org/abs/2501.00001', 'summary' => 'B1'],
                ['title' => 'Paper 2 dup', 'url' => 'https://arxiv.org/abs/2501.00002', 'summary' => 'B2'],
            ],
        ];

        $result = $this->dedup->dedup($input);

        $this->assertCount(2, $result['Source A']);
        $this->assertCount(0, $result['Source B']);
    }

    public function test_dedup_doi_urls_deduped(): void
    {
        $input = [
            'Source A' => [
                ['title' => 'DOI Paper', 'url' => 'https://doi.org/10.1101/2024.01.01.000001', 'summary' => 'A'],
            ],
            'Source B' => [
                ['title' => 'DOI Paper dup', 'url' => 'https://doi.org/10.1101/2024.01.01.000001', 'summary' => 'B'],
            ],
        ];

        $result = $this->dedup->dedup($input);

        $this->assertCount(1, $result['Source A']);
        $this->assertCount(0, $result['Source B']);
        $this->assertSame(['Source B'], $result['Source A'][0]['also_in']);
    }

    public function test_dedup_empty_url_items_always_kept(): void
    {
        $input = [
            'Source A' => [
                ['title' => 'No URL A', 'url' => '', 'summary' => 'A'],
            ],
            'Source B' => [
                ['title' => 'No URL B', 'url' => '', 'summary' => 'B'],
            ],
        ];

        $result = $this->dedup->dedup($input);

        $this->assertCount(1, $result['Source A']);
        $this->assertCount(1, $result['Source B']);
    }

    public function test_dedup_preserves_source_order(): void
    {
        $input = [
            'First'  => [['title' => 'P1', 'url' => 'https://arxiv.org/abs/2501.00001', 'summary' => '1']],
            'Second' => [['title' => 'P2', 'url' => 'https://arxiv.org/abs/2501.00002', 'summary' => '2']],
            'Third'  => [['title' => 'P3', 'url' => 'https://arxiv.org/abs/2501.00003', 'summary' => '3']],
        ];

        $result = $this->dedup->dedup($input);

        $this->assertSame(['First', 'Second', 'Third'], array_keys($result));
    }

    public function test_dedup_also_in_survives_merge_with_plus_operator(): void
    {
        // Simulates what AiSummarizer::mergeBatchSummaries does: $it + $byIndex[$idx]
        // Left-side precedence means also_in on $it is preserved.
        $item = [
            'title'   => 'Paper',
            'url'     => 'https://arxiv.org/abs/2501.00001',
            'summary' => 'Abstract',
            'also_in' => ['Source B'],
        ];

        $aiFields = [
            'eli5'     => 'Simple explanation',
            'swe'      => 'Engineer angle',
            'investor' => 'Investment angle',
        ];

        $merged = $item + $aiFields;

        $this->assertSame(['Source B'], $merged['also_in']);
        $this->assertSame('Simple explanation', $merged['eli5']);
    }
}
