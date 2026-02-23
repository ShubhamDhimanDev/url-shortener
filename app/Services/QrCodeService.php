<?php

namespace App\Services;

use App\Models\Link;
use App\Models\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;

class QrCodeService
{
    private const STORAGE_DIR = 'qrcodes';

    /**
     * Generate (or regenerate) a QR code for a link and persist metadata.
     *
     * @param  array{
     *     foreground_color?: string,
     *     background_color?: string,
     *     logo_url?: string|null,
     *     size?: int,
     *     format?: 'png'|'svg',
     * } $options
     */
    public function generate(Link $link, array $options = []): QrCode
    {
        $foreground = $options['foreground_color'] ?? '#000000';
        $background = $options['background_color'] ?? '#ffffff';
        $size       = (int) ($options['size']      ?? 300);
        $format     = $options['format']            ?? 'png';
        $logoUrl    = $options['logo_url']          ?? null;

        $shortUrl = $link->short_url;

        // ── Build QR generator ──────────────────────────────────────────────
        $generator = QrCodeGenerator::format($format)
            ->size($size)
            ->color(...$this->hexToRgb($foreground))
            ->backgroundColor(...$this->hexToRgb($background))
            ->margin(1)
            ->errorCorrection('H');

        if ($logoUrl) {
            $generator = $generator->merge($logoUrl, .3, true);
        }

        $imageData = $generator->generate($shortUrl);

        // ── Persist file ───────────────────────────────────────────────────
        $filename  = "link-{$link->ulid}.{$format}";
        $path      = self::STORAGE_DIR . '/' . $filename;

        Storage::disk('local')->put($path, $imageData);

        // ── Upsert DB record ───────────────────────────────────────────────
        $qrCode = QrCode::updateOrCreate(
            ['link_id' => $link->id],
            [
                'foreground_color' => $foreground,
                'background_color' => $background,
                'logo_url'         => $logoUrl,
                'size'             => $size,
                'format'           => $format,
                'file_path'        => $path,
            ]
        );

        return $qrCode;
    }

    /**
     * Return a time-limited signed URL for downloading the QR code file.
     */
    public function signedDownloadUrl(QrCode $qrCode, int $expiresInMinutes = 60): string
    {
        return URL::temporarySignedRoute(
            'qrcode.download',
            now()->addMinutes($expiresInMinutes),
            ['qrcode' => $qrCode->id]
        );
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** Convert #RRGGBB to [r, g, b] */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
