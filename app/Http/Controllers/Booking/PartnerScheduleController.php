<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\PartnerWorkingHoursRequest;
use App\Http\Requests\Booking\StoreScheduleBlockRequest;
use App\Models\Booking;
use App\Models\PartnerProfile;
use App\Models\PartnerScheduleBlock;
use App\Models\PartnerWorkingHour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PartnerScheduleController extends Controller
{
    private const DAY_NAMES = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    public function index(Request $request)
    {
        $partner = $this->partnerFor($request);
        $hours = PartnerWorkingHour::query()->where('partner_profile_id', $partner->id)->get()->keyBy('day_of_week');
        $blocks = PartnerScheduleBlock::query()->where('partner_profile_id', $partner->id)->where('ends_at', '>=', now())->orderBy('starts_at')->paginate(10);
        return view('booking.partner.schedule', compact('partner', 'hours', 'blocks'));
    }

    public function update(PartnerWorkingHoursRequest $request)
    {
        $partner = $this->partnerFor($request);
        DB::transaction(function () use ($request, $partner) {
            PartnerProfile::query()->whereKey($partner->id)->lockForUpdate()->firstOrFail();
            foreach ($request->validated('hours') as $row) {
                $active = (bool) $row['is_active'];
                $start = $active ? $row['start_time'] : null;
                $end = $active ? $row['end_time'] : null;
                if ($active && (! $start || ! $end || $start >= $end)) {
                    throw ValidationException::withMessages(['hours' => 'Jam buka dan tutup harus lengkap; jam tutup harus setelah jam buka.']);
                }
                $this->assertNoFutureBookingOutsideHours($partner->id, (int) $row['day_of_week'], $start, $end, $active);
                PartnerWorkingHour::updateOrCreate(
                    ['partner_profile_id' => $partner->id, 'day_of_week' => (int) $row['day_of_week']],
                    ['start_time' => $start, 'end_time' => $end, 'is_active' => $active],
                );
            }
        });
        return back()->with('success', 'Jam operasional berhasil diperbarui.');
    }

    public function storeBlock(StoreScheduleBlockRequest $request)
    {
        $partner = $this->partnerFor($request);
        $data = $request->validated();
        $start = \Illuminate\Support\Carbon::parse($data['starts_at']);
        $end = \Illuminate\Support\Carbon::parse($data['ends_at']);
        DB::transaction(function () use ($partner, $start, $end, $data) {
            // Use the same partner-row lock as booking creation to prevent race conditions.
            PartnerProfile::query()->whereKey($partner->id)->lockForUpdate()->firstOrFail();
            $conflict = Booking::query()->where('partner_profile_id', $partner->id)
                ->whereIn('status', config('jasawarga-booking.active_booking_statuses', []))
                ->where('scheduled_at', '<', $end)->get(['scheduled_at', 'duration_minutes'])
                ->contains(function ($booking) use ($start, $end) {
                    $bookingStart = \Illuminate\Support\Carbon::parse($booking->scheduled_at);
                    $bookingEnd = $bookingStart->copy()->addMinutes((int) $booking->duration_minutes);
                    return $bookingStart->lt($end) && $bookingEnd->gt($start);
                });
            if ($conflict) {
                throw ValidationException::withMessages(['starts_at' => 'Blokir jadwal bertabrakan dengan booking aktif.']);
            }
            PartnerScheduleBlock::create([
                'partner_profile_id' => $partner->id,
                'starts_at' => $start,
                'ends_at' => $end,
                'reason' => $data['reason'] ?? null,
            ]);
        }, 3);
        return back()->with('success', 'Waktu libur berhasil ditambahkan.');
    }

    public function destroyBlock(Request $request, PartnerScheduleBlock $block)
    {
        $partner = $this->partnerFor($request);
        abort_unless($block->partner_profile_id === $partner->id, 404);
        $block->delete();
        return back()->with('success', 'Blokir jadwal berhasil dihapus.');
    }

    private function assertNoFutureBookingOutsideHours(int $partnerId, int $day, ?string $start, ?string $end, bool $active): void
    {
        $bookings = Booking::query()->where('partner_profile_id', $partnerId)
            ->whereIn('status', config('jasawarga-booking.active_booking_statuses', []))
            ->where('scheduled_at', '>', now())->get(['scheduled_at', 'duration_minutes']);
        foreach ($bookings as $booking) {
            $when = \Illuminate\Support\Carbon::parse($booking->scheduled_at);
            if ($when->dayOfWeek !== $day) continue;
            $finish = $when->copy()->addMinutes((int) $booking->duration_minutes);
            $outside = ! $active || ! $start || ! $end
                || $when->format('H:i:s') < $start.':00'
                || $finish->format('H:i:s') > $end.':00';
            if ($outside) {
                throw ValidationException::withMessages(['hours' => 'Jam operasional tidak dapat diubah/nonaktif karena masih ada booking aktif pada hari tersebut.']);
            }
        }
    }

    private function partnerFor(Request $request): PartnerProfile
    {
        return PartnerProfile::query()->where('user_id', $request->user()->id)->firstOrFail();
    }
}
