@extends('backend-feedback.layout')
@section('title','Ulasan Saya di Database | JasaWarga')
@section('feedback_content')
<div class="mb-7 flex flex-wrap items-end justify-between gap-3"><div><p class="text-xs font-bold uppercase tracking-widest text-[#006e9d]">Riwayat Warga</p><h1 class="mt-2 text-3xl font-extrabold">Ulasan saya</h1><p class="mt-2 text-sm text-[#637d90]">Hanya ulasan yang benar-benar tersimpan pada akun PostgreSQL ini.</p></div><a class="rounded-xl bg-[#006d9e] px-5 py-3 text-sm font-bold text-white" href="{{ route('db-feedback.reviews.create') }}">Tulis ulasan</a></div>
<div class="space-y-4">@forelse($reviews as $review)
<article class="rounded-2xl border border-[#dbe5ed] bg-white p-5 sm:p-6">
<div class="flex flex-wrap items-center justify-between gap-2"><strong>{{ $review->service->name ?? 'Layanan diarsipkan' }} · {{ $review->partner->business_name ?? 'Mitra' }}</strong><span class="rounded-lg px-3 py-1 text-xs font-bold {{ $review->verification_status === 'verified' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-800' }}">{{ $review->verification_status === 'verified' ? 'Pesanan terverifikasi' : 'Menunggu pesanan PostgreSQL selesai atau validasi Admin' }}</span></div>
<p class="mt-2 text-xs text-[#627e91]">Referensi: {{ $review->booking_reference }} · {{ $review->created_at->format('d M Y') }} · ★ {{ $review->rating }}/5</p><p class="mt-4 whitespace-pre-wrap text-sm leading-6">{{ $review->comment }}</p>
@if($review->moderation_status === 'hidden')<p class="mt-3 text-xs font-bold text-red-700">Tidak ditampilkan publik karena moderasi.</p>@endif
@if($review->reply)<div class="mt-4 rounded-xl bg-[#eef6fa] p-4"><p class="text-xs font-bold text-[#006d9e]">Balasan Mitra</p><p class="mt-1 whitespace-pre-wrap text-sm">{{ $review->reply->body }}</p></div>@endif
@if($review->verification_status === 'pending')<form class="mt-4" method="POST" action="{{ route('db-feedback.reviews.destroy-pending',$review) }}" onsubmit="return confirm('Hapus pengajuan ulasan yang belum diverifikasi?')">@csrf @method('DELETE')<button type="submit" class="rounded-lg border border-[#e7b8b8] px-4 py-2 text-xs font-bold text-red-700">Hapus pengajuan</button></form>@endif
</article>
@empty <p class="rounded-2xl bg-white p-8 text-sm text-[#637d90]">Belum ada ulasan di akun database ini.</p>@endforelse</div><div class="mt-6">{{ $reviews->links() }}</div>
@endsection
