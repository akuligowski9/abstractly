@inject('savedPapers', 'App\Services\SavedPapersRepository')
<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Abstractly' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
</head>
<body class="min-h-full bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 flex flex-col transition-colors duration-300">
    {{-- Navigation --}}
    <nav class="bg-white dark:bg-gray-950 border-b border-gray-200 dark:border-gray-900 transition-colors duration-300" x-data="{
        darkMode: localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
        toggle() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('color-theme', this.darkMode ? 'dark' : 'light');
            if (this.darkMode) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    }" x-init="$watch('darkMode', val => val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark')); if(darkMode) document.documentElement.classList.add('dark');">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">
                <div class="flex items-center gap-6">
                    <a href="{{ route('disciplines.index') }}" wire:navigate class="text-lg font-bold tracking-tight text-gray-900">
                        Abstractly
                    </a>
                    <div class="hidden sm:flex items-center gap-4 text-sm font-medium">
                        <a href="{{ route('disciplines.index') }}" wire:navigate
                           class="{{ request()->routeIs('disciplines.*') ? 'text-indigo-600' : 'text-gray-600 hover:text-gray-900' }}">
                            Disciplines
                        </a>
                        <a href="{{ route('digest.show') }}" wire:navigate
                           class="{{ request()->routeIs('digest.*') ? 'text-indigo-600' : 'text-gray-600 hover:text-gray-900' }}">
                            Digest
                        </a>
                        <a href="{{ route('saved.index') }}" wire:navigate
                           class="flex items-center gap-1.5 {{ request()->routeIs('saved.*') ? 'text-indigo-600' : 'text-gray-600 hover:text-gray-900' }}">
                            Saved
                            @if ($savedPapers->count() > 0)
                                <span class="inline-flex items-center justify-center rounded-full bg-amber-100 text-amber-700 text-xs font-semibold min-w-[1.25rem] h-5 px-1.5">{{ $savedPapers->count() }}</span>
                            @endif
                        </a>
                    </div>
                </div>

                {{-- Dark Mode Toggle --}}
                <button type="button" @click="toggle()"
                        class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                    <svg x-show="darkMode" style="display: none;" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                </button>
            </div>
        </div>
    </nav>

    {{-- Main content --}}
    <main class="flex-1 w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="border-t border-gray-200 dark:border-gray-900 bg-white dark:bg-gray-950 transition-colors duration-300">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-xs text-gray-400 dark:text-gray-500 text-center">
                Abstractly &mdash; AI-powered weekly summaries
            </p>
        </div>
    </footer>
</body>
</html>
