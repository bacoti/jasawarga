@extends('backend-feedback.layout')
@section('title','Balasan Ulasan Mitra Database | JasaWarga')
@section('feedback_content')
<p class="text-xs font-bold uppercase tracking-widest text-[#006e9d]">Portal Mitra / PostgreSQL</p><h1 class="mt-2 text-3xl font-extrabold">Ulasan untuk {{ $profile->business_name }}</h1><p class="mt-3 max-w-3xl text-sm leading-6 text-[#647e90]">Hanya ulasan yang telah dibuktikan dari pesanan database. Mitra hanya dapat membalas ulasan milik usahanya sendiri; rating dan komentar Warga tidak dapat diedit.</p>
<div class="mt-7 space-y-5">@forelse($reviews as $review)
<article class="rounded-2xl border border-[#dce6ef] bg-white p-5 sm:p-6">
<div class="flex flex-wrap items-center justify-between gap-3"><div><strong>{{ $review->service->name ?? 'Layanan' }}</strong><p class="mt-1 text-xs text-[#607b90]">{{ $review->citizen->name ?? 'Warga' }} · {{ $review->created_at->format('d M Y') }} · ★ {{ $review->rating }}/5</p></div><span class="rounded-lg px-3 py-2 text-xs font-bold {{ $review->moderation_status === 'hidden' ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }}">{{ $review->moderation_status === 'hidden' ? 'Disembunyikan Admin' : 'Tampil publik' }}</span></div>
<p class="mt-4 whitespace-pre-wrap text-sm leading-6">{{ $review->comment }}</p>
<form method="POST" action="{{ route('partner.db-feedback.reviews.reply',$review) }}" class="mt-5 rounded-xl bg-[#f1f6fa] p-4">@csrf<label class="mb-2 block text-sm font-bold" for="reply-{{ $review->id }}">{{ $review->reply ? 'Edit balasan' : 'Beri balasan' }} (10–600 karakter)</label><textarea id="reply-{{ $review->id }}" name="body" required minlength="10" maxlength="600" rows="3" class="w-full rounded-lg border border-[#c4d7e5] bg-white p-3 text-sm">{{ old('body',$review->reply?->body) }}</textarea><button type="submit" class="mt-3 rounded-lg bg-[#006d9e] px-4 py-2.5 text-xs font-bold text-white">{{ $review->reply ? 'Simpan perubahan' : 'Kirim balasan' }}</button></form>
@if($review->reply)<form method="POST" action="{{ route('partner.db-feedback.reviews.destroy-reply',$review) }}" class="mt-2" onsubmit="return confirm('Hapus balasan Mitra ini?')">@csrf @method('DELETE')<button type="submit" class="text-xs font-bold text-red-700">Hapus balasan</button></form>@endif
</article>
@empty <p class="mt-7 rounded-2xl border border-dashed border-[#cbdce7] bg-white p-10 text-sm">Belum ada ulasan terverifikasi untuk Mitra ini. Hanya ulasan dari pesanan PostgreSQL selesai yang dapat ditampilkan.</p>@endforelse</div><div class="mt-6">{{ $reviews->links() }}</div>
@endsection
