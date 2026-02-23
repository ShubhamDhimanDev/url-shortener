@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
    <h1 class="text-2xl font-semibold mb-1">Welcome back</h1>
    <p class="text-slate-500 text-sm mb-7">Sign in to your {{ config('app.name') }} account</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Email --}}
        <div class="mb-5">
            <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Email address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                autofocus
                class="input-dark w-full rounded-lg px-4 py-2.5 text-sm border"
                placeholder="you@example.com"
            />
            @error('email')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div class="mb-5">
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
                <a href="{{ route('password.request') }}" class="text-xs text-primary hover:underline">
                    Forgot password?
                </a>
            </div>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="input-dark w-full rounded-lg px-4 py-2.5 text-sm border"
                placeholder="••••••••"
            />
            @error('password')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember me --}}
        <div class="mb-6 flex items-center gap-2">
            <input
                id="remember"
                type="checkbox"
                name="remember"
                class="rounded border-border-dark bg-card-dark text-primary focus:ring-primary/40"
            />
            <label for="remember" class="text-sm text-slate-400">Keep me signed in</label>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="btn-primary w-full rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20">
            Sign in
        </button>
    </form>
@endsection

@section('footer')
    Don't have an account?
    <a href="{{ route('register') }}" class="text-primary hover:underline ml-1">Create one free</a>
@endsection
