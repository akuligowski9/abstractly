<div>
    {{-- Breadcrumb --}}
    <nav class="mb-6 text-sm text-gray-500 flex items-center gap-1.5">
        <a href="{{ route('disciplines.index') }}" wire:navigate class="hover:text-gray-900 transition">Disciplines</a>
        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 font-medium">Digest</span>
    </nav>

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Weekly Digest</h1>
            <p class="mt-1 text-sm text-gray-600">AI-powered summaries from your selected sources.</p>
        </div>

        <div class="flex gap-2 shrink-0">
            <button wire:click="generate"
                    wire:loading.attr="disabled"
                    wire:target="generate"
                    type="button"
                    class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-wait text-white font-medium rounded-lg px-4 py-2.5 transition">
                <span wire:loading.remove wire:target="generate">Generate digest</span>
                <span wire:loading wire:target="generate" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Generating&hellip;
                </span>
            </button>

            <a href="{{ route('disciplines.index') }}" wire:navigate
               class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg px-4 py-2.5 font-medium transition">
                Edit selection
            </a>
        </div>
    </div>

    {{-- Loading overlay --}}
    <div wire:loading wire:target="generate" class="mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
            <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-gray-700 font-medium">Generating your digest&hellip;</p>
            <p class="text-sm text-gray-500 mt-1">Fetching papers and running AI summaries. This may take a minute or two.</p>
        </div>
    </div>

    {{-- Empty state --}}
    @php $hasAny = !empty($digest) && collect($digest)->sum(fn($d) => count($d['sections'] ?? [])) > 0; @endphp

    <div wire:loading.remove wire:target="generate">
        @if (!$hasAny)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                <div class="text-gray-400 mb-3">
                    <svg class="w-12 h-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <p class="text-gray-700 font-medium">No digest yet</p>
                <p class="text-sm text-gray-500 mt-1">
                    Select disciplines and sources, then click <strong>Generate digest</strong> to get started.
                </p>
            </div>
        @else
            {{-- Digest content --}}
            <div class="space-y-8">
                @foreach ($digest as $d)
                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $d['discipline'] }}</h2>

                        <div class="space-y-4">
                            @foreach ($d['sections'] as $sec)
                                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                                    <h3 class="font-semibold text-gray-900 mb-3">{{ $sec['source'] }}</h3>

                                    <div class="space-y-4">
                                        @foreach ($sec['items'] as $it)
                                            <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-4">
                                                <a href="{{ $it['url'] }}" target="_blank" rel="noopener"
                                                   class="font-medium text-indigo-600 hover:text-indigo-800 transition">
                                                    {{ $it['title'] }}
                                                </a>

                                                @if (!empty($it['summary']))
                                                    <p class="text-xs text-gray-500 mt-1 line-clamp-3">{{ $it['summary'] }}</p>
                                                @endif

                                                <div class="mt-3 space-y-2">
                                                    {{-- ELI5 — green --}}
                                                    @if (!empty($it['eli5']))
                                                        <div class="border-l-4 border-green-400 pl-3 py-1">
                                                            <div class="text-xs font-semibold text-green-700 uppercase tracking-wide mb-0.5">ELI5</div>
                                                            <p class="text-sm text-gray-700">{{ $it['eli5'] }}</p>
                                                        </div>
                                                    @endif

                                                    {{-- SWE — blue --}}
                                                    @if (!empty($it['swe']))
                                                        <div class="border-l-4 border-blue-400 pl-3 py-1">
                                                            <div class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-0.5">Solo SWE</div>
                                                            <p class="text-sm text-gray-700">{{ $it['swe'] }}</p>
                                                        </div>
                                                    @endif

                                                    {{-- Investor — amber --}}
                                                    @if (!empty($it['investor']))
                                                        <div class="border-l-4 border-amber-400 pl-3 py-1">
                                                            <div class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-0.5">Investor</div>
                                                            <p class="text-sm text-gray-700">{{ $it['investor'] }}</p>
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
                @endforeach
            </div>
        @endif
    </div>
</div>
