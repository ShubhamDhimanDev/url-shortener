@extends('layouts.super-admin')
@section('title', 'Edit ' . $team->name)
@section('page-title', 'Edit Team: ' . $team->name)

@section('header-actions')
    <a href="{{ route('super-admin.teams.show', $team) }}"
       class="inline-flex items-center gap-2 px-3 py-2 bg-white/5 hover:bg-white/10 text-sm rounded-lg transition-colors">← Back</a>
@endsection

@section('content')
<div class="max-w-lg">
    <div class="glass-card rounded-xl p-6">
        <form method="POST" action="{{ route('super-admin.teams.update', $team) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs text-slate-400 mb-1.5 font-medium">Team Name <span class="text-red-400">*</span></label>
                <input type="text" name="name" value="{{ old('name', $team->name) }}" required
                       class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary @error('name') border-red-500 @enderror" />
                @error('name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1.5 font-medium">Description</label>
                <textarea name="description" rows="3"
                          class="w-full bg-black/30 border border-border-dark rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary">{{ old('description', $team->description) }}</textarea>
            </div>

            <div class="flex items-center gap-3">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       class="rounded border-border-dark bg-black/30 text-primary focus:ring-primary"
                       @checked(old('is_active', $team->is_active)) />
                <label for="is_active" class="text-sm text-slate-300">Active</label>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-border-dark">
                <button type="submit"
                        class="px-6 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
