<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\PartnerProfile;
use App\Models\PartnerScheduleBlock;
use App\Models\PartnerWorkingHour;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class BookingAvailabilityService
{
    public function __construct(private readonly ServiceCatalog $catalog) {}

    public function availableSlots(PartnerProfile $partner, int $serviceId, string $date): array
    {
        $service = $this->catalog->findActiveForPartner($partner->id, $serviceId);
        $day = CarbonImmutable::parse($date, config('app.timezone'))->startOfDay();
        $hour = PartnerWorkingHour::query()
            ->where('partner_profile_id', $partner->id)
            ->where('day_of_week', $day->dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (! $hour || ! $partner->isPubliclyVisible()) {
            return [];
        }

        $opens = CarbonImmutable::parse($day->toDateString().' '.$hour->start_time, config('app.timezone'));
        $closes = CarbonImmutable::parse($day->toDateString().' '.$hour->end_time, config('app.timezone'));
        if ($closes->lessThanOrEqualTo($opens)) {
            return [];
        }

        $slots = [];
        $step = max(5, (int) config('jasawarga-booking.slot_interval_minutes', 30));
        $minimum = now(config('app.timezone'))->addHours((int) config('jasawarga-booking.min_booking_hours', 36));
        $cursor = $opens;

        while ($cursor->copy()->addMinutes($service['duration_minutes'])->lessThanOrEqualTo($closes)) {
            $end = $cursor->addMinutes($service['duration_minutes']);
            if ($cursor->greaterThanOrEqualTo($minimum) && $this->isSlotFree($partner->id, $cursor, $end)) {
                $slots[] = [
                    'value' => $cursor->format('Y-m-d\TH:i'),
                    'label' => $cursor->format('H:i'),
                ];
            }
            $cursor = $cursor->addMinutes($step);
        }

        return $slots;
    }

    public function assertSlotAvailable(PartnerProfile $partner, CarbonImmutable $start, int $duration): void
    {
        $end = $start->addMinutes($duration);
        if ($start->lessThan(now(config('app.timezone'))->addHours((int) config('jasawarga-booking.min_booking_hours', 36)))) {
            throw ValidationException::withMessages(['scheduled_at' => 'Booking harus dibuat minimal 36 jam sebelum waktu layanan.']);
        }

        $hour = PartnerWorkingHour::query()
            ->where('partner_profile_id', $partner->id)
            ->where('day_of_week', $start->dayOfWeek)
            ->where('is_active', true)
            ->first();
        if (! $hour) {
            throw ValidationException::withMessages(['scheduled_at' => 'Mitra belum mengatur jam operasional pada hari tersebut.']);
        }

        $opens = CarbonImmutable::parse($start->toDateString().' '.$hour->start_time, config('app.timezone'));
        $closes = CarbonImmutable::parse($start->toDateString().' '.$hour->end_time, config('app.timezone'));
        if ($start->lessThan($opens) || $end->greaterThan($closes) || $end->toDateString() !== $start->toDateString()) {
            throw ValidationException::withMessages(['scheduled_at' => 'Waktu booking berada di luar jam operasional Mitra atau melewati akhir hari kerja.']);
        }

        if (! $this->isSlotFree($partner->id, $start, $end)) {
            throw ValidationException::withMessages(['scheduled_at' => 'Jadwal tersebut sudah terisi atau diblokir. Silakan pilih waktu lain.']);
        }
    }

    public function overlapsActiveBooking(int $partnerId, CarbonImmutable $start, CarbonImmutable $end, ?int $ignoreBookingId = null): bool
    {
        $query = Booking::query()
            ->where('partner_profile_id', $partnerId)
            ->whereIn('status', config('jasawarga-booking.active_booking_statuses', []))
            ->where('scheduled_at', '<', $end->toDateTimeString());
        if ($ignoreBookingId) {
            $query->where('id', '<>', $ignoreBookingId);
        }

        foreach ($query->get(['id', 'scheduled_at', 'duration_minutes']) as $booking) {
            $existingStart = CarbonImmutable::parse($booking->scheduled_at);
            $existingEnd = $existingStart->addMinutes((int) $booking->duration_minutes);
            if ($existingStart->lessThan($end) && $existingEnd->greaterThan($start)) {
                return true;
            }
        }
        return false;
    }

    private function isSlotFree(int $partnerId, CarbonImmutable $start, CarbonImmutable $end): bool
    {
        $blocked = PartnerScheduleBlock::query()
            ->where('partner_profile_id', $partnerId)
            ->where('starts_at', '<', $end->toDateTimeString())
            ->where('ends_at', '>', $start->toDateTimeString())
            ->exists();

        return ! $blocked && ! $this->overlapsActiveBooking($partnerId, $start, $end);
    }
}
