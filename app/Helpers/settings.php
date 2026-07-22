<?php

use Illuminate\Support\Facades\Cache;

if (! function_exists('setting')) {
    /**
     * Retrieve a platform setting by its dot-notation key or "group.key" pair.
     *
     * Values are cached forever under the key "setting:{$key}".
     * To bust the cache after saving a new value use:
     *
     *   Cache::forget("setting:{$key}");
     *
     * Examples:
     *   setting('general.site_name')          // group=general, key=site_name
     *   setting('spam.blocklist', [])          // returns [] when not found
     *   setting('mail.from_address', 'noreply@example.com')
     *
     * @param  string  $key      Dot-notation key matching the settings table (group.key)
     * @param  mixed   $default  Returned when no row is found
     * @return mixed
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
            // Support both "group.key" and plain "key" formats.
            if (str_contains($key, '.')) {
                [$group, $settingKey] = explode('.', $key, 2);

                $record = \App\Models\Setting::query()
                    ->where('group', $group)
                    ->where('key', $settingKey)
                    ->first();
            } else {
                $record = \App\Models\Setting::query()
                    ->where('key', $key)
                    ->first();
            }

            if ($record === null) {
                return $default;
            }

            $value = $record->value;

            // Auto-cast the raw string value when a cast type is stored.
            return match ($record->cast ?? 'string') {
                'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                'integer', 'int'  => (int) $value,
                'float'           => (float) $value,
                'array', 'json'   => json_decode($value, true) ?? $default,
                default           => $value,
            };
        });
    }
}

if (! function_exists('setting_forget')) {
    /**
     * Bust the cached value for a given setting key.
     *
     * Call this whenever a setting is saved so the next read fetches fresh data.
     *
     * @param  string  $key  Same dot-notation key used with setting()
     */
    function setting_forget(string $key): void
    {
        Cache::forget("setting:{$key}");
    }
}
