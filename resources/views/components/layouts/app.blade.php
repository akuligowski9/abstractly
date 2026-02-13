<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Research Digest' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-gray-50 text-gray-900 flex flex-col">
    {{-- Navigation --}}
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">
                <div class="flex items-center gap-6">
                    <a href="{{ route('disciplines.index') }}" class="text-lg font-bold tracking-tight text-gray-900">
                        Research Digest
                    </a>
                    <div class="hidden sm:flex items-center gap-4 text-sm font-medium">
                        <a href="{{ route('disciplines.index') }}"
                           class="{{ request()->routeIs('disciplines.*') ? 'text-indigo-600' : 'text-gray-600 hover:text-gray-900' }}">
                            Disciplines
                        </a>
                        <a href="{{ route('digest.show') }}"
                           class="{{ request()->routeIs('digest.*') ? 'text-indigo-600' : 'text-gray-600 hover:text-gray-900' }}">
                            Digest
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    {{-- Main content --}}
    <main class="flex-1 w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="border-t border-gray-200 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-xs text-gray-400 text-center">
                Research Digest &mdash; AI-powered weekly summaries
            </p>
        </div>
    </footer>
</body>
</html>
