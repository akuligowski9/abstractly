<?php

namespace App\Services;

class PaperDeduplicator
{
    /**
     * Remove duplicate papers across sources within a single discipline.
     *
     * First occurrence is kept and annotated with 'also_in' (list of other
     * source labels that had the same paper). Later occurrences are removed.
     *
     * @param  array<string, array>  $fetchedBySource  ['Source Label' => [items], ...]
     * @return array<string, array>  Same structure, deduped
     */
    public function dedup(array $fetchedBySource): array
    {
        if (count($fetchedBySource) <= 1) {
            return $fetchedBySource;
        }

        // Track which normalized URL we've seen and where (source label + item index).
        $seen = []; // normalizedUrl => ['sourceLabel' => string, 'itemIndex' => int]

        // First pass: record first occurrence of each URL.
        foreach ($fetchedBySource as $label => $items) {
            foreach ($items as $idx => $item) {
                $norm = $this->normalizeUrl($item['url'] ?? '');

                if ($norm === null) {
                    continue; // non-dedupable URL — always keep
                }

                if (!isset($seen[$norm])) {
                    $seen[$norm] = ['sourceLabel' => $label, 'itemIndex' => $idx];
                }
            }
        }

        // Second pass: annotate first occurrences with also_in, remove later dupes.
        $result = [];
        foreach ($fetchedBySource as $label => $items) {
            $kept = [];
            foreach ($items as $idx => $item) {
                $norm = $this->normalizeUrl($item['url'] ?? '');

                if ($norm === null) {
                    $kept[] = $item; // non-dedupable — always keep
                    continue;
                }

                $owner = $seen[$norm];

                if ($owner['sourceLabel'] === $label && $owner['itemIndex'] === $idx) {
                    // This is the first occurrence — keep it (also_in added below).
                    $kept[] = $item;
                }
                // else: duplicate in a later source — skip it, but record for also_in
            }
            $result[$label] = $kept;
        }

        // Third pass: build also_in annotations on first occurrences.
        foreach ($fetchedBySource as $label => $items) {
            foreach ($items as $idx => $item) {
                $norm = $this->normalizeUrl($item['url'] ?? '');
                if ($norm === null) {
                    continue;
                }

                $owner = $seen[$norm];
                if ($owner['sourceLabel'] === $label && $owner['itemIndex'] === $idx) {
                    continue; // this IS the owner — skip
                }

                // Find the owner item in $result and append this source label.
                foreach ($result[$owner['sourceLabel']] as &$ownerItem) {
                    $ownerNorm = $this->normalizeUrl($ownerItem['url'] ?? '');
                    if ($ownerNorm === $norm) {
                        if (!isset($ownerItem['also_in'])) {
                            $ownerItem['also_in'] = [];
                        }
                        if (!in_array($label, $ownerItem['also_in'], true)) {
                            $ownerItem['also_in'][] = $label;
                        }
                        break;
                    }
                }
                unset($ownerItem);
            }
        }

        return $result;
    }

    /**
     * Normalize a URL for dedup comparison.
     *
     * Returns null for non-dedupable URLs (#, empty, whitespace-only).
     */
    public function normalizeUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '' || $url === '#') {
            return null;
        }

        $url = strtolower($url);
        $url = preg_replace('#^http://#', 'https://', $url);
        $url = rtrim($url, '/');

        // Strip arXiv version suffix: /abs/2501.12345v3 → /abs/2501.12345
        $url = preg_replace('#(arxiv\.org/abs/[\d.]+)v\d+#', '$1', $url);

        return $url;
    }
}
