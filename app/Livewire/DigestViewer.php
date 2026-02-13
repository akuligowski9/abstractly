<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Services\SourcePreviewer;
use App\Services\AiSummarizer;
use App\Services\PaperDeduplicator;
use App\Services\SavedPapersRepository;
use Illuminate\Support\Facades\Storage;

#[Layout('components.layouts.app')]
#[Title('Weekly Digest')]
class DigestViewer extends Component
{
    public array $digest = [];
    public array $failures = [];
    public array $savedUrls = [];
    public bool $generating = false;
    public bool $forceRefresh = false;
    public int $limitPerSource = 5;

    protected int $requestTimeout = 120;

    public function mount(SavedPapersRepository $savedPapers): void
    {
        $this->digest = session('digest.latest', []);
        $this->savedUrls = $savedPapers->savedUrls();
    }

    public function generate(SourcePreviewer $previewer, AiSummarizer $ai, PaperDeduplicator $deduplicator): void
    {
        set_time_limit($this->requestTimeout);

        $this->generating = true;
        $this->failures = [];

        $discAll = config('disciplines.all', []);
        $readyDisc = collect($discAll)
            ->filter(fn ($m) => $m['ready'] ?? false)
            ->keys()
            ->all();

        $enabled = (array) session('enabled_disciplines', []);
        $disciplines = array_values(array_intersect($enabled, $readyDisc));

        $allSources = collect(config('sources.list', []));
        $digest = [];

        // Precompute sources per discipline for progress tracking.
        $plan = [];
        foreach ($disciplines as $slug) {
            $sourcesForSlug = $allSources
                ->filter(fn ($s) => in_array($slug, $s['disciplines'] ?? [], true))
                ->values();

            $selectedKeys = (array) session("enabled_sources.$slug", []);
            if (! empty($selectedKeys)) {
                $sourcesForSlug = $sourcesForSlug->whereIn('key', $selectedKeys)->values();
            } else {
                $sourcesForSlug = $sourcesForSlug->take(3);
            }

            if ($sourcesForSlug->isNotEmpty()) {
                $plan[] = ['slug' => $slug, 'sources' => $sourcesForSlug];
            }
        }

        $totalSources = collect($plan)->sum(fn ($p) => $p['sources']->count());
        $completed = 0;

        foreach ($plan as $p) {
            $slug = $p['slug'];
            $sourcesForSlug = $p['sources'];
            $discLabel = $discAll[$slug]['label'] ?? ucfirst($slug);

            // Pass 1: Fetch all sources for this discipline.
            $fetchedBySource = [];
            foreach ($sourcesForSlug as $src) {
                $this->stream('progress-status', "Fetching {$src['label']}…", true);

                try {
                    $items = $previewer->fetch($src, $this->limitPerSource, $this->forceRefresh);
                } catch (\Throwable $e) {
                    $this->failures[] = ['source' => $src['label'], 'type' => 'fetch'];
                    $items = [];
                }

                if (! empty($items)) {
                    $fetchedBySource[$src['label']] = $items;
                }
            }

            // Pass 2: Deduplicate across sources within this discipline.
            $this->stream('progress-status', "Deduplicating {$discLabel}…", true);
            $dedupedBySource = $deduplicator->dedup($fetchedBySource);

            // Pass 3: Summarize unique items per source (original order).
            $sections = [];
            foreach ($sourcesForSlug as $src) {
                $items = $dedupedBySource[$src['label']] ?? [];
                if (empty($items)) {
                    $completed++;
                    $this->streamProgress($completed, $totalSources);
                    continue;
                }

                $this->stream('progress-status', "Summarizing {$src['label']}…", true);

                try {
                    $enriched = $ai->summarizeItems($src['label'], $items, $this->forceRefresh);
                } catch (\Throwable $e) {
                    $type = str_contains($e->getMessage(), 'daily limit') ? 'rate_limit' : 'summarize';
                    $this->failures[] = ['source' => $src['label'], 'type' => $type];
                    $enriched = $items;
                }

                $sections[] = [
                    'source' => $src['label'],
                    'items'  => $enriched,
                ];

                $completed++;
                $this->streamProgress($completed, $totalSources);
            }

            if (! empty($sections)) {
                $entry = [
                    'discipline' => $discLabel,
                    'slug'       => $slug,
                    'sections'   => $sections,
                ];
                $digest[] = $entry;

                $html = view('livewire.partials.digest-section', ['d' => $entry, 'savedUrls' => $this->savedUrls])->render();
                $this->stream('digest-stream', $html, false);
            }
        }

        session(['digest.latest' => $digest]);
        $this->digest = $digest;
        $this->generating = false;
    }

    public function toggleSave(string $url, SavedPapersRepository $savedPapers): void
    {
        if ($savedPapers->has($url)) {
            $savedPapers->remove($url);
        } else {
            $paper = $this->findPaperByUrl($url);
            if ($paper) {
                $savedPapers->save($paper);
            }
        }

        $this->savedUrls = $savedPapers->savedUrls();
    }

    private function findPaperByUrl(string $url): ?array
    {
        foreach ($this->digest as $entry) {
            $discipline = $entry['discipline'] ?? '';
            foreach ($entry['sections'] ?? [] as $section) {
                $source = $section['source'] ?? '';
                foreach ($section['items'] ?? [] as $item) {
                    if (($item['url'] ?? '') === $url) {
                        return [
                            'url'        => $item['url'] ?? '',
                            'title'      => $item['title'] ?? '',
                            'summary'    => $item['summary'] ?? '',
                            'eli5'       => $item['eli5'] ?? '',
                            'swe'        => $item['swe'] ?? '',
                            'investor'   => $item['investor'] ?? '',
                            'also_in'    => $item['also_in'] ?? [],
                            'discipline' => $discipline,
                            'source'     => $source,
                        ];
                    }
                }
            }
        }

        return null;
    }

    private function streamProgress(int $completed, int $total): void
    {
        $pct = $total > 0 ? round(($completed / $total) * 100) : 0;
        $this->stream('progress-bar', '<div class="h-full bg-indigo-600 rounded-full transition-all duration-300" style="width: ' . $pct . '%"></div>', true);
        $this->stream('progress-count', "{$completed} of {$total} sources completed", true);
    }

    public function export()
    {
        $enabled = (array) session('enabled_disciplines', []);
        $sourcesPerDiscipline = [];
        foreach ($enabled as $slug) {
            $sourcesPerDiscipline[$slug] = (array) session("enabled_sources.$slug", []);
        }

        $envelope = [
            'meta' => [
                'generated_at'  => now()->toIso8601String(),
                'format_version' => 1,
                'disciplines'   => $enabled,
                'sources'       => $sourcesPerDiscipline,
            ],
            'digest' => $this->digest,
        ];

        $json = json_encode($envelope, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'digest-' . now()->format('Y-m-d_His') . '.json';

        Storage::disk('local')->put("digests/{$filename}", $json);

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, $filename, ['Content-Type' => 'application/json']);
    }

    public function render()
    {
        return view('livewire.digest-viewer');
    }
}
