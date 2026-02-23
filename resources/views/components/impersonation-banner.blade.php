@if (session()->has('impersonator_id'))
<div class="w-full bg-amber-500 text-black px-6 py-2 flex items-center justify-between text-sm font-medium z-50">
    <div class="flex items-center gap-2">
        <span class="material-symbols-outlined !text-lg">visibility</span>
        <span>
            You are impersonating
            <strong>{{ auth()->user()->name }}</strong>
            ({{ auth()->user()->email }}).
        </span>
    </div>
    <form method="POST" action="{{ route('super-admin.impersonate.stop') }}">
        @csrf
        @method('DELETE')
        <button type="submit"
                class="px-3 py-1 rounded bg-black/10 hover:bg-black/20 transition-colors text-xs font-semibold">
            Stop Impersonating
        </button>
    </form>
</div>
@endif
