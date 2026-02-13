<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('dusk')) {
            $this->fakeDuskHttp();
        }
    }

    /**
     * Register Http::fake() stubs so Dusk tests never hit real APIs.
     */
    private function fakeDuskHttp(): void
    {
        Http::fake([
            // arXiv Atom feeds
            'export.arxiv.org/*' => Http::response($this->cannedArxivAtom(), 200, [
                'Content-Type' => 'application/atom+xml',
            ]),

            // bioRxiv JSON
            'api.biorxiv.org/*' => Http::response([
                'collection' => $this->cannedRxivCollection('biorxiv'),
            ], 200),

            // medRxiv JSON
            'api.medrxiv.org/*' => Http::response([
                'collection' => $this->cannedRxivCollection('medrxiv'),
            ], 200),

            // Gemini AI
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode([
                                'summaries' => $this->cannedGeminiSummaries(5),
                            ]),
                        ]],
                    ],
                ]],
            ], 200),
        ]);
    }

    private function cannedArxivAtom(): string
    {
        $entries = '';
        for ($i = 1; $i <= 5; $i++) {
            $entries .= <<<XML
            <entry>
                <title>Mock Paper {$i}: Advances in Mathematical Structures</title>
                <id>http://arxiv.org/abs/2501.0000{$i}</id>
                <link rel="alternate" href="http://arxiv.org/abs/2501.0000{$i}" />
                <summary>This is a mock abstract for paper {$i}. It discusses novel approaches to mathematical structures and their applications in theoretical frameworks.</summary>
            </entry>
            XML;
        }

        return <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <feed xmlns="http://www.w3.org/2005/Atom">
            <title>arXiv Mock Feed</title>
            {$entries}
        </feed>
        XML;
    }

    private function cannedRxivCollection(string $server): array
    {
        $items = [];
        for ($i = 1; $i <= 5; $i++) {
            $items[] = [
                'title'    => "Mock {$server} Paper {$i}: Quantitative Biology Methods",
                'abstract' => "This is a mock abstract for {$server} paper {$i}. It covers quantitative methods applicable to biological and medical research.",
                'doi'      => "10.1101/2025.01.{$i}",
            ];
        }
        return $items;
    }

    private function cannedGeminiSummaries(int $count): array
    {
        $summaries = [];
        for ($i = 1; $i <= $count; $i++) {
            $summaries[] = [
                'index'    => $i,
                'eli5'     => "This paper looks at interesting patterns in data. Think of it like finding hidden shapes in a cloud — the researchers built a tool to spot these shapes faster.",
                'swe'      => "A solo dev could build a lightweight API that applies this technique to user-uploaded datasets, charging per analysis. There is clear demand in the data-science tooling market.",
                'investor' => "Early-stage opportunity in automated research tooling. The TAM for AI-assisted scientific analysis is growing rapidly, with potential for a vertical SaaS play.",
            ];
        }
        return $summaries;
    }
}
