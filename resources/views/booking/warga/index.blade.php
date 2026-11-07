@extends('layouts.profile-stage2')
@section('title', 'Pesanan Saya')
@section('content')
<div class="mx-auto max-w-5xl space-y-5"><div><h1 class="text-2xl font-bold">Pesanan saya</h1><p class="text-slate-600">Pantau booking, penawaran, dan penyelesaian jasa.</p></div>
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">@forelse($bookings as $booking)<a href="{{ route('account.bookings.show', $booking->booking_code) }}" class="flex flex-col gap-2 border-b border-slate-100 p-5 hover:bg-slate-50 sm:flex-row sm:items-center sm:justify-between"><div><div class="font-semibold">{{ $booking->service_name_snapshot }} · {{ $booking->partnerProfile?->business_name }}</div><div class="text-sm text-slate-500">{{ $booking->booking_code }} · {{ $booking->scheduled_at?->format('d M Y, H:i') }}</div></div><div class="text-sm font-semibold text-slate-700">{{ str_replace('_',' ',ucfirst($booking->status)) }} →</div></a>@empty<div class="p-10 text-center text-slate-500">Belum ada booking.</div>@endforelse</div>{{ $bookings->links() }}</div>
@endsection
