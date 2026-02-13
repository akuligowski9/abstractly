<div>
    {{-- Breadcrumb --}}
    <nav class="mb-6 text-sm text-gray-500 flex items-center gap-1.5">
        <a href="{{ route('disciplines.index') }}" wire:navigate class="hover:text-gray-900 transition">Disciplines</a>
        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 font-medium">{{ $label }}</span>
    </nav>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ $label }} — Sources</h1>
        <p class="mt-2 text-sm text-gray-600">
            Choose which sources feed your weekly digest for <strong>{{ $label }}</strong>.
        </p>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <div class="text-sm text-gray-700">
            <span class="font-medium">{{ count($selected) }}</span> / {{ count($sources) }} selected
        </div>
        <div class="flex gap-2">
            <button wire:click="selectAll" type="button"
                    class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg px-4 py-2.5 text-sm font-medium transition">
                Select all
            </button>
            <button wire:click="selectNone" type="button"
                    class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg px-4 py-2.5 text-sm font-medium transition">
                Select none
            </button>
        </div>
    </div>

    <div class="space-y-3">
        @foreach ($sources as $s)
            <div
                wire:click="toggleSource('{{ $s['key'] }}')"
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 cursor-pointer transition
                    hover:border-indigo-200 hover:shadow-md
                    {{ in_array($s['key'], $selected, true) ? 'ring-2 ring-indigo-500 border-indigo-200' : '' }}"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        {{-- Visual checkbox --}}
                        <div class="mt-0.5 flex-shrink-0 w-5 h-5 rounded border-2 flex items-center justify-center transition
                            {{ in_array($s['key'], $selected, true) ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300 bg-white' }}">
                            @if (in_array($s['key'], $selected, true))
                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            @endif
                        </div>

                        <div>
                            <div class="font-medium text-gray-900">{{ $s['label'] }}</div>
                            <div class="flex items-center gap-2 mt-1">
                                {{-- Kind badge --}}
                                @php
                                    $kindColors = match($s['kind'] ?? 'source') {
                                        'primary' => 'bg-indigo-50 text-indigo-700',
                                        'json'    => 'bg-emerald-50 text-emerald-700',
                                        default   => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $kindColors }}">
                                    {{ $s['kind'] ?? 'source' }}
                                </span>

                                <span class="text-xs text-gray-500">{{ $s['signal'] ?? '' }}</span>
                            </div>
                            @if (!empty($s['notes']))
                                <div class="text-xs text-gray-500 mt-1.5">{{ $s['notes'] }}</div>
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('sources.preview', [$slug, $s['key']]) }}"
                       class="shrink-0 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg px-3 py-1.5 text-sm font-medium transition"
                       onclick="event.stopPropagation()">
                        Preview
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8 flex flex-wrap items-center gap-3">
        <button wire:click="save" type="button"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg px-4 py-2.5 transition">
            Save sources
        </button>

        <a href="{{ route('disciplines.index') }}" wire:navigate
           class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg px-4 py-2.5 font-medium transition">
            Back to disciplines
        </a>
    </div>
</div>
