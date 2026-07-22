@extends('layouts.app')
@section('title', 'QR Code — ' . $link->title)
@section('page-title', 'QR Code')

@section('content')
<div class="max-w-2xl space-y-6">

    {{-- ── Preview ───────────────────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-6 flex flex-col items-center gap-4">
        @if($qrCode)
            <div class="p-3 bg-white rounded-xl shadow-sm">
                <img src="{{ Storage::url($qrCode->path) }}"
                     alt="QR Code for {{ $link->title }}"
                     class="size-48 object-contain" />
            </div>
            <p class="text-xs text-slate-400">
                Last generated {{ $qrCode->updated_at->diffForHumans() }}
            </p>
            <a href="{{ route('app.qrcodes.download', $link->ulid) }}"
               class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors">
                <span class="material-symbols-outlined !text-base">download</span>
                Download QR Code
            </a>
        @else
            <div class="size-48 rounded-xl bg-slate-100 dark:bg-slate-800 flex flex-col items-center justify-center gap-2 text-slate-400">
                <span class="material-symbols-outlined !text-4xl">qr_code_2</span>
                <p class="text-xs">Not generated yet</p>
            </div>
        @endif
    </div>

    {{-- ── Customise ─────────────────────────────────────────────────────── --}}
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-5">
            <span class="material-symbols-outlined !text-base align-middle mr-1 text-primary">tune</span>
            Customise
        </h3>

        <form method="POST" action="{{ route('app.qrcodes.generate', $link->ulid) }}" class="space-y-4">
            @csrf

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Foreground Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="foreground_color"
                               value="{{ old('foreground_color', $qrCode?->settings['foreground_color'] ?? '#000000') }}"
                               class="size-9 rounded cursor-pointer border border-slate-200 dark:border-border-dark" />
                        <input type="text" id="fg-text"
                               value="{{ old('foreground_color', $qrCode?->settings['foreground_color'] ?? '#000000') }}"
                               class="flex-1 px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none"
                               readonly />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Background Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="background_color"
                               value="{{ old('background_color', $qrCode?->settings['background_color'] ?? '#ffffff') }}"
                               class="size-9 rounded cursor-pointer border border-slate-200 dark:border-border-dark" />
                        <input type="text" id="bg-text"
                               value="{{ old('background_color', $qrCode?->settings['background_color'] ?? '#ffffff') }}"
                               class="flex-1 px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none"
                               readonly />
                    </div>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Size (px)</label>
                    <select name="size"
                            class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition">
                        @foreach([200, 300, 400, 500, 600] as $sz)
                            <option value="{{ $sz }}" @selected(old('size', $qrCode?->settings['size'] ?? 300) == $sz)>{{ $sz }}×{{ $sz }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Format</label>
                    <div class="flex gap-3 pt-2.5">
                        @foreach(['png', 'svg'] as $fmt)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="format" value="{{ $fmt }}"
                                       @checked(old('format', $qrCode?->settings['format'] ?? 'png') === $fmt)
                                       class="accent-primary" />
                                <span class="text-sm text-slate-600 dark:text-slate-300 uppercase">{{ $fmt }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Logo URL (optional)</label>
                <input type="url" name="logo_url"
                       value="{{ old('logo_url', $qrCode?->settings['logo_url'] ?? '') }}"
                       placeholder="https://example.com/logo.png"
                       class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition" />
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors">
                    <span class="material-symbols-outlined !text-base">qr_code_2</span>
                    Generate QR Code
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('input[type="color"]').forEach(picker => {
    const textInput = picker.parentElement.querySelector('input[type="text"]');
    picker.addEventListener('input', () => { textInput.value = picker.value; });
});
</script>
@endpush
