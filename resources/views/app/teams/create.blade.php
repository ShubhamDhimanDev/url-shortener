@extends('layouts.app')
@section('title', 'Create Team')
@section('page-title', 'Create Team')

@section('content')
<div class="max-w-xl">
    <form method="POST" action="{{ route('app.teams.store') }}" class="glass-card rounded-xl p-6 space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">
                Team Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" id="name"
                   value="{{ old('name') }}"
                   placeholder="Acme Marketing"
                   class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border @error('name') border-red-400 @else border-slate-200 dark:border-border-dark @enderror rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition" />
            @error('name')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Description</label>
            <textarea name="description" id="description" rows="3"
                      placeholder="What is this team for?"
                      class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition resize-none">{{ old('description') }}</textarea>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('app.teams.index') }}" class="text-sm text-slate-500 hover:text-slate-700 transition">Cancel</a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors">
                <span class="material-symbols-outlined !text-base">group_add</span>
                Create Team
            </button>
        </div>
    </form>
</div>
@endsection
