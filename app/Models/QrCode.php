<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class QrCode extends Model
{
    use HasFactory;

    protected $table = 'qrcodes';

    protected $fillable = [
        'link_id',
        'foreground_color',
        'background_color',
        'logo_url',
        'size',
        'format',
        'file_path',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }

    // ─── Accessors ───────────────────────────────────────────────────────────

    public function getDownloadUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return Storage::url($this->file_path);
    }
}
