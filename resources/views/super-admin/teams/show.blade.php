@extends('layouts.super-admin')
@section('title', $team->name)
@section('page-title', 'Team: ' . $team->name)

@section('header-actions')
    <a href="{{ route('super-admin.teams.edit', $team) }}"
       class="inline-flex items-center gap-2 px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
        <span class="material-symbols-outlined !text-sm">edit</span> Edit
    </a>
    <a href="{{ route('super-admin.teams.index') }}"
       class="inline-flex items-center gap-2 px-3 py-2 bg-white/5 hover:bg-white/10 text-sm rounded-lg transition-colors">← Back</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Info column --}}
    <div class="space-y-4">
        <div class="glass-card rounded-xl p-5">
            <h3 class="font-semibold text-lg mb-4">{{ $team->name }}</h3>
            <dl class="space-y-2 text-sm divide-y divide-border-dark">
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Slug</dt>
                    <dd class="font-mono text-xs">{{ $team->slug }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Owner</dt>
                    <dd>
                        <a href="{{ route('super-admin.users.show', $team->owner) }}"
                           class="text-primary hover:underline">{{ $team->owner?->name ?? '—' }}</a>
                    </dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Status</dt>
                    <dd>{{ $team->is_active ? '✅ Active' : '⛔ Inactive' }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Total Links</dt>
                    <dd>{{ number_format($linkCount) }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Total Clicks</dt>
                    <dd>{{ number_format($clicksTotal) }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Created</dt>
                    <dd>{{ $team->created_at->format('d M Y') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Subscription --}}
        <div class="glass-card rounded-xl p-5">
            <h4 class="text-sm font-semibold mb-3">Subscription</h4>
            @if ($team->subscription)
                @php $sub = $team->subscription; @endphp
                <dl class="space-y-2 text-sm divide-y divide-border-dark">
                    <div class="flex justify-between py-2">
                        <dt class="text-slate-400">Plan</dt>
                        <dd><x-plan-badge :plan="$sub->plan" :status="$sub->status" /></dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-slate-400">Period ends</dt>
                        <dd>{{ $sub->current_period_end?->format('d M Y') ?? '—' }}</dd>
                    </div>
                </dl>
                <a href="{{ route('super-admin.subscriptions.show', $sub) }}"
                   class="mt-2 inline-block text-xs text-primary hover:underline">View subscription →</a>
            @else
                <p class="text-sm text-slate-400">No active subscription.</p>
            @endif
        </div>
    </div>

    {{-- Members & Links --}}
    <div class="lg:col-span-2 space-y-4">
        {{-- Members --}}
        <div class="glass-card rounded-xl p-5">
            <h4 class="text-sm font-semibold mb-3">Members ({{ $team->members->count() }})</h4>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-slate-400 uppercase tracking-wider border-b border-border-dark">
                        <th class="text-left py-2 pr-4">Name</th>
                        <th class="text-left py-2 pr-4">Email</th>
                        <th class="text-left py-2 pr-4">Role</th>
                        <th class="text-left py-2">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark">
                    @forelse ($team->members as $member)
                        <tr>
                            <td class="py-2 pr-4">
                                <a href="{{ route('super-admin.users.show', $member->user) }}"
                                   class="hover:text-primary">{{ $member->user?->name }}</a>
                            </td>
                            <td class="py-2 pr-4 text-slate-400">{{ $member->user?->email }}</td>
                            <td class="py-2 pr-4">
                                <span class="px-2 py-0.5 rounded-full text-xs bg-primary/10 text-primary capitalize">
                                    {{ $member->role }}
                                </span>
                            </td>
                            <td class="py-2 text-slate-400">{{ $member->joined_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-center text-slate-400">No members.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Recent Links --}}
        <div class="glass-card rounded-xl p-5">
            <h4 class="text-sm font-semibold mb-3">Recent Links</h4>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-slate-400 uppercase tracking-wider border-b border-border-dark">
                        <th class="text-left py-2 pr-4">Code</th>
                        <th class="text-left py-2 pr-4">Destination</th>
                        <th class="text-left py-2">Clicks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark">
                    @forelse ($team->links as $link)
                        <tr>
                            <td class="py-2 pr-4 font-mono text-xs text-primary">{{ $link->short_code }}</td>
                            <td class="py-2 pr-4 text-slate-400 text-xs truncate max-w-64">{{ $link->destination_url }}</td>
                            <td class="py-2">{{ number_format($link->clicks_count) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-4 text-center text-slate-400">No links yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
