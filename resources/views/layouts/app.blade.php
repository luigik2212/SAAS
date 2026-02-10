<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? config('app.name', 'NOME_DO_SAAS') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bg: '#0B0B12',
                        surface: '#111122',
                        card: '#14142B',
                        primary: '#7C3AED',
                        text: '#EDEDF7',
                        muted: '#A9A9C2',
                    },
                    borderRadius: {
                        xl2: '14px',
                    },
                    boxShadow: {
                        glow: '0 0 0 3px rgba(124, 58, 237, .25)',
                    },
                },
            },
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-bg text-text antialiased">
<div x-data="{ mobileMenu: false }" class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">
    <aside class="hidden lg:flex flex-col border-r border-white/10 bg-surface/85 backdrop-blur px-4 py-6">
        <div class="mb-8 px-2">
            <p class="text-xs uppercase tracking-widest text-muted">NOME_DO_SAAS</p>
            <h1 class="mt-1 text-xl font-semibold">Sprint 1</h1>
        </div>

        <nav class="space-y-2">
            <a href="{{ url('/dashboard') }}" class="block rounded-xl2 border border-white/10 bg-card px-4 py-3 font-medium text-text transition hover:border-primary/70 hover:text-white focus:outline-none focus:ring-2 focus:ring-primary/70">Dashboard</a>
            <a href="{{ url('/contas') }}" class="block rounded-xl2 border border-white/10 px-4 py-3 text-muted transition hover:border-primary/70 hover:bg-card hover:text-text focus:outline-none focus:ring-2 focus:ring-primary/70">Contas</a>
            <a href="{{ url('/logout') }}" class="block rounded-xl2 border border-white/10 px-4 py-3 text-muted transition hover:border-rose-500/60 hover:bg-rose-500/10 hover:text-rose-200 focus:outline-none focus:ring-2 focus:ring-rose-500/70">Sair</a>
        </nav>

        <p class="mt-auto px-2 text-xs text-muted">Controle financeiro premium</p>
    </aside>

    <main class="pb-24 lg:pb-8">
        <header class="sticky top-0 z-30 border-b border-white/10 bg-bg/95 px-4 py-3 backdrop-blur lg:px-8">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
                <div class="flex items-center gap-3 lg:hidden">
                    <button @click="mobileMenu = !mobileMenu" class="inline-flex h-10 w-10 items-center justify-center rounded-xl2 border border-white/10 bg-card text-muted hover:text-text focus:outline-none focus:ring-2 focus:ring-primary/70">
                        <span class="sr-only">Abrir menu</span>
                        ☰
                    </button>
                    <h2 class="text-sm font-semibold">{{ $mobileTitle ?? 'NOME_DO_SAAS' }}</h2>
                </div>

                <div class="ml-auto flex items-center gap-2">
                    <x-badge variant="primary">Hoje: {{ $todayCount ?? 0 }}</x-badge>
                    <x-badge variant="danger">Atraso: {{ $lateCount ?? 0 }}</x-badge>
                </div>
            </div>
        </header>

        <div x-cloak x-show="mobileMenu" @click="mobileMenu = false" class="fixed inset-0 z-40 bg-black/60 lg:hidden"></div>
        <aside x-cloak x-show="mobileMenu" class="fixed left-0 top-0 z-50 h-full w-72 border-r border-white/10 bg-surface p-5 lg:hidden">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="font-semibold">Menu</h3>
                <button @click="mobileMenu = false" class="rounded-lg border border-white/10 px-3 py-1 text-muted">Fechar</button>
            </div>
            <nav class="space-y-2">
                <a href="{{ url('/dashboard') }}" class="block rounded-xl2 border border-white/10 bg-card px-4 py-3">Dashboard</a>
                <a href="{{ url('/contas') }}" class="block rounded-xl2 border border-white/10 px-4 py-3 text-muted">Contas</a>
                <a href="{{ url('/logout') }}" class="block rounded-xl2 border border-white/10 px-4 py-3 text-rose-200">Sair</a>
            </nav>
        </aside>

        <section class="mx-auto max-w-7xl px-4 py-6 lg:px-8">
            @yield('content')
        </section>

        <nav class="fixed inset-x-3 bottom-3 z-40 rounded-2xl border border-white/10 bg-surface/95 p-2 shadow-lg backdrop-blur lg:hidden">
            <ul class="grid grid-cols-5 gap-2 text-center text-xs">
                <li><a class="block rounded-xl2 px-2 py-2 text-muted hover:bg-card hover:text-text" href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a class="block rounded-xl2 px-2 py-2 text-muted hover:bg-card hover:text-text" href="{{ url('/contas') }}">Contas</a></li>
                <li><a class="block rounded-xl2 bg-primary px-2 py-2 font-semibold text-white" href="{{ url('/contas?open=create') }}">+</a></li>
                <li><a class="block rounded-xl2 px-2 py-2 text-muted hover:bg-card hover:text-text" href="{{ url('/contas?quick=atraso') }}">Alertas</a></li>
                <li><button @click="mobileMenu = true" class="w-full rounded-xl2 px-2 py-2 text-muted hover:bg-card hover:text-text">Menu</button></li>
            </ul>
        </nav>
    </main>
</div>

@stack('scripts')
</body>
</html>
