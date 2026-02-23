<?php

namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Invoice extends Model
{
    use HasFactory, HasUlid;

    protected $fillable = [
        'ulid',
        'subscribable_type',
        'subscribable_id',
        'subscription_id',
        'plan_id',
        'gateway',
        'gateway_invoice_id',
        'gateway_payment_id',
        'amount',
        'tax',
        'total',
        'currency',
        'status',
        'description',
        'paid_at',
        'due_at',
        'pdf_url',
        'metadata',
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'tax'      => 'decimal:2',
        'total'    => 'decimal:2',
        'paid_at'  => 'datetime',
        'due_at'   => 'datetime',
        'metadata' => 'array',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('subscribable_type', User::class)->where('subscribable_id', $userId);
    }

    public function scopeForTeam($query, int $teamId)
    {
        return $query->where('subscribable_type', Team::class)->where('subscribable_id', $teamId);
    }
}
