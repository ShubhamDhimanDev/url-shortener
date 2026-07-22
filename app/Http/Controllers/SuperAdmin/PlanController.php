<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StorePlanRequest;
use App\Http\Requests\SuperAdmin\UpdatePlanRequest;
use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlanController extends Controller
{
    /** Feature keys available for plan features. */
    public const FEATURE_KEYS = [
        'links_per_month',
        'custom_domains_count',
        'domain_whitelist',
        'custom_subdomain',
        'meta_tracking',
        'google_tracking',
        'analytics_level',
        'qrcode',
        'password_protected_links',
        'geo_metrics',
        'bot_detection',
        'spam_detection',
        'team_members_count',
        'link_expiry',
        'bulk_links',
        'campaign_tracking',
    ];

    public function index(): View
    {
        $plans = Plan::withTrashed()
            ->with('features')
            ->withCount('subscriptions')
            ->orderBy('sort_order')
            ->get();

        return view('super-admin.plans.index', compact('plans'));
    }

    public function create(): View
    {
        $featureKeys = self::FEATURE_KEYS;

        return view('super-admin.plans.create', compact('featureKeys'));
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        $plan = Plan::create([
            'name'          => $request->name,
            'slug'          => Str::slug($request->name),
            'description'   => $request->description,
            'price_monthly' => $request->price_monthly,
            'price_yearly'  => $request->price_yearly,
            'currency'      => $request->input('currency', 'INR'),
            'trial_days'    => $request->input('trial_days', 0),
            'is_active'     => $request->boolean('is_active', true),
            'is_public'     => $request->boolean('is_public', true),
            'sort_order'    => Plan::max('sort_order') + 1,
        ]);

        // Sync plan features
        $this->syncFeatures($plan, $request->input('features', []));

        return redirect()
            ->route('super-admin.plans.index')
            ->with('success', "Plan \"{$plan->name}\" created successfully.");
    }

    public function edit(Plan $plan): View
    {
        $plan->loadMissing('features');
        $featureKeys    = self::FEATURE_KEYS;
        $featureValues  = $plan->features->pluck('feature_value', 'feature_key');

        return view('super-admin.plans.edit', compact('plan', 'featureKeys', 'featureValues'));
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $plan->update($request->only(
            'name', 'description', 'price_monthly', 'price_yearly',
            'currency', 'trial_days', 'is_active', 'is_public'
        ));

        $this->syncFeatures($plan, $request->input('features', []));

        return redirect()
            ->route('super-admin.plans.index')
            ->with('success', "Plan \"{$plan->name}\" updated successfully.");
    }

    public function toggleActive(Plan $plan): RedirectResponse
    {
        $plan->update(['is_active' => ! $plan->is_active]);

        return back()->with('success', 'Plan visibility updated.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer|exists:plans,id']);

        foreach ($request->order as $sortOrder => $planId) {
            Plan::where('id', $planId)->update(['sort_order' => $sortOrder]);
        }

        return back()->with('success', 'Plans reordered.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        abort_if(
            $plan->subscriptions()->whereIn('status', ['active', 'trialing'])->exists(),
            422,
            'Cannot delete a plan with active subscriptions.'
        );

        $plan->delete();

        return redirect()
            ->route('super-admin.plans.index')
            ->with('success', 'Plan deleted.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function syncFeatures(Plan $plan, array $features): void
    {
        foreach ($features as $key => $value) {
            if (! in_array($key, self::FEATURE_KEYS, true)) {
                continue;
            }

            PlanFeature::updateOrCreate(
                ['plan_id' => $plan->id, 'feature_key' => $key],
                ['feature_value' => (string) $value]
            );
        }

        // Remove features not in the submitted set
        $plan->features()
            ->whereNotIn('feature_key', array_keys($features))
            ->delete();
    }
}
