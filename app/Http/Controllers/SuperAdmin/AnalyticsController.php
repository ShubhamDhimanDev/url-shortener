<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\User;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function index(Request $request): View
    {
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : Carbon::now()->subDays(29)->startOfDay();

        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : Carbon::now()->endOfDay();

        // Platform-wide aggregated stats
        $summary = $this->analytics->getPlatformSummary($from, $to);

        // Top 10 links by clicks in the period
        $topLinks = Link::withoutTrashed()
            ->with('user')
            ->withCount(['clicks as period_clicks' => function ($q) use ($from, $to) {
                $q->whereBetween('clicked_at', [$from, $to])->where('is_bot', false);
            }])
            ->orderByDesc('period_clicks')
            ->limit(10)
            ->get();

        // Top 10 users by clicks generated in the period
        $topUsers = User::withoutTrashed()
            ->with('roles')
            ->withCount(['links as total_links'])
            ->addSelect([
                'period_clicks' => \App\Models\LinkClick::selectRaw('COUNT(*)')
                    ->whereColumn('link_clicks.link_id', 'links.id')
                    ->join('links', 'links.user_id', '=', 'users.id')
                    ->whereBetween('link_clicks.clicked_at', [$from, $to])
                    ->where('link_clicks.is_bot', false)
                    ->limit(1),
            ])
            ->orderByDesc('total_links')
            ->limit(10)
            ->get();

        return view('super-admin.analytics.index', compact(
            'summary', 'topLinks', 'topUsers', 'from', 'to'
        ));
    }
}
