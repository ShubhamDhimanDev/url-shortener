@extends('layouts.app')
@section('title', 'Profile')
@section('page-title', 'Profile')

@section('content')
<div class="max-w-2xl space-y-6">

    {{-- ── Profile details ──────────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-5">Account Details</h3>

        <form method="POST" action="{{ route('app.profile.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf @method('PUT')

            {{-- Avatar --}}
            <div class="flex items-center gap-4">
                @if($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}"
                         class="size-14 rounded-full object-cover ring-2 ring-primary/20" />
                @else
                    <div class="size-14 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xl">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Avatar</span>
                    <input type="file" name="avatar" accept="image/*"
                           class="text-xs text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition" />
                </label>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition" />
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition" />
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Timezone</label>
                    <select name="timezone"
                            class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition">
                        @foreach(\DateTimeZone::listIdentifiers() as $tz)
                            <option value="{{ $tz }}" @selected(old('timezone', $user->timezone) === $tz)>{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Locale</label>
                    <select name="locale"
                            class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition">
                        <option value="en" @selected(old('locale', $user->locale) === 'en')>English</option>
                        <option value="hi" @selected(old('locale', $user->locale) === 'hi')>Hindi</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors">
                    <span class="material-symbols-outlined !text-base">save</span>
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- ── Change password ──────────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-5">Change Password</h3>

        <form method="POST" action="{{ route('app.profile.password') }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Current Password</label>
                <input type="password" name="current_password" autocomplete="current-password"
                       class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition" />
                @error('current_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">New Password</label>
                    <input type="password" name="password" autocomplete="new-password"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition" />
                    @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Confirm Password</label>
                    <input type="password" name="password_confirmation" autocomplete="new-password"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition" />
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold rounded-lg transition-colors">
                    <span class="material-symbols-outlined !text-base">lock_reset</span>
                    Update Password
                </button>
            </div>
        </form>
    </div>

    {{-- ── Danger zone ──────────────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-6 border border-red-200 dark:border-red-900/50">
        <h3 class="text-sm font-semibold text-red-600 mb-2">Danger Zone</h3>
        <p class="text-xs text-slate-400 mb-4">
            Deleting your account is permanent. All your links, clicks, and billing data will be removed.
        </p>
        <button type="button"
                onclick="document.getElementById('delete-account-modal').classList.remove('hidden')"
                class="inline-flex items-center gap-1.5 px-4 py-2 border border-red-400 text-red-500 text-sm font-medium rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition">
            <span class="material-symbols-outlined !text-sm">no_accounts</span>
            Delete Account
        </button>
    </div>

</div>

{{-- ── Delete account modal ─────────────────────────────────────────────── --}}
<div id="delete-account-modal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="w-full max-w-sm glass-card rounded-2xl p-6 mx-4">
        <h4 class="text-base font-semibold text-slate-800 dark:text-white mb-1">Delete Account?</h4>
        <p class="text-sm text-slate-400 mb-5">Enter your password to confirm permanent account deletion.</p>
        <form method="POST" action="{{ route('app.profile.destroy') }}">
            @csrf @method('DELETE')
            <input type="password" name="password" placeholder="Your password" autocomplete="current-password"
                   class="w-full px-4 py-2.5 text-sm mb-4 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-red-400 transition" />
            @error('password', 'deleteAccount')
                <p class="text-xs text-red-500 mb-3">{{ $message }}</p>
            @enderror
            <div class="flex gap-2">
                <button type="button"
                        onclick="document.getElementById('delete-account-modal').classList.add('hidden')"
                        class="flex-1 py-2 text-sm border border-slate-200 dark:border-border-dark rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 py-2 text-sm font-semibold bg-red-500 hover:bg-red-600 text-white rounded-lg transition">
                    Delete
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
