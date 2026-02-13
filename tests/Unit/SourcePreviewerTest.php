<?php

namespace Tests\Unit;

use App\Services\SourcePreviewer;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SourcePreviewerTest extends TestCase
{
    private SourcePreviewer $previewer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previewer = new SourcePreviewer();
    }

    // ------------------------------------------------------------------
    // fetchArxiv (Atom XML)
    // ------------------------------------------------------------------

    public function test_fetch_arxiv_parses_atom_entries(): void
    {
        Http::fake([
            'export.arxiv.org/*' => Http::response($this->arxivAtomXml(), 200),
        ]);

        $items = $this->previewer->fetch([
            'key' => 'arxiv_math',
            'url' => 'https://export.arxiv.org/api/query?search_query=cat:math.AG&max_results=5',
        ], 5);

        $this->assertCount(2, $items);

        $this->assertSame('Paper Alpha', $items[0]['title']);
        $this->assertSame('https://arxiv.org/abs/2401.00001', $items[0]['url']);
        $this->assertStringContainsString('abstract alpha', $items[0]['summary']);

        $this->assertSame('Paper Beta', $items[1]['title']);
    }

    public function test_fetch_arxiv_respects_limit(): void
    {
        Http::fake([
            'export.arxiv.org/*' => Http::response($this->arxivAtomXml(), 200),
        ]);

        $items = $this->previewer->fetch([
            'key' => 'arxiv_math',
            'url' => 'https://export.arxiv.org/api/query?search_query=cat:math.AG&max_results=5',
        ], 1);

        $this->assertCount(1, $items);
        $this->assertSame('Paper Alpha', $items[0]['title']);
    }

    public function test_fetch_arxiv_empty_feed_returns_empty_array(): void
    {
        $emptyFeed = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"></feed>
XML;

        Http::fake([
            'export.arxiv.org/*' => Http::response($emptyFeed, 200),
        ]);

        $items = $this->previewer->fetch([
            'key' => 'arxiv_math',
            'url' => 'https://export.arxiv.org/api/query?search_query=cat:math.AG&max_results=5',
        ]);

        $this->assertSame([], $items);
    }

    // ------------------------------------------------------------------
    // fetchRxivJson (bioRxiv / medRxiv)
    // ------------------------------------------------------------------

    public function test_fetch_biorxiv_parses_collection(): void
    {
        Http::fake([
            'api.biorxiv.org/*' => Http::response([
                'collection' => [
                    [
                        'title'    => 'Bio Paper One',
                        'abstract' => 'Abstract for bio paper one.',
                        'doi'      => '10.1101/2024.01.01.000001',
                    ],
                    [
                        'title'    => 'Bio Paper Two',
                        'abstract' => 'Abstract for bio paper two.',
                        'doi'      => '10.1101/2024.01.01.000002',
                    ],
                ],
            ], 200),
        ]);

        $items = $this->previewer->fetch([
            'key' => 'biorxiv_recent',
            'url' => 'https://api.biorxiv.org/details/biorxiv/2024-01-01/2024-01-02',
        ]);

        $this->assertCount(2, $items);
        $this->assertSame('Bio Paper One', $items[0]['title']);
        $this->assertSame('https://doi.org/10.1101/2024.01.01.000001', $items[0]['url']);
        $this->assertSame('Abstract for bio paper one.', $items[0]['summary']);
    }

    public function test_fetch_medrxiv_uses_rxiv_parser(): void
    {
        Http::fake([
            'api.biorxiv.org/*' => Http::response([
                'collection' => [
                    [
                        'title'    => 'Med Paper',
                        'abstract' => 'Medical abstract.',
                        'doi'      => '10.1101/med.001',
                    ],
                ],
            ], 200),
        ]);

        $items = $this->previewer->fetch([
            'key' => 'medrxiv_recent',
            'url' => 'https://api.biorxiv.org/details/medrxiv/2024-01-01/2024-01-02',
        ]);

        $this->assertCount(1, $items);
        $this->assertSame('Med Paper', $items[0]['title']);
    }

    // ------------------------------------------------------------------
    // fetchOsfPreprints (PsyArXiv, SocArXiv, EdArXiv)
    // ------------------------------------------------------------------

    public function test_fetch_osf_preprints_parses_jsonapi(): void
    {
        Http::fake([
            'api.osf.io/*' => Http::response([
                'data' => [
                    [
                        'attributes' => [
                            'title'       => 'Psychology Paper',
                            'description' => 'A study on cognition.',
                            'doi'         => '10.31234/osf.io/abc12',
                        ],
                        'links' => ['html' => 'https://osf.io/preprints/psyarxiv/abc12'],
                    ],
                ],
            ], 200),
        ]);

        $items = $this->previewer->fetch([
            'key' => 'psyarxiv_cognition',
            'url' => 'https://api.osf.io/v2/preprints/?filter[provider]=psyarxiv',
        ]);

        $this->assertCount(1, $items);
        $this->assertSame('Psychology Paper', $items[0]['title']);
        $this->assertSame('https://doi.org/10.31234/osf.io/abc12', $items[0]['url']);
        $this->assertSame('A study on cognition.', $items[0]['summary']);
    }

    public function test_fetch_osf_falls_back_to_html_link_when_no_doi(): void
    {
        Http::fake([
            'api.osf.io/*' => Http::response([
                'data' => [
                    [
                        'attributes' => [
                            'title'       => 'No DOI Paper',
                            'description' => 'Description here.',
                        ],
                        'links' => ['html' => 'https://osf.io/preprints/socarxiv/xyz99'],
                    ],
                ],
            ], 200),
        ]);

        $items = $this->previewer->fetch([
            'key' => 'socarxiv_general',
            'url' => 'https://api.osf.io/v2/preprints/?filter[provider]=socarxiv',
        ]);

        $this->assertSame('https://osf.io/preprints/socarxiv/xyz99', $items[0]['url']);
    }

    // ------------------------------------------------------------------
    // fetchEuropePmc
    // ------------------------------------------------------------------

    public function test_fetch_europe_pmc_parses_result_list(): void
    {
        Http::fake([
            'www.ebi.ac.uk/*' => Http::response([
                'resultList' => [
                    'result' => [
                        [
                            'title'        => 'PMC Paper',
                            'abstractText' => 'PMC abstract text.',
                            'doi'          => '10.1038/s41586-024-00001-1',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $items = $this->previewer->fetch([
            'key' => 'europepmc_neuro',
            'url' => 'https://www.ebi.ac.uk/europepmc/webservices/rest/search?query=neuroscience',
        ]);

        $this->assertCount(1, $items);
        $this->assertSame('PMC Paper', $items[0]['title']);
        $this->assertSame('https://doi.org/10.1038/s41586-024-00001-1', $items[0]['url']);
        $this->assertSame('PMC abstract text.', $items[0]['summary']);
    }

    // ------------------------------------------------------------------
    // fetchRssOrAtom (fallback)
    // ------------------------------------------------------------------

    public function test_fetch_rss_parses_channel_items(): void
    {
        $rss = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>Test Feed</title>
    <item>
      <title>RSS Item One</title>
      <link>https://example.com/item-1</link>
      <description>RSS description one.</description>
    </item>
    <item>
      <title>RSS Item Two</title>
      <link>https://example.com/item-2</link>
      <description>RSS description two.</description>
    </item>
  </channel>
</rss>
XML;

        Http::fake([
            'example.com/*' => Http::response($rss, 200),
        ]);

        $items = $this->previewer->fetch([
            'key' => 'some_rss_feed',
            'url' => 'https://example.com/feed.xml',
        ]);

        $this->assertCount(2, $items);
        $this->assertSame('RSS Item One', $items[0]['title']);
        $this->assertSame('https://example.com/item-1', $items[0]['url']);
        $this->assertSame('RSS description one.', $items[0]['summary']);
    }

    public function test_fetch_atom_fallback_parses_entries(): void
    {
        $atom = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
  <entry>
    <title>Atom Entry</title>
    <link rel="alternate" href="https://example.com/atom-1"/>
    <summary>Atom summary text.</summary>
  </entry>
</feed>
XML;

        Http::fake([
            'example.com/*' => Http::response($atom, 200),
        ]);

        $items = $this->previewer->fetch([
            'key' => 'unknown_atom_feed',
            'url' => 'https://example.com/atom.xml',
        ]);

        $this->assertCount(1, $items);
        $this->assertSame('Atom Entry', $items[0]['title']);
        $this->assertSame('https://example.com/atom-1', $items[0]['url']);
        $this->assertSame('Atom summary text.', $items[0]['summary']);
    }

    // ------------------------------------------------------------------
    // Error path
    // ------------------------------------------------------------------

    public function test_http_error_throws_request_exception(): void
    {
        Http::fake([
            'example.com/*' => Http::response('Service Unavailable', 503),
        ]);

        // retry(2, 250) on the client throws RequestException after retries
        // are exhausted, before the code's own RuntimeException is reached.
        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        $this->previewer->fetch([
            'key' => 'some_feed',
            'url' => 'https://example.com/feed.xml',
        ]);
    }

    // ------------------------------------------------------------------
    // Fixtures
    // ------------------------------------------------------------------

    private function arxivAtomXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
  <entry>
    <title>Paper Alpha</title>
    <id>https://arxiv.org/abs/2401.00001</id>
    <link rel="alternate" href="https://arxiv.org/abs/2401.00001"/>
    <summary>This is abstract alpha about algebraic geometry.</summary>
  </entry>
  <entry>
    <title>Paper Beta</title>
    <id>https://arxiv.org/abs/2401.00002</id>
    <link rel="alternate" href="https://arxiv.org/abs/2401.00002"/>
    <summary>This is abstract beta about topology.</summary>
  </entry>
</feed>
XML;
    }
}
