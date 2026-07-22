<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Link;
use App\Models\LinkClick;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();

        // ── Headline stats ────────────────────────────────────────────────
        $totalUsers    = User::withTrashed()->count();
        $activeUsers   = User::count();
        $totalLinks    = Link::withTrashed()->count();
        $clicksToday   = LinkClick::whereDate('clicked_at', $today)->where('is_bot', false)->count();

        // Monthly Recurring Revenue — sum of active/trialing subscription plan prices
        $mrr = Subscription::whereIn('status', ['active', 'trialing'])
            ->with('plan')
            ->get()
            ->sum(fn ($sub) => (float) ($sub->plan?->price_monthly ?? 0));

        // ── New signups last 30 days (chart data) ─────────────────────────
        $signups = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(29))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        // Fill zeros for missing days
        $signupChart = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $signupChart->put($date, $signups->get($date, 0));
        }

        // ── Clicks last 30 days ───────────────────────────────────────────
        $clicksChart = collect();
        $clicksByDate = LinkClick::select(
                DB::raw('DATE(clicked_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('is_bot', false)
            ->where('clicked_at', '>=', Carbon::now()->subDays(29))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $clicksChart->put($date, $clicksByDate->get($date, 0));
        }

        // ── Recent signups ────────────────────────────────────────────────
        $recentUsers = User::with('roles')
            ->latest()
            ->limit(5)
            ->get();

        // ── Active subscriptions by plan ──────────────────────────────────
        $subscriptionsByPlan = Subscription::whereIn('status', ['active', 'trialing'])
            ->with('plan')
            ->get()
            ->groupBy(fn ($s) => $s->plan?->name ?? 'Unknown')
            ->map->count();

        // ── Revenue this month ────────────────────────────────────────────
        $revenueThisMonth = Invoice::paid()
            ->whereMonth('paid_at', $today->month)
            ->whereYear('paid_at', $today->year)
            ->sum('total');
        return view('super-admin.dashboard', compact(
            'totalUsers', 'activeUsers', 'totalLinks', 'clicksToday',
            'mrr', 'signupChart', 'clicksChart', 'recentUsers',
            'subscriptionsByPlan', 'revenueThisMonth'
        ));
    }
}
