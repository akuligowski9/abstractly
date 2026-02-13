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

        <div class="flex items-center gap-3 shrink-0">
            <label class="flex items-center gap-1.5 text-sm text-gray-600 cursor-pointer select-none">
                <input type="checkbox" wire:model="forceRefresh"
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                Skip cache
            </label>

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

            @if (!empty($digest) && collect($digest)->sum(fn($d) => count($d['sections'] ?? [])) > 0)
                <button wire:click="export"
                        type="button"
                        class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg px-4 py-2.5 font-medium transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export JSON
                </button>
            @endif

            <a href="{{ route('disciplines.index') }}" wire:navigate
               class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg px-4 py-2.5 font-medium transition">
                Edit selection
            </a>
        </div>
    </div>

    {{-- Progress bar (visible during generate) --}}
    <div wire:loading wire:target="generate" class="mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <svg class="animate-spin h-5 w-5 text-indigo-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p wire:stream="progress-status" class="text-sm text-gray-700 font-medium">Starting generation&hellip;</p>
            </div>
            <div class="mt-3 h-2 bg-gray-100 rounded-full overflow-hidden">
                <div wire:stream="progress-bar" class="h-full bg-indigo-600 rounded-full" style="width: 0%"></div>
            </div>
            <p wire:stream="progress-count" class="mt-1.5 text-xs text-gray-400">Preparing&hellip;</p>
        </div>
    </div>

    {{-- Streamed results (visible during generate, appended progressively) --}}
    <div wire:loading wire:target="generate">
        <div wire:stream="digest-stream"></div>
    </div>

    {{-- Failure warnings (visible after generate if any sources failed) --}}
    @if (!empty($failures))
        <div wire:loading.remove wire:target="generate" class="mb-6">
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-amber-800">
                            {{ count($failures) }} source{{ count($failures) !== 1 ? 's' : '' }} failed during generation
                        </p>
                        <ul class="mt-1 text-sm text-amber-700 list-disc list-inside">
                            @foreach ($failures as $f)
                                @if ($f['type'] === 'rate_limit')
                                    <li>{{ $f['source'] }} — daily free tier limit reached, try again tomorrow</li>
                                @elseif ($f['type'] === 'fetch')
                                    <li>{{ $f['source'] }} (fetch failed)</li>
                                @else
                                    <li>{{ $f['source'] }} (summarization failed)</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Final content (hidden during generate) --}}
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
            @foreach ($digest as $d)
                @include('livewire.partials.digest-section', ['d' => $d, 'savedUrls' => $savedUrls])
            @endforeach
        @endif
    </div>
</div>
