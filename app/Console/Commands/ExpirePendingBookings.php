<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\Booking\BookingWorkflowService;
use Illuminate\Console\Command;

class ExpirePendingBookings extends Command
{
    protected $signature = 'jasawarga:expire-bookings';
    protected $description = 'Kedaluwarsakan booking yang tidak direspons Mitra dalam 24 jam.';

    public function handle(BookingWorkflowService $workflow): int
    {
        $count = 0;
        Booking::query()
            ->where('status', Booking::PENDING_PARTNER)
            ->where('partner_response_deadline', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($bookings) use ($workflow, &$count) {
                foreach ($bookings as $booking) {
                    $workflow->expirePending($booking);
                    $count++;
                }
            });
        $this->info("{$count} booking diperiksa untuk kedaluwarsa.");
        return self::SUCCESS;
    }
}
