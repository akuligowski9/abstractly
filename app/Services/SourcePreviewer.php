<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SourcePreviewer
{
    public function fetch(array $source, int $limit = 5): array
    {
        $key = $source['key'] ?? '';
        $url = $source['url'] ?? '';

        return match (true) {
            // arXiv Atom
            $key === 'arxiv_math'                => $this->fetchArxiv($url, $limit),

            // HAL JSON
            $key === 'hal_math'                  => $this->fetchHalJson($url, $limit),

            // bioRxiv / medRxiv JSON
            $key === 'biorxiv_recent',
            $key === 'medrxiv_recent'           => $this->fetchRxivJson($url, $limit),

            // OSF Preprints (PsyArXiv, SocArXiv, EdArXiv)
            str_starts_with($key, 'psyarxiv_'),
            str_starts_with($key, 'socarxiv_'),
            str_starts_with($key, 'edarxiv_')  => $this->fetchOsfPreprints($url, $limit),

            // Europe PMC
            str_starts_with($key, 'europepmc_') => $this->fetchEuropePmc($url, $limit),

            // fallback: try RSS/Atom
            default                             => $this->fetchRssOrAtom($url, $limit),
        };
    }

    /** Shared HTTP client with browser-like headers */
    private function client()
    {
        return Http::withHeaders([
                'User-Agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X) ResearchDigest/1.0',
                'Accept'          => 'application/atom+xml, application/rss+xml, application/json;q=0.9, application/xml;q=0.8, */*;q=0.5',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])
            ->retry(2, 250)
            ->timeout(20);
    }

    /** arXiv Atom */
    private function fetchArxiv(string $url, int $limit): array
    {
        $res = $this->client()->get($url);
        if (!$res->ok()) {
            throw new \RuntimeException("HTTP {$res->status()}: " . substr($res->body(), 0, 120));
        }
        $xml = @simplexml_load_string($res->body());
        if (!$xml) return [];

        $items = [];
        $i = 0;
        foreach ($xml->entry ?? [] as $e) {
            if ($i++ >= $limit) break;

            $title   = trim((string) $e->title);
            $summary = trim((string) $e->summary);

            $link = '';
            foreach ($e->link as $lnk) {
                $attrs = $lnk->attributes();
                if ((string)($attrs['rel'] ?? '') === 'alternate') {
                    $link = (string) ($attrs['href'] ?? '');
                    break;
                }
            }
            if (!$link && isset($e->id)) $link = (string) $e->id;

            $items[] = [
                'title'   => $title ?: '(untitled)',
                'url'     => $link,
                'summary' => $summary,
            ];
        }
        return $items;
    }

    /** bioRxiv / medRxiv JSON (details endpoint with N most recent) */
    private function fetchRxivJson(string $url, int $limit): array
    {
        $res = $this->client()->get($url);
        if (!$res->ok()) {
            throw new \RuntimeException("HTTP {$res->status()}: " . substr($res->body(), 0, 120));
        }
        $data = $res->json();

        // bioRxiv/medRxiv “details” responses typically put entries under 'collection'
        $entries = collect($data['collection'] ?? $data['records'] ?? $data['data'] ?? [])
            ->filter() // remove nulls
            ->take($limit);

        return $entries->map(function ($r) {
            // Common keys across bioRxiv/medRxiv
            $title = $r['title'] ?? '(untitled)';
            $abs   = $r['abstract'] ?? $r['abstract_text'] ?? '';
            $doi   = $r['doi'] ?? null;

            // Universal link via DOI (resolves to host page)
            $link  = $doi ? ('https://doi.org/' . $doi) : ($r['biorxiv_url'] ?? $r['medrxiv_url'] ?? '#');

            return [
                'title'   => $title,
                'url'     => $link,
                'summary' => $abs,
            ];
        })->values()->all();
    }

    /** OSF Preprints JSON:API (PsyArXiv, SocArXiv, EdArXiv) */
    private function fetchOsfPreprints(string $url, int $limit): array
    {
        $res = $this->client()->get($url);
        if (!$res->ok()) {
            throw new \RuntimeException("HTTP {$res->status()}: " . substr($res->body(), 0, 120));
        }
        $data = $res->json();

        return collect($data['data'] ?? [])
            ->take($limit)
            ->map(function ($item) {
                $attrs = $item['attributes'] ?? [];
                $title = $attrs['title'] ?? '(untitled)';
                $abstract = $attrs['description'] ?? '';
                $doi = $attrs['doi'] ?? null;
                $link = $doi ? ('https://doi.org/' . $doi) : ($item['links']['html'] ?? '#');

                return [
                    'title'   => $title,
                    'url'     => $link,
                    'summary' => $abstract,
                ];
            })->values()->all();
    }

    /** Europe PMC REST JSON */
    private function fetchEuropePmc(string $url, int $limit): array
    {
        $res = $this->client()->get($url);
        if (!$res->ok()) {
            throw new \RuntimeException("HTTP {$res->status()}: " . substr($res->body(), 0, 120));
        }
        $data = $res->json();

        return collect($data['resultList']['result'] ?? [])
            ->take($limit)
            ->map(function ($r) {
                $title = $r['title'] ?? '(untitled)';
                $abstract = $r['abstractText'] ?? '';
                $doi = $r['doi'] ?? null;
                $link = $doi ? ('https://doi.org/' . $doi) : '#';

                return [
                    'title'   => $title,
                    'url'     => $link,
                    'summary' => $abstract,
                ];
            })->values()->all();
    }

    /** RSS or Atom (fallback) */
    private function fetchRssOrAtom(string $url, int $limit): array
    {
        $res = $this->client()->get($url);
        if (!$res->ok()) {
            throw new \RuntimeException("HTTP {$res->status()}: " . substr($res->body(), 0, 120));
        }
        $xml = @simplexml_load_string($res->body());
        if (!$xml) return [];

        $root = strtolower($xml->getName());
        if ($root === 'feed') {
            // Atom
            $items = [];
            $i = 0;
            foreach ($xml->entry ?? [] as $e) {
                if ($i++ >= $limit) break;
                $link = '';
                foreach ($e->link as $lnk) {
                    $attrs = $lnk->attributes();
                    if ((string)($attrs['rel'] ?? '') === 'alternate') {
                        $link = (string) ($attrs['href'] ?? '');
                        break;
                    }
                }
                if (!$link && isset($e->id)) $link = (string) $e->id;
                $summary = trim((string) ($e->summary ?? $e->content ?? ''));
                $items[] = [
                    'title'   => trim((string) $e->title) ?: '(untitled)',
                    'url'     => $link,
                    'summary' => strip_tags($summary),
                ];
            }
            return $items;
        }

        // RSS
        $items = [];
        foreach ($xml->channel->item ?? [] as $i => $item) {
            if ($i >= $limit) break;
            $summary = (string) ($item->description ?? $item->children('content', true)->encoded ?? '');
            $items[] = [
                'title'   => trim((string) $item->title) ?: '(untitled)',
                'url'     => trim((string) $item->link),
                'summary' => trim(strip_tags($summary)),
            ];
        }
        return $items;
    }
}
