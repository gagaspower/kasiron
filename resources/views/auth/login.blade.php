@extends('layouts.guest')

@section('title', 'Masuk | KasirOn')

@section('content')
    <main class="grid min-h-screen lg:grid-cols-[minmax(0,5fr)_minmax(0,6fr)]">
        <aside class="relative hidden overflow-hidden bg-blue-600 p-12 text-white lg:flex lg:flex-col lg:justify-between xl:p-16">
            <div class="pointer-events-none absolute -right-24 -top-24 size-96 rounded-full bg-white/10" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-20 size-[28rem] rounded-full bg-blue-500/40" aria-hidden="true"></div>

            <div class="relative flex items-center gap-2.5">
                <span class="flex size-10 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 8.5h14l1 11H4l1-11Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M9 9V6a3 3 0 0 1 6 0v3M8 13h8M9 16.5h.01M15 16.5h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="text-xl font-bold tracking-tight">KasirOn</span>
            </div>

            <div class="relative max-w-md">
                <h2 class="text-3xl font-semibold leading-tight tracking-tight xl:text-4xl">Kelola toko lebih rapi, dari satu layar.</h2>
                <p class="mt-4 text-base leading-7 text-blue-50">Pantau penjualan, transaksi, dan stok produk secara langsung, di kasir maupun di ponsel.</p>

                <ul class="mt-8 space-y-4">
                    @foreach ([
                        'Ringkasan penjualan harian yang mudah dibaca',
                        'Riwayat transaksi dan metode pembayaran',
                        'Peringatan stok menipis sebelum kehabisan',
                    ] as $benefit)
                        <li class="flex items-start gap-3 text-sm leading-6 text-blue-50">
                            <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-white/20">
                                <svg class="size-3" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="m4.5 10.5 3.5 3.5 7.5-8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            {{ $benefit }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="relative text-xs text-blue-100">&copy; {{ date('Y') }} KasirOn. Sistem kasir untuk toko Anda.</p>
        </aside>

        <section class="flex items-center justify-center px-4 py-10 sm:px-8">
            <div class="w-full max-w-md">
                <a href="{{ route('login') }}" class="mb-8 flex items-center gap-2.5 lg:hidden" aria-label="KasirOn">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm shadow-blue-600/20">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 8.5h14l1 11H4l1-11Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M9 9V6a3 3 0 0 1 6 0v3M8 13h8M9 16.5h.01M15 16.5h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span class="text-lg font-bold tracking-tight text-slate-900">Kasir<span class="text-blue-600">On</span></span>
                </a>

                <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-xl shadow-slate-900/5 sm:p-8">
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Masuk ke akun Anda</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Gunakan email dan password yang terdaftar untuk melanjutkan.</p>

                    <form method="POST" action="{{ route('login.store') }}" data-login-form novalidate class="mt-7 space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                autocomplete="username"
                                inputmode="email"
                                autofocus
                                required
                                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                                placeholder="nama@toko.com"
                                class="{{ $errors->has('email') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/15' : 'border-slate-300 focus:border-blue-500 focus:ring-blue-500/15' }} mt-1.5 block min-h-11 w-full rounded-xl border bg-white px-3.5 text-base text-slate-900 placeholder:text-slate-400 transition focus:outline-none focus:ring-4 sm:text-sm"
                            >
                            @error('email')
                                <p id="email-error" role="alert" class="mt-1.5 flex items-center gap-1.5 text-sm text-rose-600">
                                    <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M12 8v5m0 3.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                            <div class="relative mt-1.5">
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    autocomplete="current-password"
                                    required
                                    @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                                    placeholder="Masukkan password"
                                    class="{{ $errors->has('password') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/15' : 'border-slate-300 focus:border-blue-500 focus:ring-blue-500/15' }} block min-h-11 w-full rounded-xl border bg-white py-0 pl-3.5 pr-12 text-base text-slate-900 placeholder:text-slate-400 transition focus:outline-none focus:ring-4 sm:text-sm"
                                >
                                <button
                                    type="button"
                                    data-password-toggle
                                    aria-controls="password"
                                    aria-pressed="false"
                                    aria-label="Tampilkan password"
                                    class="absolute inset-y-0 right-0 flex w-11 cursor-pointer items-center justify-center rounded-r-xl text-slate-500 transition hover:text-slate-800 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-500/20"
                                >
                                    <svg data-icon-show class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/>
                                    </svg>
                                    <svg data-icon-hide class="hidden size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M3 3l18 18M10.6 6a9.6 9.6 0 0 1 1.4-.1c6 0 9.5 6.1 9.5 6.1a16 16 0 0 1-2.9 3.6M6.5 7.5A16 16 0 0 0 2.5 12S6 18.5 12 18.5c1.5 0 2.8-.4 4-.9M9.9 9.9a3 3 0 0 0 4.2 4.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p id="password-error" role="alert" class="mt-1.5 flex items-center gap-1.5 text-sm text-rose-600">
                                    <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M12 8v5m0 3.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <label for="remember" class="flex min-h-11 cursor-pointer items-center gap-3 text-sm text-slate-600">
                            <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember')) class="size-4 cursor-pointer rounded border-slate-300 text-blue-600 focus:ring-4 focus:ring-blue-500/15">
                            Ingat saya di perangkat ini
                        </label>

                        <button
                            type="submit"
                            data-login-submit
                            class="inline-flex min-h-12 w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-70"
                        >
                            <svg data-login-spinner class="hidden size-4 animate-spin motion-reduce:animate-none" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/>
                                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            <span data-login-label>Masuk</span>
                        </button>
                    </form>
                </div>

                <p class="mt-6 text-center text-xs text-slate-500">&copy; {{ date('Y') }} KasirOn</p>
            </div>
        </section>
    </main>
@endsection
