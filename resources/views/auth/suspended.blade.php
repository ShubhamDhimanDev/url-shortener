@extends('layouts.auth')

@section('title', 'Account Suspended')

@section('content')
    {{-- Icon --}}
    <div class="flex justify-center mb-6">
        <div class="size-16 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center">
            <span class="material-symbols-outlined text-red-400 !text-3xl">block</span>
        </div>
    </div>

    <h1 class="text-2xl font-semibold mb-2 text-center">Account suspended</h1>
    <p class="text-slate-500 text-sm text-center mb-7">
        Your account has been suspended. Please contact support if you believe this is a mistake.
    </p>

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
                class="w-full rounded-lg border border-border-dark px-4 py-2.5 text-sm font-medium text-slate-400 hover:border-slate-600 hover:text-slate-300 transition-colors">
            Sign out
        </button>
    </form>
@endsection
