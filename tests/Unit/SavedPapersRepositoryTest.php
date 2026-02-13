<?php

namespace Tests\Unit;

use App\Services\SavedPapersRepository;
use Tests\TestCase;

class SavedPapersRepositoryTest extends TestCase
{
    private string $tempPath;
    private SavedPapersRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempPath = sys_get_temp_dir() . '/saved-papers-test-' . uniqid() . '.json';
        $this->repo = new SavedPapersRepository($this->tempPath);
    }

    protected function tearDown(): void
    {
        @unlink($this->tempPath);
        parent::tearDown();
    }

    private function makePaper(array $overrides = []): array
    {
        return array_merge([
            'url'        => 'https://arxiv.org/abs/2501.00001',
            'title'      => 'Test Paper',
            'summary'    => 'A summary.',
            'eli5'       => 'Simple explanation.',
            'swe'        => 'Engineer angle.',
            'investor'   => 'Investment angle.',
            'also_in'    => [],
            'discipline' => 'Mathematics',
            'source'     => 'arXiv — Mathematics (all)',
        ], $overrides);
    }

    // ------------------------------------------------------------------
    // Empty state
    // ------------------------------------------------------------------

    public function test_all_returns_empty_array_when_no_file(): void
    {
        $this->assertSame([], $this->repo->all());
    }

    public function test_count_returns_zero_when_empty(): void
    {
        $this->assertSame(0, $this->repo->count());
    }

    public function test_has_returns_false_when_empty(): void
    {
        $this->assertFalse($this->repo->has('https://arxiv.org/abs/2501.00001'));
    }

    public function test_saved_urls_returns_empty_when_empty(): void
    {
        $this->assertSame([], $this->repo->savedUrls());
    }

    // ------------------------------------------------------------------
    // Save
    // ------------------------------------------------------------------

    public function test_save_persists_paper(): void
    {
        $paper = $this->makePaper();
        $this->repo->save($paper);

        $this->assertSame(1, $this->repo->count());
        $this->assertTrue($this->repo->has($paper['url']));
    }

    public function test_save_adds_saved_at_timestamp(): void
    {
        $paper = $this->makePaper();
        $this->repo->save($paper);

        $all = $this->repo->all();
        $this->assertArrayHasKey('saved_at', $all[0]);
        $this->assertNotEmpty($all[0]['saved_at']);
    }

    public function test_save_preserves_all_fields(): void
    {
        $paper = $this->makePaper([
            'url'        => 'https://arxiv.org/abs/2501.99999',
            'title'      => 'Special Paper',
            'summary'    => 'Special summary.',
            'eli5'       => 'Special ELI5.',
            'swe'        => 'Special SWE.',
            'investor'   => 'Special investor.',
            'also_in'    => ['Source B'],
            'discipline' => 'Biology',
            'source'     => 'bioRxiv',
        ]);

        $this->repo->save($paper);

        $saved = $this->repo->all()[0];
        $this->assertSame('https://arxiv.org/abs/2501.99999', $saved['url']);
        $this->assertSame('Special Paper', $saved['title']);
        $this->assertSame('Special summary.', $saved['summary']);
        $this->assertSame('Special ELI5.', $saved['eli5']);
        $this->assertSame('Special SWE.', $saved['swe']);
        $this->assertSame('Special investor.', $saved['investor']);
        $this->assertSame(['Source B'], $saved['also_in']);
        $this->assertSame('Biology', $saved['discipline']);
        $this->assertSame('bioRxiv', $saved['source']);
    }

    public function test_save_multiple_papers(): void
    {
        $this->repo->save($this->makePaper(['url' => 'https://example.com/1']));
        $this->repo->save($this->makePaper(['url' => 'https://example.com/2']));
        $this->repo->save($this->makePaper(['url' => 'https://example.com/3']));

        $this->assertSame(3, $this->repo->count());
    }

    public function test_save_newest_first_ordering(): void
    {
        $this->travel(-2)->hours();
        $this->repo->save($this->makePaper(['url' => 'https://example.com/old', 'title' => 'Old']));

        $this->travelBack();
        // Need a fresh repo instance to clear the in-memory cache.
        $repo2 = new SavedPapersRepository($this->tempPath);
        $repo2->save($this->makePaper(['url' => 'https://example.com/new', 'title' => 'New']));

        $repo3 = new SavedPapersRepository($this->tempPath);
        $all = $repo3->all();

        $this->assertSame('New', $all[0]['title']);
        $this->assertSame('Old', $all[1]['title']);
    }

    public function test_save_deduplicates_by_url(): void
    {
        $url = 'https://arxiv.org/abs/2501.00001';
        $this->repo->save($this->makePaper(['url' => $url, 'title' => 'Original']));

        // Fresh repo to clear cache, then re-save same URL.
        $repo2 = new SavedPapersRepository($this->tempPath);
        $repo2->save($this->makePaper(['url' => $url, 'title' => 'Updated']));

        $repo3 = new SavedPapersRepository($this->tempPath);
        $this->assertSame(1, $repo3->count());
        $this->assertSame('Updated', $repo3->all()[0]['title']);
    }

    public function test_resave_updates_saved_at(): void
    {
        $url = 'https://arxiv.org/abs/2501.00001';

        $this->travel(-1)->hours();
        $this->repo->save($this->makePaper(['url' => $url]));
        $firstSavedAt = $this->repo->all()[0]['saved_at'];

        $this->travelBack();
        $repo2 = new SavedPapersRepository($this->tempPath);
        $repo2->save($this->makePaper(['url' => $url]));
        $secondSavedAt = $repo2->all()[0]['saved_at'];

        $this->assertNotSame($firstSavedAt, $secondSavedAt);
    }

    // ------------------------------------------------------------------
    // Remove
    // ------------------------------------------------------------------

    public function test_remove_deletes_paper(): void
    {
        $url = 'https://arxiv.org/abs/2501.00001';
        $this->repo->save($this->makePaper(['url' => $url]));
        $this->repo = new SavedPapersRepository($this->tempPath);

        $this->repo->remove($url);

        $this->assertSame(0, $this->repo->count());
        $this->assertFalse($this->repo->has($url));
    }

    public function test_remove_nonexistent_url_is_noop(): void
    {
        $this->repo->save($this->makePaper(['url' => 'https://example.com/1']));
        $this->repo = new SavedPapersRepository($this->tempPath);

        $this->repo->remove('https://example.com/nonexistent');

        $this->assertSame(1, $this->repo->count());
    }

    public function test_remove_only_affects_target(): void
    {
        $this->repo->save($this->makePaper(['url' => 'https://example.com/1', 'title' => 'Keep']));
        $this->repo = new SavedPapersRepository($this->tempPath);
        $this->repo->save($this->makePaper(['url' => 'https://example.com/2', 'title' => 'Remove']));
        $this->repo = new SavedPapersRepository($this->tempPath);

        $this->repo->remove('https://example.com/2');

        $this->assertSame(1, $this->repo->count());
        $this->assertTrue($this->repo->has('https://example.com/1'));
        $this->assertFalse($this->repo->has('https://example.com/2'));
    }

    // ------------------------------------------------------------------
    // Clear
    // ------------------------------------------------------------------

    public function test_clear_removes_all(): void
    {
        $this->repo->save($this->makePaper(['url' => 'https://example.com/1']));
        $this->repo = new SavedPapersRepository($this->tempPath);
        $this->repo->save($this->makePaper(['url' => 'https://example.com/2']));
        $this->repo = new SavedPapersRepository($this->tempPath);

        $this->repo->clear();

        $this->assertSame(0, $this->repo->count());
        $this->assertSame([], $this->repo->all());
    }

    // ------------------------------------------------------------------
    // savedUrls
    // ------------------------------------------------------------------

    public function test_saved_urls_returns_all_urls(): void
    {
        $this->repo->save($this->makePaper(['url' => 'https://example.com/1']));
        $this->repo = new SavedPapersRepository($this->tempPath);
        $this->repo->save($this->makePaper(['url' => 'https://example.com/2']));

        $urls = $this->repo->savedUrls();

        $this->assertCount(2, $urls);
        $this->assertContains('https://example.com/1', $urls);
        $this->assertContains('https://example.com/2', $urls);
    }

    // ------------------------------------------------------------------
    // Export
    // ------------------------------------------------------------------

    public function test_export_returns_valid_json_envelope(): void
    {
        $this->repo->save($this->makePaper(['url' => 'https://example.com/1', 'title' => 'Paper 1']));
        $this->repo = new SavedPapersRepository($this->tempPath);

        $json = $this->repo->export();
        $decoded = json_decode($json, true);

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('meta', $decoded);
        $this->assertArrayHasKey('papers', $decoded);
        $this->assertSame(1, $decoded['meta']['format_version']);
        $this->assertSame(1, $decoded['meta']['count']);
        $this->assertArrayHasKey('exported_at', $decoded['meta']);
        $this->assertCount(1, $decoded['papers']);
        $this->assertSame('Paper 1', $decoded['papers'][0]['title']);
    }

    public function test_export_empty_is_valid(): void
    {
        $json = $this->repo->export();
        $decoded = json_decode($json, true);

        $this->assertIsArray($decoded);
        $this->assertSame(0, $decoded['meta']['count']);
        $this->assertSame([], $decoded['papers']);
    }

    // ------------------------------------------------------------------
    // File format
    // ------------------------------------------------------------------

    public function test_file_contains_format_version(): void
    {
        $this->repo->save($this->makePaper());

        $raw = json_decode(file_get_contents($this->tempPath), true);

        $this->assertArrayHasKey('format_version', $raw);
        $this->assertSame(1, $raw['format_version']);
    }

    public function test_file_survives_corruption_gracefully(): void
    {
        file_put_contents($this->tempPath, 'not valid json{{{');

        $repo = new SavedPapersRepository($this->tempPath);

        $this->assertSame([], $repo->all());
        $this->assertSame(0, $repo->count());
    }
}
