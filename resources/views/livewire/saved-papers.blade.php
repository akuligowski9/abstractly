<div>
    {{-- Breadcrumb --}}
    <nav class="mb-6 text-sm text-gray-500 flex items-center gap-1.5">
        <a href="{{ route('disciplines.index') }}" wire:navigate class="hover:text-gray-900 transition">Disciplines</a>
        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 font-medium">Saved Papers</span>
    </nav>

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Saved Papers</h1>
            <p class="mt-1 text-sm text-gray-600">
                @if (count($papers) > 0)
                    {{ count($papers) }} paper{{ count($papers) !== 1 ? 's' : '' }} saved.
                @else
                    No papers saved yet.
                @endif
            </p>
        </div>

        @if (count($papers) > 0)
            <div class="flex items-center gap-3 shrink-0">
                <button wire:click="export"
                        type="button"
                        class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg px-4 py-2.5 font-medium transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export JSON
                </button>
                <button wire:click="clearAll"
                        wire:confirm="Remove all saved papers? This cannot be undone."
                        type="button"
                        class="bg-white border border-red-300 text-red-600 hover:bg-red-50 rounded-lg px-4 py-2.5 font-medium transition">
                    Clear All
                </button>
            </div>
        @endif
    </div>

    @if (empty($papers))
        {{-- Empty state --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
            <div class="text-gray-400 mb-3">
                <svg class="w-12 h-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z"/>
                </svg>
            </div>
            <p class="text-gray-700 font-medium">No saved papers</p>
            <p class="text-sm text-gray-500 mt-1">
                Bookmark papers from your <a href="{{ route('digest.show') }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">digest</a> to save them here.
            </p>
        </div>
    @else
        {{-- Paper cards --}}
        <div class="space-y-4">
            @foreach ($papers as $paper)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-start gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ $paper['url'] }}" target="_blank" rel="noopener"
                                   class="font-medium text-indigo-600 hover:text-indigo-800 transition">
                                    {{ $paper['title'] }}
                                </a>
                                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                    {{ $paper['discipline'] }}
                                </span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                                    {{ $paper['source'] }}
                                </span>
                            </div>

                            @if (!empty($paper['saved_at']))
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Saved {{ \Carbon\Carbon::parse($paper['saved_at'])->diffForHumans() }}
                                </p>
                            @endif

                            @if (!empty($paper['also_in']))
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Also in:
                                    @foreach ($paper['also_in'] as $src)
                                        <span class="inline-flex items-center rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600">{{ $src }}</span>{{ !$loop->last ? ' ' : '' }}
                                    @endforeach
                                </p>
                            @endif

                            <div class="mt-3 space-y-2">
                                @if (!empty($paper['eli5']))
                                    <div class="border-l-4 border-green-400 pl-3 py-1">
                                        <div class="text-xs font-semibold text-green-700 uppercase tracking-wide mb-0.5">ELI5</div>
                                        <p class="text-sm text-gray-700">{{ $paper['eli5'] }}</p>
                                    </div>
                                @endif

                                @if (!empty($paper['swe']))
                                    <div class="border-l-4 border-blue-400 pl-3 py-1">
                                        <div class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-0.5">Solo SWE</div>
                                        <p class="text-sm text-gray-700">{{ $paper['swe'] }}</p>
                                    </div>
                                @endif

                                @if (!empty($paper['investor']))
                                    <div class="border-l-4 border-amber-400 pl-3 py-1">
                                        <div class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-0.5">Investor</div>
                                        <p class="text-sm text-gray-700">{{ $paper['investor'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <button wire:click="removePaper({{ @js($paper['url']) }})"
                                title="Remove from saved"
                                class="shrink-0 p-1.5 rounded hover:bg-red-50 text-gray-400 hover:text-red-500 transition"
                                aria-label="Remove paper">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Footer note --}}
        <p class="mt-6 text-xs text-gray-400 text-center">
            Saved papers persist in a local JSON file and survive session expiry.
        </p>
    @endif
</div>
