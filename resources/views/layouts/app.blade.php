<!DOCTYPE html>
<html lang="id" class="h-full bg-neutral-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Timeline Generator' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full">
    <div class="flex h-full">

        {{-- ── Sidebar ── --}}
        <aside class="w-56 shrink-0 flex flex-col bg-white border-r border-neutral-200 h-screen sticky top-0">
            {{-- Logo --}}
            <div class="h-14 flex items-center gap-2.5 px-5 border-b border-neutral-100">
                <div class="w-7 h-7 rounded-lg bg-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <span class="font-semibold text-neutral-900 text-sm leading-tight">Timeline<br>Generator</span>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
                @php
                    $navItem = function(string $label, string $route, string $icon) use (&$navItem): string {
                        $active = request()->routeIs($route) || str_starts_with(request()->route()?->getName() ?? '', rtrim($route, '.*'));
                        $base   = 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors w-full text-left';
                        $cls    = $active
                            ? "$base bg-blue-50 text-blue-700"
                            : "$base text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900";
                        return "<a href=\"" . route($route) . "\" class=\"$cls\">$icon<span>$label</span></a>";
                    };
                @endphp

                {!! $navItem(
                    'Projects',
                    'projects.index',
                    '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>'
                ) !!}

                @if(auth()->user()->isAdmin())
                {!! $navItem(
                    'Holidays',
                    'admin.holidays',
                    '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
                ) !!}
                {!! $navItem(
                    'Users',
                    'admin.users',
                    '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'
                ) !!}
                @endif
            </nav>

            {{-- User + Logout --}}
            <div class="border-t border-neutral-100 px-3 py-3">
                <div class="flex items-center gap-2.5 px-2 mb-2">
                    <div class="w-7 h-7 rounded-full bg-neutral-200 flex items-center justify-center shrink-0">
                        <span class="text-xs font-semibold text-neutral-600">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-neutral-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-neutral-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-neutral-500 hover:bg-neutral-100 hover:text-neutral-900 transition-colors">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- ── Main content ── --}}
        <div class="flex-1 min-w-0 overflow-y-auto">
            <main class="max-w-screen-xl mx-auto px-6 py-6">
                {{ $slot }}
            </main>
        </div>

    </div>

    @livewireScripts
</body>
</html>
