<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;

#[Layout('components.layouts.app')]
class SourcePicker extends Component
{
    public string $slug;
    public string $label;
    public array $sources = [];
    public array $selected = [];

    public function mount(string $slug): void
    {
        $all = config('disciplines.all', []);
        abort_unless(array_key_exists($slug, $all), 404);

        $this->slug = $slug;
        $this->label = $all[$slug]['label'] ?? ucfirst($slug);

        $this->sources = collect(config('sources.list', []))
            ->filter(fn ($s) => in_array($slug, $s['disciplines'] ?? [], true))
            ->values()
            ->all();

        $sessionKey = "enabled_sources.$slug";
        $this->selected = session($sessionKey, $this->defaultSourcesFor($slug));
    }

    public function getTitle(): string
    {
        return "{$this->label} — Sources";
    }

    public function selectAll(): void
    {
        $this->selected = collect($this->sources)->pluck('key')->all();
    }

    public function selectNone(): void
    {
        $this->selected = [];
    }

    public function toggleSource(string $key): void
    {
        $validKeys = collect($this->sources)->pluck('key')->all();
        if (! in_array($key, $validKeys, true)) {
            return;
        }

        if (in_array($key, $this->selected, true)) {
            $this->selected = array_values(array_diff($this->selected, [$key]));
        } else {
            $this->selected[] = $key;
        }
    }

    public function save(): void
    {
        $validKeys = collect($this->sources)->pluck('key')->all();

        $normalized = collect($this->selected)
            ->map(fn ($k) => trim($k))
            ->filter(fn ($k) => in_array($k, $validKeys, true))
            ->unique()
            ->values()
            ->all();

        session(["enabled_sources.{$this->slug}" => $normalized]);
        $this->selected = $normalized;

        session()->flash('status', 'Sources updated (' . count($normalized) . ' selected).');
    }

    public function render()
    {
        return view('livewire.source-picker')
            ->title($this->getTitle());
    }

    private function defaultSourcesFor(string $slug): array
    {
        $available = collect($this->sources)->pluck('key')->all();

        if ($slug === 'math') {
            $preferred = [
                'arxiv_math_all',
                'arxiv_math_PR',
                'arxiv_math_NT',
                'arxiv_math_AP',
                'biorxiv_recent',
                'medrxiv_recent',
            ];
            return array_values(array_intersect($preferred, $available));
        }

        return array_slice($available, 0, 3);
    }
}
