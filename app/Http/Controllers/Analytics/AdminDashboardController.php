<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AdminAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request, AdminAnalyticsService $analytics): View
    {
        $from = $request->query('from', today()->subDays(29)->toDateString());
        $to = $request->query('to', today()->toDateString());
        validator(['from' => $from, 'to' => $to], ['from' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'], 'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from', 'before_or_equal:today']])->validate();
        if (\Carbon\CarbonImmutable::parse($from)->diffInDays(\Carbon\CarbonImmutable::parse($to)) + 1 > (int) config('jasawarga-analytics.date_filter_max_days', 366)) {
            abort(422, 'Rentang dashboard maksimal 366 hari.');
        }
        return view('analytics.admin.dashboard', ['report' => $analytics->dashboard($from, $to)]);
    }
}
