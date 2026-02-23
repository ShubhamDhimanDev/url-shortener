<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /** Ordered list of setting groups and their labels. */
    private const GROUPS = [
        'general'  => 'General',
        'mail'     => 'Mail / SMTP',
        'payment'  => 'Payment',
        'features' => 'Platform Features',
        'limits'   => 'Global Limits',
        'spam'     => 'Spam & Security',
    ];

    public function index(): View
    {
        $settings = Setting::all()
            ->groupBy('group')
            ->sortBy(fn ($_, $group) => array_search($group, array_keys(self::GROUPS)));

        $groups = self::GROUPS;

        return view('super-admin.settings.index', compact('settings', 'groups'));
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        foreach ($request->validated()['settings'] as $group => $keys) {
            foreach ($keys as $key => $value) {
                Setting::updateOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['value' => $value ?? '']
                );

                // Bust the individual setting cache.
                Cache::forget("setting:{$group}.{$key}");
            }
        }

        return back()->with('success', 'Settings saved successfully.');
    }
}
