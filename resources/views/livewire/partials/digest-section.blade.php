<section class="mb-8">
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

                            @if (!empty($it['also_in']))
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Also in:
                                    @foreach ($it['also_in'] as $src)
                                        <span class="inline-flex items-center rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600">{{ $src }}</span>{{ !$loop->last ? ' ' : '' }}
                                    @endforeach
                                </p>
                            @endif

                            @if (!empty($it['summary']))
                                <p class="text-xs text-gray-500 mt-1 line-clamp-3">{{ $it['summary'] }}</p>
                            @endif

                            <div class="mt-3 space-y-2">
                                @if (!empty($it['eli5']))
                                    <div class="border-l-4 border-green-400 pl-3 py-1">
                                        <div class="text-xs font-semibold text-green-700 uppercase tracking-wide mb-0.5">ELI5</div>
                                        <p class="text-sm text-gray-700">{{ $it['eli5'] }}</p>
                                    </div>
                                @endif

                                @if (!empty($it['swe']))
                                    <div class="border-l-4 border-blue-400 pl-3 py-1">
                                        <div class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-0.5">Solo SWE</div>
                                        <p class="text-sm text-gray-700">{{ $it['swe'] }}</p>
                                    </div>
                                @endif

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
