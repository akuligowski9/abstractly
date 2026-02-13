<?php

namespace App\Services;

class SavedPapersRepository
{
    private string $path;
    private ?array $cache = null;

    public function __construct(?string $path = null)
    {
        $this->path = $path ?? storage_path('app/saved-papers.json');
    }

    /**
     * All saved papers, newest first.
     */
    public function all(): array
    {
        $data = $this->read();

        $papers = $data['papers'] ?? [];

        usort($papers, fn ($a, $b) => strcmp($b['saved_at'] ?? '', $a['saved_at'] ?? ''));

        return $papers;
    }

    /**
     * Save a paper (deduplicate by URL, adds saved_at).
     */
    public function save(array $paper): void
    {
        $data = $this->read();

        $url = $paper['url'] ?? '';

        // Remove existing entry with same URL (re-save updates timestamp).
        $data['papers'] = array_values(
            array_filter($data['papers'] ?? [], fn ($p) => ($p['url'] ?? '') !== $url)
        );

        $paper['saved_at'] = now()->toIso8601String();

        $data['papers'][] = $paper;

        $this->write($data);
    }

    /**
     * Remove a paper by URL.
     */
    public function remove(string $url): void
    {
        $data = $this->read();

        $data['papers'] = array_values(
            array_filter($data['papers'] ?? [], fn ($p) => ($p['url'] ?? '') !== $url)
        );

        $this->write($data);
    }

    /**
     * Check if a URL is saved.
     */
    public function has(string $url): bool
    {
        $data = $this->read();

        foreach ($data['papers'] ?? [] as $paper) {
            if (($paper['url'] ?? '') === $url) {
                return true;
            }
        }

        return false;
    }

    /**
     * Flat array of all saved URLs (for batch lookups).
     */
    public function savedUrls(): array
    {
        $data = $this->read();

        return array_values(
            array_map(fn ($p) => $p['url'] ?? '', $data['papers'] ?? [])
        );
    }

    /**
     * Total count of saved papers.
     */
    public function count(): int
    {
        $data = $this->read();

        return count($data['papers'] ?? []);
    }

    /**
     * Remove all saved papers.
     */
    public function clear(): void
    {
        $this->write(['format_version' => 1, 'papers' => []]);
    }

    /**
     * JSON string with { meta, papers } envelope for export.
     */
    public function export(): string
    {
        $papers = $this->all();

        return json_encode([
            'meta' => [
                'exported_at'    => now()->toIso8601String(),
                'format_version' => 1,
                'count'          => count($papers),
            ],
            'papers' => $papers,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Read the JSON file into memory (cached per request).
     */
    private function read(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        if (! file_exists($this->path)) {
            $this->cache = ['format_version' => 1, 'papers' => []];
            return $this->cache;
        }

        $json = file_get_contents($this->path);
        $data = json_decode($json, true);

        if (! is_array($data)) {
            $data = ['format_version' => 1, 'papers' => []];
        }

        $this->cache = $data;

        return $this->cache;
    }

    /**
     * Write data to the JSON file and update cache.
     */
    private function write(array $data): void
    {
        if (! isset($data['format_version'])) {
            $data['format_version'] = 1;
        }

        $dir = dirname($this->path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $this->path,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->cache = $data;
    }
}
