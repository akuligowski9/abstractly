<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Weekly Digest — Disciplines</h1>
        <p class="mt-2 text-sm text-gray-600">
            Toggle which disciplines are included. Your selection is kept in the session for now.
        </p>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <div class="text-sm text-gray-700">
            <span class="font-medium">{{ $countSel }}</span> / {{ $countAll }} selected
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

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @foreach ($all as $slug => $meta)
            @php $ready = $meta['ready'] ?? false; @endphp

            <div
                @if ($ready) wire:click="toggleDiscipline('{{ $slug }}')" @endif
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-start justify-between gap-3 transition
                    {{ $ready ? 'cursor-pointer hover:border-indigo-200 hover:shadow-md' : 'opacity-50 cursor-not-allowed' }}
                    {{ $ready && in_array($slug, $selected, true) ? 'ring-2 ring-indigo-500 border-indigo-200' : '' }}"
            >
                <div class="flex items-start gap-3">
                    {{-- Visual checkbox indicator --}}
                    <div class="mt-0.5 flex-shrink-0 w-5 h-5 rounded border-2 flex items-center justify-center transition
                        {{ in_array($slug, $selected, true) ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300 bg-white' }}
                        {{ !$ready ? 'border-gray-200 bg-gray-100' : '' }}">
                        @if (in_array($slug, $selected, true))
                            <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        @endif
                    </div>

                    <div>
                        <div class="font-medium text-gray-900">{{ $meta['label'] }}</div>
                        <div class="text-xs text-gray-500">{{ $slug }}</div>
                        @unless ($ready)
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-500 mt-1">
                                Coming soon
                            </span>
                        @endunless
                    </div>
                </div>

                @if ($ready)
                    <a href="{{ route('disciplines.show', $slug) }}"
                       wire:navigate
                       class="shrink-0 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg px-3 py-1.5 text-sm font-medium transition"
                       onclick="event.stopPropagation()">
                        Sources
                    </a>
                @else
                    <span class="shrink-0 px-3 py-1.5 rounded-lg bg-gray-100 text-sm text-gray-400">Sources</span>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-8 flex flex-wrap items-center gap-3">
        <button wire:click="save" type="button"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg px-4 py-2.5 transition">
            Save selection
        </button>

        <a href="{{ route('digest.show') }}" wire:navigate
           class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg px-4 py-2.5 font-medium transition">
            View digest
        </a>
    </div>
</div>
