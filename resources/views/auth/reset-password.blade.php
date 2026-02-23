@extends('layouts.auth')

@section('title', 'Set New Password')

@section('content')
    <h1 class="text-2xl font-semibold mb-1">Set new password</h1>
    <p class="text-slate-500 text-sm mb-7">Choose a strong password for your account.</p>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        {{-- Token (hidden) --}}
        <input type="hidden" name="token" value="{{ $token }}" />

        {{-- Email (hidden / pre-filled) --}}
        <div class="mb-5">
            <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Email address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ $email ?? old('email') }}"
                required
                autocomplete="email"
                class="input-dark w-full rounded-lg px-4 py-2.5 text-sm border"
                placeholder="you@example.com"
            />
            @error('email')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- New password --}}
        <div class="mb-5">
            <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">New password</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autofocus
                autocomplete="new-password"
                class="input-dark w-full rounded-lg px-4 py-2.5 text-sm border"
                placeholder="Min. 8 characters"
            />
            @error('password')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirm new password --}}
        <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-1.5">Confirm new password</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="input-dark w-full rounded-lg px-4 py-2.5 text-sm border"
                placeholder="••••••••"
            />
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="btn-primary w-full rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20">
            Reset password
        </button>
    </form>
@endsection

@section('footer')
    <a href="{{ route('login') }}" class="text-primary hover:underline flex items-center justify-center gap-1">
        <span class="material-symbols-outlined !text-sm">arrow_back</span>
        Back to sign in
    </a>
@endsection
