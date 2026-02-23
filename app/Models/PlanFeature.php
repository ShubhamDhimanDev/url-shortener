<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'feature_key',
        'feature_value',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function getBoolValue(): bool
    {
        return filter_var($this->feature_value, FILTER_VALIDATE_BOOLEAN);
    }

    public function getIntValue(): int
    {
        return (int) $this->feature_value;
    }
}
