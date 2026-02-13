<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Services\SourcePreviewer;
use App\Services\AiSummarizer;
use Illuminate\Support\Facades\Storage;

#[Layout('components.layouts.app')]
#[Title('Weekly Digest')]
class DigestViewer extends Component
{
    public array $digest = [];
    public bool $generating = false;
    public int $limitPerSource = 5;

    protected int $requestTimeout = 120;

    public function mount(): void
    {
        $this->digest = session('digest.latest', []);
    }

    public function generate(SourcePreviewer $previewer, AiSummarizer $ai): void
    {
        $this->generating = true;

        $discAll = config('disciplines.all', []);
        $readyDisc = collect($discAll)
            ->filter(fn ($m) => $m['ready'] ?? false)
            ->keys()
            ->all();

        $enabled = (array) session('enabled_disciplines', []);
        $disciplines = array_values(array_intersect($enabled, $readyDisc));

        $allSources = collect(config('sources.list', []));
        $digest = [];

        foreach ($disciplines as $slug) {
            $selectedKeys = (array) session("enabled_sources.$slug", []);
            if (empty($selectedKeys)) {
                continue;
            }

            $sourcesForSlug = $allSources
                ->filter(fn ($s) => in_array($slug, $s['disciplines'] ?? [], true))
                ->whereIn('key', $selectedKeys)
                ->values();

            if ($sourcesForSlug->isEmpty()) {
                continue;
            }

            $sections = [];
            foreach ($sourcesForSlug as $src) {
                try {
                    $items = $previewer->fetch($src, $this->limitPerSource);
                } catch (\Throwable $e) {
                    $items = [];
                }
                if (empty($items)) {
                    continue;
                }

                try {
                    $enriched = $ai->summarizeItems($src['label'], $items);
                } catch (\Throwable $e) {
                    $enriched = $items;
                }

                $sections[] = [
                    'source' => $src['label'],
                    'items'  => $enriched,
                ];
            }

            if (! empty($sections)) {
                $digest[] = [
                    'discipline' => $discAll[$slug]['label'] ?? ucfirst($slug),
                    'slug'       => $slug,
                    'sections'   => $sections,
                ];
            }
        }

        session(['digest.latest' => $digest]);
        $this->digest = $digest;
        $this->generating = false;
    }

    public function export()
    {
        $json = json_encode($this->digest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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
