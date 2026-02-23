<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'value',
        'cast',
    ];

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
            $setting = static::query()->where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return $setting->getCastedValue();
        });
    }

    public static function set(string $group, string $key, mixed $value, string $cast = 'string'): static
    {
        $setting = static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => (string) $value, 'cast' => $cast]
        );

        Cache::forget("setting:{$key}");

        return $setting;
    }

    public function getCastedValue(): mixed
    {
        return match ($this->cast) {
            'boolean', 'bool' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int'  => (int) $this->value,
            'float', 'double' => (float) $this->value,
            'array', 'json'   => json_decode($this->value, true),
            default           => $this->value,
        };
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
