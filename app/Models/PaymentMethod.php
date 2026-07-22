<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscribable_type',
        'subscribable_id',
        'gateway',
        'gateway_token',
        'type',
        'last_four',
        'brand',
        'exp_month',
        'exp_year',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'exp_month'  => 'integer',
        'exp_year'   => 'integer',
    ];

    protected $hidden = [
        'gateway_token',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isCard(): bool
    {
        return $this->type === 'card';
    }

    public function maskedNumber(): string
    {
        return $this->last_four ? "**** **** **** {$this->last_four}" : 'N/A';
    }
}
