<?php

namespace App\Observers;

use App\Models\Booking;
use App\Models\User;
use App\Services\Notifications\PlatformNotificationService;

class BookingObserver
{
    public function __construct(private readonly PlatformNotificationService $notifications) {}

    public function created(Booking $booking): void
    {
        $booking->loadMissing(['partnerProfile.user']);
        $partnerUser = $booking->partnerProfile?->user;
        $code = (string) $booking->booking_code;

        if ($partnerUser) {
            $this->notifications->notify(
                $partnerUser,
                'booking.created',
                'Pesanan baru masuk',
                "Ada permintaan {$booking->service_name_snapshot} baru dengan kode {$code}. Tinjau sebelum batas respons 24 jam.",
                '/mitra/workspace/pesanan/'.$code,
                ['booking_id' => $booking->id, 'booking_code' => $code, 'status' => $booking->status],
            );
        }

        $this->notifications->notifyAdmins(
            'booking.created',
            'Booking baru tercatat',
            "Booking {$code} telah dibuat dan menunggu respons Mitra.",
            '/admin/pesanan',
            ['booking_id' => $booking->id, 'booking_code' => $code, 'status' => $booking->status],
        );
    }

    public function updated(Booking $booking): void
    {
        if (! $booking->wasChanged('status')) {
            return;
        }

        $booking->loadMissing(['warga', 'partnerProfile.user']);
        $label = config('jasawarga-analytics.booking_statuses.'.$booking->status, $booking->status);
        $code = (string) $booking->booking_code;
        $message = "Status pesanan {$code} berubah menjadi {$label}.";
        $data = [
            'booking_id' => $booking->id,
            'booking_code' => $code,
            'from_status' => $booking->getOriginal('status'),
            'to_status' => $booking->status,
        ];

        if ($booking->warga) {
            $this->notifications->notify(
                $booking->warga,
                'booking.status_changed',
                'Pembaruan pesanan',
                $message,
                '/akun/pesanan/'.$code,
                $data,
            );
        }

        $partnerUser = $booking->partnerProfile?->user;
        if ($partnerUser) {
            $this->notifications->notify(
                $partnerUser,
                'booking.status_changed',
                'Pembaruan pesanan',
                $message,
                '/mitra/workspace/pesanan/'.$code,
                $data,
            );
        }
    }
}
