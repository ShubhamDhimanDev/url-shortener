@extends('layouts.auth')

@section('title', 'Create Account')

@section('content')
    <h1 class="text-2xl font-semibold mb-1">Create your account</h1>
    <p class="text-slate-500 text-sm mb-7">Start shortening links in seconds. Free to get started.</p>

    <form method="POST" action="{{ route('register') }}" x-data="registerForm()">
        @csrf

        {{-- Account type toggle --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-300 mb-2">Account type</label>
            <div class="grid grid-cols-2 gap-2">
                <button type="button"
                        @click="accountType = 'individual'"
                        :class="accountType === 'individual'
                            ? 'border-primary bg-primary/10 text-primary'
                            : 'border-border-dark text-slate-400 hover:border-slate-600'"
                        class="rounded-lg border px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined !text-base">person</span>
                    Individual
                </button>
                <button type="button"
                        @click="accountType = 'team'"
                        :class="accountType === 'team'
                            ? 'border-primary bg-primary/10 text-primary'
                            : 'border-border-dark text-slate-400 hover:border-slate-600'"
                        class="rounded-lg border px-4 py-2.5 text-sm font-medium transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined !text-base">group</span>
                    Team
                </button>
            </div>
            <input type="hidden" name="account_type" :value="accountType" />
            @error('account_type')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Team name (conditional) --}}
        <div class="mb-5" x-show="accountType === 'team'" x-cloak>
            <label for="team_name" class="block text-sm font-medium text-slate-300 mb-1.5">Team name</label>
            <input
                id="team_name"
                type="text"
                name="team_name"
                value="{{ old('team_name') }}"
                :required="accountType === 'team'"
                maxlength="100"
                class="input-dark w-full rounded-lg px-4 py-2.5 text-sm border"
                placeholder="Acme Inc."
            />
            @error('team_name')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Name --}}
        <div class="mb-5">
            <label for="name" class="block text-sm font-medium text-slate-300 mb-1.5">Full name</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                maxlength="100"
                autofocus
                class="input-dark w-full rounded-lg px-4 py-2.5 text-sm border"
                placeholder="Jane Smith"
            />
            @error('name')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

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
                class="input-dark w-full rounded-lg px-4 py-2.5 text-sm border"
                placeholder="you@example.com"
            />
            @error('email')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div class="mb-5">
            <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                class="input-dark w-full rounded-lg px-4 py-2.5 text-sm border"
                placeholder="Min. 8 characters"
            />
            @error('password')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirm password --}}
        <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-1.5">Confirm password</label>
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
            Create account
        </button>

        <p class="mt-4 text-center text-xs text-slate-600">
            By creating an account you agree to our
            <a href="#" class="text-slate-400 hover:underline">Terms of Service</a> and
            <a href="#" class="text-slate-400 hover:underline">Privacy Policy</a>.
        </p>
    </form>
@endsection

@section('footer')
    Already have an account?
    <a href="{{ route('login') }}" class="text-primary hover:underline ml-1">Sign in</a>
@endsection

@push('scripts')
    <script>
        function registerForm() {
            return {
                accountType: '{{ old('account_type', 'individual') }}',
            }
        }
    </script>
@endpush
