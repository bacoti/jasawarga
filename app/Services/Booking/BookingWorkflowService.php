<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\BookingOffer;
use App\Models\BookingStatusHistory;
use App\Models\PartnerProfile;
use App\Models\User;
use App\Models\UserAddress;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BookingWorkflowService
{
    public function __construct(
        private readonly ServiceCatalog $catalog,
        private readonly BookingAvailabilityService $availability,
    ) {}

    public function create(User $warga, PartnerProfile $partner, array $data): Booking
    {
        return DB::transaction(function () use ($warga, $partner, $data) {
            // Serializes slot checks for the same partner across concurrent requests.
            $lockedPartner = PartnerProfile::query()->whereKey($partner->id)->lockForUpdate()->firstOrFail();
            if (! $lockedPartner->isPubliclyVisible()) {
                throw ValidationException::withMessages(['partner' => 'Mitra belum dapat menerima booking karena profil belum disetujui atau sedang diperiksa ulang.']);
            }

            $service = $this->catalog->findActiveForPartner($lockedPartner->id, (int) $data['service_id']);
            $start = CarbonImmutable::parse($data['scheduled_at'], config('app.timezone'));
            $this->availability->assertSlotAvailable($lockedPartner, $start, $service['duration_minutes']);

            $address = UserAddress::query()
                ->where('user_id', $warga->id)
                ->whereKey((int) $data['address_id'])
                ->first();
            if (! $address) {
                throw ValidationException::withMessages(['address_id' => 'Alamat tidak ditemukan pada akun Anda.']);
            }

            $now = now(config('app.timezone'));
            $booking = Booking::create([
                'booking_code' => $this->newBookingCode(),
                'user_id' => $warga->id,
                'partner_profile_id' => $lockedPartner->id,
                'service_id' => $service['id'],
                'service_name_snapshot' => $service['name'],
                'price_type_snapshot' => $service['price_type'],
                'initial_price_snapshot' => $service['price'],
                'agreed_price' => $service['price_type'] === config('jasawarga-booking.services.price_types.fixed', 'fixed') ? $service['price'] : null,
                'duration_minutes' => $service['duration_minutes'],
                'scheduled_at' => $start,
                'user_address_id' => $address->id,
                'address_snapshot' => [
                    'label' => $address->label,
                    'recipient_name' => $address->recipient_name,
                    'phone' => $address->phone,
                    'province' => $address->province,
                    'city' => $address->city,
                    'district' => $address->district,
                    'subdistrict' => $address->subdistrict,
                    'postal_code' => $address->postal_code,
                    'address_detail' => $address->address_detail,
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude,
                ],
                'notes' => $data['notes'] ?? null,
                'status' => Booking::PENDING_PARTNER,
                'partner_response_deadline' => $now->copy()->addHours((int) config('jasawarga-booking.partner_response_hours', 24)),
            ]);

            $this->recordStatus($booking, $warga, null, Booking::PENDING_PARTNER, 'Booking dibuat oleh Warga. Slot ditahan sementara menunggu respons Mitra.');
            return $booking->fresh(['partnerProfile', 'offers', 'statusHistories']);
        }, 3);
    }

    public function partnerRespond(Booking $booking, User $actor, bool $accept): Booking
    {
        return DB::transaction(function () use ($booking, $actor, $accept) {
            $locked = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $this->assertStatus($locked, [Booking::PENDING_PARTNER], 'Booking ini sudah tidak menunggu respons Mitra.');
            if ($locked->partner_response_deadline->isPast()) {
                $this->transition($locked, Booking::EXPIRED, $actor, 'Booking kedaluwarsa sebelum Mitra merespons.');
                $locked->forceFill(['expired_at' => now()])->save();
                return $locked->fresh();
            }

            if (! $accept) {
                $locked->forceFill(['partner_rejected_at' => now()])->save();
                $this->transition($locked, Booking::REJECTED, $actor, 'Mitra menolak booking.');
                return $locked->fresh();
            }

            $next = $locked->price_type_snapshot === config('jasawarga-booking.services.price_types.estimate', 'estimate')
                ? Booking::INSPECTION_SCHEDULED
                : Booking::CONFIRMED;
            $locked->forceFill([
                'partner_accepted_at' => now(),
                'confirmed_at' => $next === Booking::CONFIRMED ? now() : null,
            ])->save();
            $this->transition($locked, $next, $actor, $next === Booking::INSPECTION_SCHEDULED
                ? 'Mitra menerima jadwal inspeksi; harga final akan diajukan setelah pemeriksaan.'
                : 'Mitra menerima booking dengan harga layanan tetap.');

            return $locked->fresh();
        }, 3);
    }

    public function submitFinalOffer(Booking $booking, User $actor, float $amount, ?string $note): BookingOffer
    {
        return DB::transaction(function () use ($booking, $actor, $amount, $note) {
            $locked = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $this->assertStatus($locked, [Booking::INSPECTION_SCHEDULED], 'Penawaran final hanya dapat dikirim setelah Mitra menerima jadwal inspeksi.');
            $offer = BookingOffer::create([
                'booking_id' => $locked->id,
                'created_by_user_id' => $actor->id,
                'amount' => $amount,
                'note' => $note,
                'status' => 'pending',
            ]);
            $this->transition($locked, Booking::PENDING_PRICE_APPROVAL, $actor, 'Mitra mengirim penawaran harga final.');
            return $offer;
        }, 3);
    }

    public function wargaRespondToOffer(Booking $booking, BookingOffer $offer, User $actor, bool $approve): Booking
    {
        return DB::transaction(function () use ($booking, $offer, $actor, $approve) {
            $locked = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $this->assertStatus($locked, [Booking::PENDING_PRICE_APPROVAL], 'Booking tidak sedang menunggu persetujuan harga.');
            $lockedOffer = BookingOffer::query()->whereKey($offer->id)->where('booking_id', $locked->id)->lockForUpdate()->first();
            if (! $lockedOffer || $lockedOffer->status !== 'pending') {
                throw ValidationException::withMessages(['offer' => 'Penawaran tidak ditemukan atau sudah diproses.']);
            }

            $lockedOffer->forceFill(['status' => $approve ? 'accepted' : 'rejected', 'responded_at' => now()])->save();
            if ($approve) {
                $locked->forceFill(['agreed_price' => $lockedOffer->amount, 'confirmed_at' => now()])->save();
                $this->transition($locked, Booking::CONFIRMED, $actor, 'Warga menyetujui harga final Mitra.');
            } else {
                // Rejection after inspection is recorded as inspection-only, not as generic cancellation.
                $this->transition($locked, Booking::INSPECTION_NO_REPAIR, $actor, 'Warga menolak harga perbaikan; inspeksi tercatat selesai tanpa perbaikan.');
            }
            return $locked->fresh();
        }, 3);
    }

    public function startWork(Booking $booking, User $actor): Booking
    {
        return DB::transaction(function () use ($booking, $actor) {
            $locked = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $this->assertStatus($locked, [Booking::CONFIRMED], 'Pekerjaan hanya dapat dimulai setelah booking dikonfirmasi.');
            $locked->forceFill(['started_at' => now()])->save();
            $this->transition($locked, Booking::IN_PROGRESS, $actor, 'Mitra memulai pekerjaan.');
            return $locked->fresh();
        }, 3);
    }

    public function requestCompletion(Booking $booking, User $actor): Booking
    {
        return DB::transaction(function () use ($booking, $actor) {
            $locked = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $this->assertStatus($locked, [Booking::IN_PROGRESS], 'Pekerjaan belum berada pada status sedang dikerjakan.');
            $locked->forceFill(['completion_requested_at' => now()])->save();
            $this->transition($locked, Booking::COMPLETION_PENDING, $actor, 'Mitra mengajukan konfirmasi penyelesaian.');
            return $locked->fresh();
        }, 3);
    }

    public function confirmCompletion(Booking $booking, User $actor): Booking
    {
        return DB::transaction(function () use ($booking, $actor) {
            $locked = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $this->assertStatus($locked, [Booking::COMPLETION_PENDING], 'Belum ada pengajuan penyelesaian dari Mitra.');
            $locked->forceFill(['completed_at' => now()])->save();
            $this->transition($locked, Booking::COMPLETED, $actor, 'Warga mengonfirmasi pekerjaan selesai.');
            return $locked->fresh();
        }, 3);
    }

    public function cancelByWarga(Booking $booking, User $actor, string $reason): Booking
    {
        return DB::transaction(function () use ($booking, $actor, $reason) {
            $locked = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $this->assertStatus($locked, config('jasawarga-booking.warga_cancelable_statuses', []), 'Booking pada tahap ini tidak dapat dibatalkan melalui akun Warga.');
            $locked->forceFill(['cancelled_at' => now(), 'cancellation_reason' => $reason])->save();
            $this->transition($locked, Booking::CANCELLED, $actor, 'Booking dibatalkan oleh Warga: '.$reason);
            return $locked->fresh();
        }, 3);
    }

    public function expirePending(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            $locked = Booking::query()->whereKey($booking->id)->lockForUpdate()->first();
            if (! $locked || $locked->status !== Booking::PENDING_PARTNER || ! $locked->partner_response_deadline || $locked->partner_response_deadline->isFuture()) {
                return;
            }
            $locked->forceFill(['expired_at' => now()])->save();
            $this->transition($locked, Booking::EXPIRED, null, 'Booking kedaluwarsa otomatis karena tidak ada respons Mitra dalam 24 jam.');
        }, 3);
    }

    private function assertStatus(Booking $booking, array $allowed, string $message): void
    {
        if (! in_array($booking->status, $allowed, true)) {
            throw ValidationException::withMessages(['status' => $message]);
        }
    }

    private function transition(Booking $booking, string $to, ?User $actor, string $note): void
    {
        $from = $booking->status;
        $booking->forceFill(['status' => $to])->save();
        $this->recordStatus($booking, $actor, $from, $to, $note);
    }

    private function recordStatus(Booking $booking, ?User $actor, ?string $from, string $to, string $note): void
    {
        BookingStatusHistory::create([
            'booking_id' => $booking->id,
            'actor_user_id' => $actor?->id,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
            'created_at' => now(),
        ]);
    }

    private function newBookingCode(): string
    {
        do {
            $code = 'JW-'.now()->format('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        } while (Booking::where('booking_code', $code)->exists());
        return $code;
    }
}
