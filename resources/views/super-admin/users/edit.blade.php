@extends('layouts.super-admin')
@section('title', 'Edit ' . $user->name)
@section('page-title', 'Edit User: ' . $user->name)

@section('header-actions')
    <a href="{{ route('super-admin.users.show', $user) }}"
       class="inline-flex items-center gap-2 px-3 py-2 bg-white/5 hover:bg-white/10 text-sm rounded-lg transition-colors">
        ← Back to User
    </a>
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="glass-card rounded-xl p-6">
        <form method="POST" action="{{ route('super-admin.users.update', $user) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5 font-medium">Full Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary @error('name') border-red-500 @enderror" />
                    @error('name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5 font-medium">Email Address <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary @error('email') border-red-500 @enderror" />
                    @error('email')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5 font-medium">
                        New Password <span class="text-slate-500">(leave blank to keep)</span>
                    </label>
                    <input type="password" name="password"
                           class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary @error('password') border-red-500 @enderror" />
                    @error('password')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5 font-medium">Confirm New Password</label>
                    <input type="password" name="password_confirmation"
                           class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5 font-medium">Role</label>
                    <select name="role" class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary">
                        <option value="">No role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}"
                                    @selected(old('role', $user->roles->first()?->name) === $role->name)>
                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5 font-medium">Timezone</label>
                    <input type="text" name="timezone" value="{{ old('timezone', $user->timezone) }}"
                           placeholder="e.g. Asia/Kolkata"
                           class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary" />
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5 font-medium">Locale</label>
                    <input type="text" name="locale" value="{{ old('locale', $user->locale) }}"
                           placeholder="e.g. en"
                           class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary" />
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       class="rounded border-border-dark bg-black/30 text-primary focus:ring-primary"
                       @checked(old('is_active', $user->is_active)) />
                <label for="is_active" class="text-sm text-slate-300">Active</label>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-border-dark">
                <button type="submit"
                        class="px-6 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
                    Save Changes
                </button>
                <a href="{{ route('super-admin.users.show', $user) }}"
                   class="px-4 py-2 text-sm text-slate-400 hover:text-white transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
