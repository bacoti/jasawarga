<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Analytics\ReportFilterRequest;
use App\Services\Analytics\AdminAnalyticsService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class AdminReportController extends Controller
{
    public function index(ReportFilterRequest $request, AdminAnalyticsService $analytics): View
    {
        $filters = $request->filters();
        return view('analytics.admin.reports', [
            'report' => $analytics->dashboard($filters['from'], $filters['to']),
            'filters' => $filters,
        ]);
    }

    public function export(ReportFilterRequest $request, AdminAnalyticsService $analytics): StreamedResponse
    {
        $filters = $request->filters();
        $filename = 'jasawarga-bookings-'.$filters['from'].'-'.$filters['to'].'.csv';
        return response()->streamDownload(function () use ($analytics, $filters): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Kode Booking', 'Layanan', 'Status', 'Tanggal Dibuat', 'Jadwal', 'Harga Awal (estimasi)', 'Harga Disepakati']);
            foreach ($analytics->bookingExportRows($filters['from'], $filters['to']) as $row) {
                fputcsv($output, [
                    $this->safeCell($row->booking_code),
                    $this->safeCell($row->service_name_snapshot),
                    $this->safeCell($row->status),
                    $row->created_at,
                    $row->scheduled_at,
                    $row->initial_price_snapshot,
                    $row->agreed_price,
                ]);
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function safeCell(?string $value): string
    {
        $value = (string) $value;
        return preg_match('/^[=+@\-\t\r]/u', $value) ? "'".$value : $value;
    }
}
