<?php

namespace Tests\Unit;

use App\Services\AiSummarizer;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiSummarizerTest extends TestCase
{
    private AiSummarizer $summarizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->summarizer = new AiSummarizer();
    }

    // ------------------------------------------------------------------
    // Gemini — happy path
    // ------------------------------------------------------------------

    public function test_gemini_happy_path_returns_summaries(): void
    {
        config([
            'ai.provider'        => 'gemini',
            'ai.gemini.api_key'  => 'fake-gemini-key',
            'ai.gemini.model'    => 'gemini-2.0-flash',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode([
                                'summaries' => [
                                    ['index' => 1, 'eli5' => 'Simple explanation.', 'swe' => 'Build this.', 'investor' => 'Bet on this.'],
                                    ['index' => 2, 'eli5' => 'Easy version.', 'swe' => 'Ship that.', 'investor' => 'Fund that.'],
                                ],
                            ]),
                        ]],
                    ],
                ]],
            ], 200),
        ]);

        $items = [
            ['title' => 'Paper A', 'url' => 'https://example.com/a', 'summary' => 'Abstract A'],
            ['title' => 'Paper B', 'url' => 'https://example.com/b', 'summary' => 'Abstract B'],
        ];

        $result = $this->summarizer->summarizeItems('Test Source', $items);

        $this->assertCount(2, $result);

        $this->assertSame('Simple explanation.', $result[0]['eli5']);
        $this->assertSame('Build this.', $result[0]['swe']);
        $this->assertSame('Bet on this.', $result[0]['investor']);
        $this->assertSame('Paper A', $result[0]['title']);

        $this->assertSame('Easy version.', $result[1]['eli5']);
    }

    // ------------------------------------------------------------------
    // Gemini — missing API key → placeholders
    // ------------------------------------------------------------------

    public function test_gemini_missing_api_key_returns_placeholders(): void
    {
        config([
            'ai.provider'        => 'gemini',
            'ai.gemini.api_key'  => null,
            'ai.gemini.model'    => 'gemini-2.0-flash',
        ]);

        Http::fake(); // should never be called

        $items = [
            ['title' => 'Paper A', 'url' => 'https://example.com/a', 'summary' => 'Abstract A'],
        ];

        $result = $this->summarizer->summarizeItems('Test Source', $items);

        $this->assertCount(1, $result);
        $this->assertStringContainsString('GOOGLE_API_KEY', $result[0]['eli5']);
        $this->assertStringContainsString('GOOGLE_API_KEY', $result[0]['swe']);

        Http::assertNothingSent();
    }

    // ------------------------------------------------------------------
    // Gemini — API failure → placeholder per batch
    // ------------------------------------------------------------------

    public function test_gemini_api_failure_returns_placeholders(): void
    {
        config([
            'ai.provider'        => 'gemini',
            'ai.gemini.api_key'  => 'fake-key',
            'ai.gemini.model'    => 'gemini-2.0-flash',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response('Internal Server Error', 500),
        ]);

        $items = [
            ['title' => 'Paper A', 'url' => 'https://example.com/a', 'summary' => 'Abstract A'],
        ];

        $result = $this->summarizer->summarizeItems('Test Source', $items);

        $this->assertCount(1, $result);
        $this->assertStringContainsString('Gemini request failed', $result[0]['eli5']);
    }

    // ------------------------------------------------------------------
    // Batch splitting — >5 items split into batches
    // ------------------------------------------------------------------

    public function test_gemini_splits_items_into_batches_of_five(): void
    {
        config([
            'ai.provider'        => 'gemini',
            'ai.gemini.api_key'  => 'fake-key',
            'ai.gemini.model'    => 'gemini-2.0-flash',
        ]);

        // Build 7 items — should produce 2 HTTP calls (batch of 5 + batch of 2)
        $items = [];
        for ($i = 1; $i <= 7; $i++) {
            $items[] = ['title' => "Paper {$i}", 'url' => "https://example.com/{$i}", 'summary' => "Abstract {$i}"];
        }

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::sequence()
                ->push([
                    'candidates' => [[
                        'content' => [
                            'parts' => [[
                                'text' => json_encode([
                                    'summaries' => array_map(fn($i) => [
                                        'index' => $i, 'eli5' => "ELI5 #{$i}", 'swe' => "SWE #{$i}", 'investor' => "INV #{$i}",
                                    ], range(1, 5)),
                                ]),
                            ]],
                        ],
                    ]],
                ], 200)
                ->push([
                    'candidates' => [[
                        'content' => [
                            'parts' => [[
                                'text' => json_encode([
                                    'summaries' => array_map(fn($i) => [
                                        'index' => $i, 'eli5' => "ELI5 B#{$i}", 'swe' => "SWE B#{$i}", 'investor' => "INV B#{$i}",
                                    ], range(1, 2)),
                                ]),
                            ]],
                        ],
                    ]],
                ], 200),
        ]);

        $result = $this->summarizer->summarizeItems('Test Source', $items);

        $this->assertCount(7, $result);

        // First batch items
        $this->assertSame('ELI5 #1', $result[0]['eli5']);
        $this->assertSame('ELI5 #5', $result[4]['eli5']);

        // Second batch items (indexes reset to 1-based within batch)
        $this->assertSame('ELI5 B#1', $result[5]['eli5']);
        $this->assertSame('ELI5 B#2', $result[6]['eli5']);

        Http::assertSentCount(2);
    }

    // ------------------------------------------------------------------
    // OpenAI — missing API key → placeholders
    // ------------------------------------------------------------------

    public function test_openai_missing_api_key_returns_placeholders(): void
    {
        config([
            'ai.provider'        => 'openai',
            'ai.openai.api_key'  => null,
            'ai.openai.model'    => 'gpt-4o-mini',
        ]);

        Http::fake();

        $items = [
            ['title' => 'Paper A', 'url' => 'https://example.com/a', 'summary' => 'Abstract A'],
        ];

        $result = $this->summarizer->summarizeItems('Test Source', $items);

        $this->assertCount(1, $result);
        $this->assertStringContainsString('OPENAI_API_KEY', $result[0]['eli5']);

        Http::assertNothingSent();
    }

    // ------------------------------------------------------------------
    // OpenAI — happy path
    // ------------------------------------------------------------------

    public function test_openai_happy_path_returns_summaries(): void
    {
        config([
            'ai.provider'        => 'openai',
            'ai.openai.api_key'  => 'fake-openai-key',
            'ai.openai.model'    => 'gpt-4o-mini',
        ]);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'summaries' => [
                                ['index' => 1, 'eli5' => 'OpenAI eli5.', 'swe' => 'OpenAI swe.', 'investor' => 'OpenAI investor.'],
                            ],
                        ]),
                    ],
                ]],
            ], 200),
        ]);

        $items = [
            ['title' => 'Paper A', 'url' => 'https://example.com/a', 'summary' => 'Abstract A'],
        ];

        $result = $this->summarizer->summarizeItems('Test Source', $items);

        $this->assertCount(1, $result);
        $this->assertSame('OpenAI eli5.', $result[0]['eli5']);
    }

    // ------------------------------------------------------------------
    // Ollama — happy path
    // ------------------------------------------------------------------

    public function test_ollama_happy_path_returns_summaries(): void
    {
        config([
            'ai.provider'      => 'ollama',
            'ai.ollama.host'   => 'http://127.0.0.1:11434',
            'ai.ollama.model'  => 'llama3.1',
        ]);

        Http::fake([
            '127.0.0.1:11434/*' => Http::response([
                'message' => [
                    'content' => json_encode([
                        'summaries' => [
                            ['index' => 1, 'eli5' => 'Ollama eli5.', 'swe' => 'Ollama swe.', 'investor' => 'Ollama investor.'],
                        ],
                    ]),
                ],
            ], 200),
        ]);

        $items = [
            ['title' => 'Paper A', 'url' => 'https://example.com/a', 'summary' => 'Abstract A'],
        ];

        $result = $this->summarizer->summarizeItems('Test Source', $items);

        $this->assertCount(1, $result);
        $this->assertSame('Ollama eli5.', $result[0]['eli5']);
    }

    // ------------------------------------------------------------------
    // Unknown provider falls back to Gemini
    // ------------------------------------------------------------------

    public function test_unknown_provider_falls_back_to_gemini(): void
    {
        config([
            'ai.provider'        => 'unknown_provider',
            'ai.gemini.api_key'  => null,
            'ai.gemini.model'    => 'gemini-2.0-flash',
        ]);

        Http::fake();

        $items = [
            ['title' => 'Paper A', 'url' => 'https://example.com/a', 'summary' => 'Abstract A'],
        ];

        $result = $this->summarizer->summarizeItems('Test Source', $items);

        // Falls back to Gemini, which has no API key → placeholders
        $this->assertCount(1, $result);
        $this->assertStringContainsString('GOOGLE_API_KEY', $result[0]['eli5']);
    }

    // ------------------------------------------------------------------
    // mergeBatchSummaries — missing index fills placeholder
    // ------------------------------------------------------------------

    public function test_missing_summary_index_gets_placeholder(): void
    {
        config([
            'ai.provider'        => 'gemini',
            'ai.gemini.api_key'  => 'fake-key',
            'ai.gemini.model'    => 'gemini-2.0-flash',
        ]);

        // AI only returns summary for index 1, skips index 2
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode([
                                'summaries' => [
                                    ['index' => 1, 'eli5' => 'Got this one.', 'swe' => 'SWE here.', 'investor' => 'Invest here.'],
                                ],
                            ]),
                        ]],
                    ],
                ]],
            ], 200),
        ]);

        $items = [
            ['title' => 'Paper A', 'url' => 'https://example.com/a', 'summary' => 'Abstract A'],
            ['title' => 'Paper B', 'url' => 'https://example.com/b', 'summary' => 'Abstract B'],
        ];

        $result = $this->summarizer->summarizeItems('Test Source', $items);

        $this->assertCount(2, $result);
        $this->assertSame('Got this one.', $result[0]['eli5']);
        $this->assertStringContainsString('(missing summary)', $result[1]['eli5']);
    }
}
