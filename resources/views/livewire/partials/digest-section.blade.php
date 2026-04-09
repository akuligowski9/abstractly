<section class="mb-8">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ $d['discipline'] }}</h2>

    <div class="space-y-4">
        @foreach ($d['sections'] as $sec)
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ $sec['source'] }}</h3>

                <div class="space-y-4">
                    @foreach ($sec['items'] as $it)
                        <div class="rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/30 p-4">
            <div class="flex items-start gap-2">
                <a href="{{ $it['url'] }}" target="_blank" rel="noopener"
                   class="font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition flex-1">
                    {{ $it['title'] }}
                </a>
                @if (isset($savedUrls))
                    <button wire:click="toggleSave('{{ $it['url'] }}')"
                            title="{{ in_array($it['url'], $savedUrls) ? 'Remove from saved' : 'Save paper' }}"
                            class="shrink-0 p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                            aria-label="{{ in_array($it['url'], $savedUrls) ? 'Remove from saved' : 'Save paper' }}">
                        @if (in_array($it['url'], $savedUrls))
                            <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M5 2h14a1 1 0 011 1v19.143a.5.5 0 01-.766.424L12 18.03l-7.234 4.536A.5.5 0 014 22.143V3a1 1 0 011-1z"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 hover:text-amber-500 dark:hover:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z"/>
                            </svg>
                        @endif
                    </button>
                @else
                    <span class="shrink-0 p-1">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z"/>
                        </svg>
                    </span>
                @endif
            </div>

            @if (!empty($it['also_in']))
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                    Also in:
                    @foreach ($it['also_in'] as $src)
                        <span class="inline-flex items-center rounded bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 text-xs font-medium text-gray-600 dark:text-gray-400">{{ $src }}</span>{{ !$loop->last ? ' ' : '' }}
                    @endforeach
                </p>
            @endif

            @if (!empty($it['summary']))
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-3">{{ $it['summary'] }}</p>
            @endif

            <div class="mt-3 space-y-2">
                @if (!empty($it['eli5']))
                    <div class="border-l-4 border-green-400 dark:border-green-600 pl-3 py-1">
                        <div class="text-xs font-semibold text-green-700 dark:text-green-400 uppercase tracking-wide mb-0.5">ELI5</div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $it['eli5'] }}</p>
                    </div>
                @endif

                @if (!empty($it['swe']))
                    <div class="border-l-4 border-blue-400 dark:border-blue-600 pl-3 py-1">
                        <div class="text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wide mb-0.5">Solo SWE</div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $it['swe'] }}</p>
                    </div>
                @endif

                @if (!empty($it['investor']))
                    <div class="border-l-4 border-amber-400 dark:border-amber-600 pl-3 py-1">
                        <div class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wide mb-0.5">Investor</div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $it['investor'] }}</p>
                    </div>
                @endif
            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</section>
