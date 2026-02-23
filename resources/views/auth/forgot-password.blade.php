@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
    <h1 class="text-2xl font-semibold mb-1">Reset your password</h1>
    <p class="text-slate-500 text-sm mb-7">
        Enter the email address linked to your account and we'll send you a password reset link.
    </p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        {{-- Email --}}
        <div class="mb-6">
            <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Email address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                class="input-dark w-full rounded-lg px-4 py-2.5 text-sm border"
                placeholder="you@example.com"
            />
            @error('email')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="btn-primary w-full rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20">
            Send reset link
        </button>
    </form>
@endsection

@section('footer')
    <a href="{{ route('login') }}" class="text-primary hover:underline flex items-center justify-center gap-1">
        <span class="material-symbols-outlined !text-sm">arrow_back</span>
        Back to sign in
    </a>
@endsection
