@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
    {{-- Icon --}}
    <div class="flex justify-center mb-6">
        <div class="size-16 rounded-full bg-primary/10 border border-primary/20 flex items-center justify-center">
            <span class="material-symbols-outlined text-primary !text-3xl">mark_email_unread</span>
        </div>
    </div>

    <h1 class="text-2xl font-semibold mb-2 text-center">Verify your email</h1>
    <p class="text-slate-500 text-sm text-center mb-7">
        We sent a verification link to <strong class="text-slate-300">{{ auth()->user()->email }}</strong>.
        Click the link in the email to activate your account.
    </p>

    {{-- Resend form --}}
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit"
                class="btn-primary w-full rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 mb-4">
            Resend verification email
        </button>
    </form>

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
                class="w-full rounded-lg border border-border-dark px-4 py-2.5 text-sm font-medium text-slate-400 hover:border-slate-600 hover:text-slate-300 transition-colors">
            Sign out
        </button>
    </form>
@endsection
