<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class QrCodeController extends Controller
{
    public function __construct(private readonly QrCodeService $qrCode) {}

    // ─── Generate / customise ─────────────────────────────────────────────────

    public function generate(Request $request, string $ulid): RedirectResponse
    {
        $user = $request->user();
        $link = $this->findLink($ulid, $user);
        $this->authorize('update', $link);

        $request->validate([
            'foreground_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'background_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'size'             => ['nullable', 'integer', 'min:100', 'max:1000'],
            'format'           => ['nullable', 'in:png,svg'],
            'logo_url'         => ['nullable', 'url'],
        ]);

        $this->qrCode->generate($link, $request->only([
            'foreground_color', 'background_color', 'size', 'format', 'logo_url',
        ]));

        return back()->with('success', 'QR code generated!');
    }

    // ─── Download ─────────────────────────────────────────────────────────────

    public function download(Request $request, string $ulid): Response
    {
        $user = $request->user();
        $link = $this->findLink($ulid, $user);
        $this->authorize('view', $link);

        $qrCode = $link->qrCode;
        if (! $qrCode || ! Storage::disk('local')->exists($qrCode->file_path)) {
            abort(404, 'QR code not found. Please generate one first.');
        }

        $content     = Storage::disk('local')->get($qrCode->file_path);
        $mimeTypes   = ['png' => 'image/png', 'svg' => 'image/svg+xml', 'pdf' => 'application/pdf'];
        $contentType = $mimeTypes[$qrCode->format] ?? 'application/octet-stream';

        return response($content, 200, [
            'Content-Type'        => $contentType,
            'Content-Disposition' => "attachment; filename=\"qr-{$link->short_code}.{$qrCode->format}\"",
        ]);
    }

    // ─── Show customise form ──────────────────────────────────────────────────

    public function show(Request $request, string $ulid): View
    {
        $user = $request->user();
        $link = $this->findLink($ulid, $user);
        $this->authorize('view', $link);

        $qrCode = $link->qrCode;

        return view('app.qrcodes.show', compact('link', 'qrCode'));
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function findLink(string $ulid, $user): Link
    {
        return Link::where('ulid', $ulid)
            ->where(fn ($q) => $q
                ->where('user_id', $user->id)
                ->orWhereIn('team_id', $user->teamMemberships()->pluck('team_id'))
            )
            ->firstOrFail();
    }
}
