<x-layouts.app :title="'Preview — ' . $source['label']">
    {{-- Breadcrumb --}}
    <nav class="mb-6 text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
        <a href="{{ route('disciplines.index') }}" wire:navigate class="hover:text-gray-900 dark:hover:text-gray-200 transition">Disciplines</a>
        <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('disciplines.show', $slug) }}" wire:navigate class="hover:text-gray-900 dark:hover:text-gray-200 transition">{{ $label }}</a>
        <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-medium">Preview</span>
    </nav>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Preview: {{ $source['label'] }}</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Showing up to {{ $limit }} items from <code class="bg-gray-100 dark:bg-gray-900 rounded px-1.5 py-0.5 text-xs">{{ $source['url'] }}</code>.
        </p>
    </div>

    @if ($error ?? false)
        <div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/40 border border-red-200 dark:border-red-800 p-3 text-sm text-red-800 dark:text-red-300">
            Error: {{ $error }}
        </div>
    @endif

    @if (empty($items))
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-8 text-center">
            <p class="text-gray-500 dark:text-gray-400">No items returned.</p>
        </div>
    @else
        <ul class="space-y-3">
            @foreach ($items as $i => $it)
                <li class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-5">
                    <div class="text-xs text-gray-400 dark:text-gray-500 font-medium">#{{ $i + 1 }}</div>
                    <div class="font-medium text-gray-900 dark:text-gray-100 mt-1">{{ $it['title'] ?? '(untitled)' }}</div>
                    @if (!empty($it['url']))
                        <a href="{{ $it['url'] }}" target="_blank" rel="noopener"
                           class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-sm transition mt-1 inline-block">
                            Open source &rarr;
                        </a>
                    @endif
                    @if (!empty($it['summary']))
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($it['summary'], 400) }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    <div class="mt-8">
        <a href="{{ route('disciplines.show', $slug) }}" wire:navigate
           class="bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg px-4 py-2.5 font-medium transition inline-block">
            Back to {{ $label }}
        </a>
    </div>
</x-layouts.app>
