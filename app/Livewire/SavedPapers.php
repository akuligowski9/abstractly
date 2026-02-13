<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Services\SavedPapersRepository;

#[Layout('components.layouts.app')]
#[Title('Saved Papers')]
class SavedPapers extends Component
{
    public array $papers = [];

    public function mount(SavedPapersRepository $repo): void
    {
        $this->papers = $repo->all();
    }

    public function removePaper(string $url, SavedPapersRepository $repo): void
    {
        $repo->remove($url);
        $this->papers = $repo->all();
    }

    public function clearAll(SavedPapersRepository $repo): void
    {
        $repo->clear();
        $this->papers = [];
    }

    public function export(SavedPapersRepository $repo)
    {
        $json = $repo->export();
        $filename = 'saved-papers-' . now()->format('Y-m-d_His') . '.json';

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, $filename, ['Content-Type' => 'application/json']);
    }

    public function render()
    {
        return view('livewire.saved-papers');
    }
}
