@extends('layouts.super-admin')
@section('title', 'New User')
@section('page-title', 'Create User')

@section('header-actions')
    <a href="{{ route('super-admin.users.index') }}"
       class="inline-flex items-center gap-2 px-3 py-2 bg-white/5 hover:bg-white/10 text-sm rounded-lg transition-colors">
        ← Back to Users
    </a>
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="glass-card rounded-xl p-6">
        <form method="POST" action="{{ route('super-admin.users.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5 font-medium">Full Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary @error('name') border-red-500 @enderror" />
                    @error('name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5 font-medium">Email Address <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary @error('email') border-red-500 @enderror" />
                    @error('email')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5 font-medium">Password <span class="text-red-400">*</span></label>
                    <input type="password" name="password" required
                           class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary @error('password') border-red-500 @enderror" />
                    @error('password')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5 font-medium">Confirm Password <span class="text-red-400">*</span></label>
                    <input type="password" name="password_confirmation" required
                           class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5 font-medium">Role</label>
                    <select name="role" class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary">
                        <option value="">No role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" @selected(old('role') === $role->name)>
                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-3 pt-5">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           class="rounded border-border-dark bg-black/30 text-primary focus:ring-primary"
                           @checked(old('is_active', true)) />
                    <label for="is_active" class="text-sm text-slate-300">Active (can log in)</label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-border-dark">
                <button type="submit"
                        class="px-6 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
                    Create User
                </button>
                <a href="{{ route('super-admin.users.index') }}"
                   class="px-4 py-2 text-sm text-slate-400 hover:text-white transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
