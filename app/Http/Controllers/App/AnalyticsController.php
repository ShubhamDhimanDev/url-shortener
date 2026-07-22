<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function show(Request $request, string $ulid): View
    {
        $user = $request->user();

        $link = Link::where('ulid', $ulid)
            ->where(fn ($q) => $q
                ->where('user_id', $user->id)
                ->orWhereIn('team_id', $user->teamMemberships()->pluck('team_id'))
            )
            ->firstOrFail();

        $this->authorize('view', $link);

        // Date range — default last 30 days
        $from = Carbon::parse($request->input('from', now()->subDays(29)->toDateString()))->startOfDay();
        $to   = Carbon::parse($request->input('to', now()->toDateString()))->endOfDay();

        $summary = $this->analytics->getSummary($link, $from, $to);

        return view('app.analytics.show', compact('link', 'summary', 'from', 'to'));
    }
}
