<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
#[Title('Discipline Selection')]
class DisciplinePicker extends Component
{
    public array $all = [];
    public array $selected = [];

    public function mount(): void
    {
        $this->all = config('disciplines.all', []);
        $defaults = config('disciplines.enabled_by_default', []);
        $this->selected = session('enabled_disciplines', $defaults);
    }

    public function selectAll(): void
    {
        $this->selected = collect($this->all)
            ->filter(fn ($meta) => $meta['ready'] ?? false)
            ->keys()
            ->all();
    }

    public function selectNone(): void
    {
        $this->selected = [];
    }

    public function toggleDiscipline(string $slug): void
    {
        $meta = $this->all[$slug] ?? null;
        if (! $meta || ! ($meta['ready'] ?? false)) {
            return;
        }

        if (in_array($slug, $this->selected, true)) {
            $this->selected = array_values(array_diff($this->selected, [$slug]));
        } else {
            $this->selected[] = $slug;
        }
    }

    public function save(): void
    {
        $allKeys = array_keys($this->all);
        $aliases = config('disciplines.aliases', []);

        $normalized = collect($this->selected)
            ->map(fn ($k) => strtolower(trim($k)))
            ->map(fn ($k) => $aliases[$k] ?? $k)
            ->filter(fn ($k) => in_array($k, $allKeys, true))
            ->unique()
            ->values()
            ->all();

        session(['enabled_disciplines' => $normalized]);
        $this->selected = $normalized;

        session()->flash('status', 'Selection saved (' . count($normalized) . ' selected).');
    }

    public function render()
    {
        return view('livewire.discipline-picker', [
            'countAll' => count($this->all),
            'countSel' => count($this->selected),
        ]);
    }
}
