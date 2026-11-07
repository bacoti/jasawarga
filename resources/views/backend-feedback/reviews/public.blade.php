@extends('backend-feedback.layout')
@section('title','Ulasan Layanan Database | JasaWarga')
@section('feedback_content')
<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div><p class="text-xs font-bold uppercase tracking-widest text-[#006e9d]">Kepercayaan warga</p><h1 class="mt-2 text-3xl font-extrabold tracking-tight">Ulasan layanan</h1><p class="mt-2 max-w-2xl text-sm leading-6 text-[#61788b]">Hanya ulasan dari pesanan PostgreSQL yang terverifikasi dan lolos moderasi yang ditampilkan. Ulasan demo browser tidak dihitung.</p></div>
    @auth @if(auth()->user()->role === 'citizen')<a href="{{ route('db-feedback.reviews.create') }}" class="rounded-xl bg-[#006d9e] px-5 py-3 text-sm font-bold text-white">Tulis ulasan</a>@endif @endauth
</div>
<form method="GET" action="{{ route('db-feedback.reviews.public') }}" class="mb-5 flex flex-wrap items-center gap-3 rounded-xl border border-[#dbe5ed] bg-white p-4">
    <label for="bintang" class="text-sm font-bold">Rating</label><select name="bintang" id="bintang" class="rounded-lg border border-[#cbdbe6] px-3 py-2 text-sm"><option value="">Semua bintang</option>@for($star=5;$star>=1;$star--)<option value="{{ $star }}" @selected(request('bintang') == $star)>{{ $star }} bintang</option>@endfor</select>
    <button type="submit" class="rounded-lg bg-[#142f44] px-5 py-2.5 text-sm font-bold text-white">Terapkan</button><span class="ml-auto text-xs font-semibold text-[#668096]">{{ $reviews->total() }} ulasan terpublikasi</span>
</form>
<div class="space-y-4">
@forelse($reviews as $review)
<article class="rounded-2xl border border-[#dbe5ed] bg-white p-5 shadow-sm sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-2"><div><p class="font-extrabold">{{ \Illuminate\Support\Str::before($review->citizen->name ?? 'Warga', ' ') }} · {{ $review->partner->business_name ?? 'Mitra' }}</p><p class="mt-1 text-xs text-[#667e91]">{{ $review->service->name ?? 'Layanan' }} · {{ $review->created_at->format('d M Y') }}</p></div><span class="rounded-full bg-amber-50 px-3 py-1 text-sm font-extrabold text-amber-800">★ {{ $review->rating }}/5</span></div>
    <p class="mt-4 whitespace-pre-wrap text-sm leading-7 text-[#3f566c]">{{ $review->comment }}</p>
    <div class="mt-3 flex flex-wrap gap-3 text-xs font-semibold text-[#60798b]"><span>Kualitas {{ $review->quality_rating }}/5</span><span>Waktu {{ $review->timeliness_rating }}/5</span><span>Keramahan {{ $review->friendliness_rating }}/5</span></div>
    @if($review->photo_path)<a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($review->photo_path) }}" target="_blank" rel="noopener noreferrer"><img loading="lazy" class="mt-4 h-36 max-w-full rounded-xl border border-[#e0e8ee] object-cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($review->photo_path) }}" alt="Foto dokumentasi ulasan"></a>@endif
    @if($review->reply)<div class="mt-5 rounded-xl border-l-4 border-[#157ead] bg-[#f0f7fa] p-4"><p class="text-xs font-extrabold text-[#006098]">Balasan Mitra</p><p class="mt-2 whitespace-pre-wrap text-sm leading-6">{{ $review->reply->body }}</p></div>@endif
</article>
@empty <div class="rounded-2xl border border-dashed border-[#c8d9e5] bg-white px-6 py-14 text-center"><p class="font-bold">Belum ada ulasan terverifikasi untuk ditampilkan.</p><p class="mt-2 text-sm text-[#61788b]">Ulasan ditampilkan setelah pesanan selesai terverifikasi dan lolos moderasi.</p></div>
@endforelse
</div><div class="mt-6">{{ $reviews->links() }}</div>
@endsection
