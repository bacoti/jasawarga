<?php

namespace App\Services\Analytics;

use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminAnalyticsService
{
    public function dashboard(?string $from = null, ?string $to = null): array
    {
        $fromDate = CarbonImmutable::parse($from ?: today()->subDays(29)->toDateString())->startOfDay();
        $toDate = CarbonImmutable::parse($to ?: today()->toDateString())->endOfDay();
        $range = [$fromDate, $toDate];
        $bookingExists = Schema::hasTable('bookings');
        $usersExists = Schema::hasTable('users');
        $partnersExists = Schema::hasTable('partner_profiles');

        $users = ['total' => 0, 'warga' => 0, 'mitra' => 0, 'admin' => 0, 'new' => 0];
        if ($usersExists) {
            $users['total'] = DB::table('users')->count();
            if (Schema::hasColumn('users', 'role')) {
                foreach (['warga', 'mitra', 'admin'] as $role) {
                    $storageRole = match ($role) { 'warga' => 'citizen', 'mitra' => 'partner', default => $role };
                    $users[$role] = DB::table('users')->where('role', $storageRole)->count();
                }
            }
            if (Schema::hasColumn('users', 'created_at')) {
                $users['new'] = DB::table('users')->whereBetween('created_at', $range)->count();
            }
        }

        $partners = ['total' => 0, 'approved' => 0, 'pending' => 0, 'needs_revision' => 0, 'rejected' => 0];
        if ($partnersExists) {
            $partners['total'] = DB::table('partner_profiles')->count();
            if (Schema::hasColumn('partner_profiles', 'verification_status')) {
                foreach (['approved', 'pending', 'needs_revision', 'rejected'] as $status) {
                    $partners[$status] = DB::table('partner_profiles')->where('verification_status', $status)->count();
                }
                // The Stage 2 module may use 'needs_correction' as its correction state.
                if ($partners['needs_revision'] === 0) {
                    $partners['needs_revision'] = DB::table('partner_profiles')->where('verification_status', 'needs_correction')->count();
                }
            }
        }

        $bookings = [
            'total' => 0, 'pending' => 0, 'active' => 0, 'completed' => 0,
            'rejected' => 0, 'expired' => 0, 'cancelled' => 0,
            'completed_value' => 0, 'new_today' => 0,
        ];
        $statusCounts = [];
        $recentBookings = collect();
        $recentActivity = collect();
        $topPartners = collect();
        $topServices = collect();
        $daily = [];
        $activeStatuses = config('jasawarga-analytics.active_booking_statuses', []);

        if ($bookingExists) {
            $query = DB::table('bookings')->whereBetween('created_at', $range);
            $bookings['total'] = (clone $query)->count();
            $bookings['pending'] = (clone $query)->where('status', 'pending_partner_confirmation')->count();
            $bookings['active'] = (clone $query)->whereIn('status', $activeStatuses)->count();
            $completedQuery = DB::table('bookings')->where('status', 'completed');
            if (Schema::hasColumn('bookings', 'completed_at')) {
                $completedQuery->whereBetween('completed_at', $range);
            } else {
                $completedQuery->whereBetween('created_at', $range);
            }
            $bookings['completed'] = (clone $completedQuery)->count();
            $bookings['rejected'] = (clone $query)->where('status', 'rejected')->count();
            $bookings['expired'] = (clone $query)->where('status', 'expired')->count();
            $bookings['cancelled'] = (clone $query)->where('status', 'cancelled')->count();
            $bookings['new_today'] = DB::table('bookings')->whereDate('created_at', today())->count();

            if (Schema::hasColumn('bookings', 'agreed_price')) {
                $bookings['completed_value'] = (float) (clone $completedQuery)->sum('agreed_price');
            }

            $statusCounts = (clone $query)->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')->orderByDesc('total')->get()->mapWithKeys(fn ($row) => [$row->status => (int) $row->total])->all();

            if (Schema::hasTable('users') && Schema::hasTable('partner_profiles')) {
                $recentBookings = (clone $query)
                    ->leftJoin('users as warga', 'warga.id', '=', 'bookings.user_id')
                    ->leftJoin('partner_profiles as partners', 'partners.id', '=', 'bookings.partner_profile_id')
                    ->select('bookings.booking_code', 'bookings.service_name_snapshot', 'bookings.status', 'bookings.created_at', 'bookings.scheduled_at', 'warga.name as warga_name', 'partners.business_name')
                    ->orderByDesc('bookings.created_at')->limit(8)->get();

                $topPartners = (clone $query)
                    ->join('partner_profiles as partners', 'partners.id', '=', 'bookings.partner_profile_id')
                    ->select('partners.business_name', DB::raw('COUNT(bookings.id) as total'))
                    ->groupBy('partners.business_name')->orderByDesc('total')->limit(5)->get();
            }

            if (Schema::hasColumn('bookings', 'service_name_snapshot')) {
                $topServices = (clone $query)->select('service_name_snapshot', DB::raw('COUNT(*) as total'))
                    ->groupBy('service_name_snapshot')->orderByDesc('total')->limit(5)->get();
            }

            if (Schema::hasTable('booking_status_histories')) {
                $activityQuery = DB::table('booking_status_histories')
                    ->join('bookings', 'bookings.id', '=', 'booking_status_histories.booking_id')
                    ->leftJoin('users as actor', 'actor.id', '=', 'booking_status_histories.actor_user_id')
                    ->whereBetween('booking_status_histories.created_at', $range)
                    ->select('bookings.booking_code', 'booking_status_histories.from_status', 'booking_status_histories.to_status', 'booking_status_histories.note', 'booking_status_histories.created_at', 'actor.name as actor_name')
                    ->orderByDesc('booking_status_histories.created_at')->limit(8);
                $recentActivity = $activityQuery->get();
            }

            $chartStart = $fromDate->startOfDay();
            $chartMaxDays = max(7, (int) config('jasawarga-analytics.chart_max_days', 90));
            $chartFloor = $toDate->startOfDay()->subDays($chartMaxDays - 1);
            if ($chartStart->lt($chartFloor)) {
                $chartStart = $chartFloor;
            }
            $chartRange = [$chartStart, $toDate];
            $createdPerDay = DB::table('bookings')->whereBetween('created_at', $chartRange)
                ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
                ->groupBy(DB::raw('DATE(created_at)'))->pluck('total', 'day')->all();
            $completedPerDay = [];
            if (Schema::hasColumn('bookings', 'completed_at')) {
                $completedPerDay = DB::table('bookings')->where('status', 'completed')->whereBetween('completed_at', $chartRange)
                    ->selectRaw('DATE(completed_at) as day, COUNT(*) as total')
                    ->groupBy(DB::raw('DATE(completed_at)'))->pluck('total', 'day')->all();
            }
            foreach (CarbonPeriod::create($chartStart, $toDate->startOfDay()) as $date) {
                $key = $date->toDateString();
                $daily[] = ['date' => $key, 'created' => (int) ($createdPerDay[$key] ?? 0), 'completed' => (int) ($completedPerDay[$key] ?? 0)];
            }
        }

        $catalog = $this->catalogCounts();
        $reviews = $this->optionalReviewSummary($range);
        $reports = $this->optionalReportSummary($range);

        return [
            'range' => ['from' => $fromDate->toDateString(), 'to' => $toDate->toDateString()],
            'users' => $users,
            'partners' => $partners,
            'bookings' => $bookings,
            'statusCounts' => $statusCounts,
            'daily' => $daily,
            'recentBookings' => $recentBookings,
            'recentActivity' => $recentActivity,
            'topPartners' => $topPartners,
            'topServices' => $topServices,
            'catalog' => $catalog,
            'reviews' => $reviews,
            'reports' => $reports,
            'dataSources' => [
                'users' => $usersExists,
                'partners' => $partnersExists,
                'bookings' => $bookingExists,
                'reviews' => $reviews['available'],
                'reports' => $reports['available'],
            ],
        ];
    }

    public function bookingExportRows(string $from, string $to): iterable
    {
        if (! Schema::hasTable('bookings')) {
            return [];
        }

        return DB::table('bookings')->whereBetween('created_at', [CarbonImmutable::parse($from)->startOfDay(), CarbonImmutable::parse($to)->endOfDay()])
            ->orderBy('created_at')
            ->get(['booking_code', 'service_name_snapshot', 'status', 'created_at', 'scheduled_at', 'initial_price_snapshot', 'agreed_price']);
    }

    private function catalogCounts(): array
    {
        $services = config('jasawarga-analytics.services.table', 'services');
        $serviceActive = config('jasawarga-analytics.services.active_column', 'is_active');
        $categories = config('jasawarga-analytics.categories.table', 'categories');
        $categoryActive = config('jasawarga-analytics.categories.active_column', 'is_active');

        return [
            'services' => Schema::hasTable($services) ? DB::table($services)->count() : null,
            'active_services' => Schema::hasTable($services) && Schema::hasColumn($services, $serviceActive) ? DB::table($services)->where($serviceActive, true)->count() : null,
            'categories' => Schema::hasTable($categories) ? DB::table($categories)->count() : null,
            'active_categories' => Schema::hasTable($categories) && Schema::hasColumn($categories, $categoryActive) ? DB::table($categories)->where($categoryActive, true)->count() : null,
        ];
    }

    private function optionalReviewSummary(array $range): array
    {
        $table = config('jasawarga-analytics.optional_tables.reviews', 'service_reviews');
        if (! Schema::hasTable($table)) {
            return ['available' => false, 'count' => null, 'average' => null];
        }
        $query = DB::table($table);
        if (Schema::hasColumn($table, 'created_at')) {
            $query->whereBetween('created_at', $range);
        }
        $count = (clone $query)->count();
        $average = null;
        if (Schema::hasColumn($table, 'rating')) {
            $ratingQuery = clone $query;
            if ($table === 'service_reviews' && Schema::hasColumn($table, 'verification_status')
                && Schema::hasColumn($table, 'moderation_status')) {
                $ratingQuery->where('verification_status', 'verified')->where('moderation_status', 'visible');
                $average = $ratingQuery->exists() ? (float) $ratingQuery->avg('rating') : null;
            } elseif (! Schema::hasColumn($table, 'status')) {
                $average = $ratingQuery->exists() ? (float) $ratingQuery->avg('rating') : null;
            } // Unknown moderation column means no public rating estimate.
        }
        return ['available' => true, 'count' => $count, 'average' => $average];
    }

    private function optionalReportSummary(array $range): array
    {
        $table = config('jasawarga-analytics.optional_tables.reports', 'service_complaints');
        if (! Schema::hasTable($table)) {
            return ['available' => false, 'count' => null, 'open' => null];
        }
        $query = DB::table($table);
        if (Schema::hasColumn($table, 'created_at')) {
            $query->whereBetween('created_at', $range);
        }
        $count = (clone $query)->count();
        $open = null;
        if (Schema::hasColumn($table, 'status')) {
            $open = (clone $query)->whereNotIn('status', ['resolved', 'closed', 'rejected'])->count();
        }
        return ['available' => true, 'count' => $count, 'open' => $open];
    }
}
