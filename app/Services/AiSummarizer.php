<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiSummarizer
{
    /**
     * Summarize each item (paper) with 3 short paragraphs:
     *  - eli5     (2–3 sentences)
     *  - swe      (2–3 sentences, concrete micro-product angles for a solo SWE)
     *  - investor (2–3 sentences, high-risk thesis)
     *
     * Provider is selected via DIGEST_AI_PROVIDER = gemini | openai | ollama
     */
    public function summarizeItems(string $sourceLabel, array $items, bool $forceRefresh = false): array
    {
        $ttl = (int) config('ai.summary_cache_ttl', 86400);

        // Split items into cached (already have summaries) and uncached (need API call).
        $cached = [];
        $uncachedIndices = [];

        foreach ($items as $i => $item) {
            $key = $this->summaryCacheKey($item);

            if (! $forceRefresh && $ttl > 0 && $key && Cache::has($key)) {
                $cached[$i] = $item + Cache::get($key);
            } else {
                $uncachedIndices[] = $i;
            }
        }

        // Summarize uncached items via the configured provider.
        if (! empty($uncachedIndices)) {
            $uncachedItems = array_map(fn ($i) => $items[$i], $uncachedIndices);

            $freshResults = $this->callProvider($sourceLabel, $uncachedItems);

            foreach ($freshResults as $j => $enriched) {
                $origIdx = $uncachedIndices[$j];
                $cached[$origIdx] = $enriched;

                // Store summary fields in cache.
                $key = $this->summaryCacheKey($items[$origIdx]);
                if ($key && $ttl > 0) {
                    Cache::put($key, [
                        'eli5'     => $enriched['eli5'] ?? '',
                        'swe'      => $enriched['swe'] ?? '',
                        'investor' => $enriched['investor'] ?? '',
                    ], $ttl);
                }
            }
        }

        ksort($cached);

        return array_values($cached);
    }

    private function callProvider(string $sourceLabel, array $items): array
    {
        $provider = config('ai.provider');

        if ($provider === 'gemini') {
            return $this->summarizeItemsWithGemini($sourceLabel, $items);
        } elseif ($provider === 'openai') {
            return $this->summarizeItemsWithOpenAI($sourceLabel, $items);
        } elseif ($provider === 'ollama') {
            return $this->summarizeItemsWithOllama($sourceLabel, $items);
        }

        Log::warning('AiSummarizer: Unknown provider "' . $provider . '"; using Gemini.');
        return $this->summarizeItemsWithGemini($sourceLabel, $items);
    }

    private function summaryCacheKey(array $item): ?string
    {
        $url = trim(strtolower($item['url'] ?? ''));

        if ($url === '' || $url === '#') {
            return null;
        }

        return 'ai_summary:' . md5($url);
    }

    // ---------------------------
    // Gemini (default / free tier)
    // ---------------------------
    private function summarizeItemsWithGemini(string $sourceLabel, array $items): array
    {
        $apiKey = config('ai.gemini.api_key');
        $model  = config('ai.gemini.model');

        if (!$apiKey) {
            Log::warning('AiSummarizer(Gemini): GOOGLE_API_KEY not set; returning placeholders.');
            return $this->placeholderPerItem($items, 'Set GOOGLE_API_KEY in .env to enable Gemini summaries.');
        }

        $batches = array_chunk($items, 5);
        $out = [];

        foreach ($batches as $batchIdx => $batch) {
            if ($batchIdx > 0) {
                $this->batchDelay();
            }

            $prompt = $this->buildJsonPrompt($sourceLabel, $batch);

            $resp = Http::timeout(40)
                ->retry(2, fn ($attempt) => $attempt * 1000, fn ($e) => $e instanceof \Illuminate\Http\Client\RequestException && $e->response->status() === 429, throw: false)
                ->withHeaders(['x-goog-api-key' => $apiKey])
                ->post('https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent', [
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'response_mime_type' => 'application/json',
                    ],
                    'contents' => [[
                        'role'  => 'user',
                        'parts' => [['text' => $prompt]],
                    ]],
                ]);

            if (!$resp->ok()) {
                Log::error('Gemini batch error', [
                    'status' => $resp->status(),
                    'body'   => mb_substr($resp->body(), 0, 800),
                ]);

                if ($resp->status() === 429) {
                    throw new \RuntimeException('Gemini free tier daily limit reached — try again tomorrow.');
                }

                $out = array_merge($out, $this->placeholderPerItem($batch, 'Gemini request failed for this batch.'));
                continue;
            }

            // Gemini returns JSON text here; decode to our summaries array
            $jsonText  = data_get($resp->json(), 'candidates.0.content.parts.0.text');
            $parsed    = json_decode((string) $jsonText, true);
            $summaries = $parsed['summaries'] ?? [];

            $out = array_merge($out, $this->mergeBatchSummaries($batch, $summaries));
        }

        return $out;
    }

    // ---------------
    // OpenAI (optional)
    // ---------------
    private function summarizeItemsWithOpenAI(string $sourceLabel, array $items): array
    {
        $apiKey = config('ai.openai.api_key');
        $model  = config('ai.openai.model');

        if (!$apiKey) {
            Log::warning('AiSummarizer(OpenAI): OPENAI_API_KEY not set; returning placeholders.');
            return $this->placeholderPerItem($items, 'Set OPENAI_API_KEY in .env or switch provider to Gemini.');
        }

        $batches = array_chunk($items, 5);
        $out = [];

        foreach ($batches as $batchIdx => $batch) {
            if ($batchIdx > 0) {
                $this->batchDelay();
            }

            $prompt = $this->buildJsonPrompt($sourceLabel, $batch);

            $resp = Http::timeout(40)
                ->retry(2, fn ($attempt) => $attempt * 1000, fn ($e) => $e instanceof \Illuminate\Http\Client\RequestException && $e->response->status() === 429, throw: false)
                ->withToken($apiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a precise research editor who outputs strict JSON.'],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                    'temperature' => 0.3,
                    'response_format' => ['type' => 'json_object'],
                    'max_tokens' => 1400,
                ]);

            if (!$resp->ok()) {
                Log::error('OpenAI batch error', [
                    'status' => $resp->status(),
                    'body'   => mb_substr($resp->body(), 0, 800),
                ]);
                $out = array_merge($out, $this->placeholderPerItem($batch, 'OpenAI request failed for this batch.'));
                continue;
            }

            $parsed   = json_decode($resp->body(), true);
            $jsonText = data_get($parsed, 'choices.0.message.content', '{}');
            $obj      = json_decode($jsonText, true);
            $summaries = $obj['summaries'] ?? [];

            $out = array_merge($out, $this->mergeBatchSummaries($batch, $summaries));
        }

        return $out;
    }

    // -----------------
    // Ollama (optional)
    // -----------------
    private function summarizeItemsWithOllama(string $sourceLabel, array $items): array
    {
        $host  = rtrim(config('ai.ollama.host'), '/');
        $model = config('ai.ollama.model');

        $batches = array_chunk($items, 3); // smaller context for local models
        $out = [];

        foreach ($batches as $batchIdx => $batch) {
            if ($batchIdx > 0) {
                $this->batchDelay();
            }

            $prompt = $this->buildJsonPrompt($sourceLabel, $batch);

            $resp = Http::timeout(60)
                ->retry(2, fn ($attempt) => $attempt * 1000, fn ($e) => $e instanceof \Illuminate\Http\Client\RequestException && $e->response->status() === 429, throw: false)
                ->post($host . '/api/chat', [
                    'model'    => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a precise research editor who outputs valid JSON only.'],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                    'stream'   => false,
                ]);

            if (!$resp->ok()) {
                Log::error('Ollama batch error', [
                    'status' => $resp->status(),
                    'body'   => mb_substr($resp->body(), 0, 800),
                ]);
                $out = array_merge($out, $this->placeholderPerItem($batch, 'Ollama request failed for this batch.'));
                continue;
            }

            $jsonText  = $resp->json('message.content');
            $obj       = json_decode((string) $jsonText, true);
            $summaries = $obj['summaries'] ?? [];

            $out = array_merge($out, $this->mergeBatchSummaries($batch, $summaries));
        }

        return $out;
    }

    // -----------------
    // Helpers
    // -----------------
    private function batchDelay(): void
    {
        $ms = (int) config('ai.batch_delay_ms', 200);

        if ($ms > 0) {
            usleep($ms * 1000);
        }
    }

    private function buildJsonPrompt(string $sourceLabel, array $batch): string
    {
        $list = collect($batch)->values()->map(function ($it, $i) {
            $idx = $i + 1;
            $t   = trim($it['title'] ?? '(untitled)');
            $s   = trim((string)($it['summary'] ?? ''));
            $s   = mb_strimwidth(preg_replace('/\s+/', ' ', $s), 0, 700, '…');
            $u   = trim($it['url'] ?? '#');
            return "{$idx}. TITLE: {$t}\nURL: {$u}\nABSTRACT: {$s}";
        })->implode("\n\n");

        return <<<PROMPT
You will receive N research items from "{$sourceLabel}" (titles + short abstracts).
For EACH item, produce a JSON object with fields:
- index: the 1-based index of the item as given.
- eli5: 2–3 sentences, plain language.
- swe: 2–3 sentences with specific micro-product ideas a solo software engineer could build quickly (why users pay).
- investor: 2–3 sentences on a high-risk thesis/opportunity (crypto allowed but not required).

CRITICAL RULES:
- Output VALID JSON ONLY with this envelope:
{"summaries":[{"index":1,"eli5":"...","swe":"...","investor":"..."}, ...]}
- No markdown fences or commentary.
- Be specific; avoid boilerplate; tailor to each item.

Items:
{$list}
PROMPT;
    }

    private function mergeBatchSummaries(array $batch, array $summaries): array
    {
        // Map by index; maintain original ordering; fill gaps with placeholders.
        $byIndex = [];
        foreach ($summaries as $row) {
            $i = (int)($row['index'] ?? 0);
            if ($i >= 1 && $i <= count($batch)) {
                $byIndex[$i] = [
                    'eli5'     => trim((string)($row['eli5'] ?? '')),
                    'swe'      => trim((string)($row['swe'] ?? '')),
                    'investor' => trim((string)($row['investor'] ?? '')),
                ];
            }
        }

        $out = [];
        foreach ($batch as $i => $it) {
            $idx = $i + 1;
            if (isset($byIndex[$idx])) {
                $out[] = $it + $byIndex[$idx];
            } else {
                $out[] = $it + [
                    'eli5'     => 'ELI5: (missing summary)',
                    'swe'      => 'SWE: (missing summary)',
                    'investor' => 'Investor: (missing summary)',
                ];
            }
        }
        return $out;
    }

    private function placeholderPerItem(array $items, string $why): array
    {
        return collect($items)->map(function ($it) use ($why) {
            return $it + [
                'eli5'     => 'ELI5: ' . $why,
                'swe'      => 'SWE: ' . $why,
                'investor' => 'Investor: ' . $why,
            ];
        })->all();
    }
}
