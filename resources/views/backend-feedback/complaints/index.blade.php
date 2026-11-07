@extends('backend-feedback.layout')
@section('title','Pengaduan Saya PostgreSQL | JasaWarga')
@section('feedback_content')
<p class="text-xs font-bold uppercase tracking-widest text-[#006e9d]">Layanan bantuan warga</p><h1 class="mt-2 text-3xl font-extrabold">Pengaduan saya</h1><p class="mt-3 max-w-3xl text-sm leading-6 text-[#607c90]">Laporkan masalah layanan secara tertulis. Pengaduan ini tersimpan pada akunmu dan hanya dapat dibaca kamu dan Admin. Kode pesanan yang dimasukkan pengguna masih berupa referensi pelapor; kepemilikannya belum diverifikasi secara otomatis pada pengaduan.</p>
<div class="mt-7 grid gap-6 xl:grid-cols-[minmax(0,1fr)_390px]">
<section class="space-y-4" aria-label="Riwayat pengaduan">
    @forelse($complaints as $complaint)
    <article class="rounded-2xl border border-[#dbe5ed] bg-white p-5">
        <div class="flex flex-wrap items-center justify-between gap-2"><strong>{{ $complaint->subject }}</strong><span class="rounded-full bg-[#e9f2f7] px-3 py-1 text-xs font-bold text-[#255e81]">{{ str_replace('_',' ', $complaint->status) }}</span></div>
        <p class="mt-1 text-xs text-[#687f91]">Pengaduan #{{ $complaint->id }} · {{ $complaint->created_at->format('d M Y H:i') }} · Prioritas {{ $complaint->priority }}</p>
        @if($complaint->booking_reference)<p class="mt-2 text-xs font-semibold text-amber-700">Referensi klaim: {{ $complaint->booking_reference }} (belum terverifikasi)</p>@endif
        <p class="mt-3 whitespace-pre-wrap text-sm leading-6">{{ $complaint->description }}</p>
        @if($complaint->resolution_note)<div class="mt-4 rounded-xl bg-[#eff5f8] p-4 text-sm"><strong>Catatan penanganan terakhir:</strong><p class="mt-2 whitespace-pre-wrap">{{ $complaint->resolution_note }}</p></div>@endif
        @if($complaint->actions->isNotEmpty())<details class="mt-4"><summary class="cursor-pointer text-xs font-bold text-[#006d9e]">Riwayat penanganan</summary><ol class="mt-3 space-y-2 text-xs text-[#536e81]">@foreach($complaint->actions as $action)<li>{{ $action->created_at->format('d M Y') }} · {{ $action->from_status }} → {{ $action->to_status }}: {{ $action->note }}</li>@endforeach</ol></details>@endif
    </article>
    @empty <div class="rounded-2xl border border-dashed border-[#cadbe6] bg-white p-10 text-sm text-[#5b7688]">Belum ada pengaduan yang dikirim dari akun database ini.</div>@endforelse
    <div>{{ $complaints->links() }}</div>
</section>
<aside class="h-fit rounded-2xl border border-[#dbe5ed] bg-white p-6 shadow-sm" aria-label="Form pengaduan baru">
    <h2 class="text-xl font-extrabold">Buat pengaduan</h2><p class="mt-2 text-xs leading-5 text-[#657d90]">Jelaskan kejadian secara faktual. Jangan memasukkan password, NIK, atau data sensitif orang lain.</p>
    <form class="mt-5 space-y-4" action="{{ route('db-feedback.complaints.store') }}" method="POST">@csrf
        <div><label class="mb-2 block text-xs font-bold" for="complaint-service">Layanan (opsional)</label><select id="complaint-service" name="partner_service_id" class="w-full rounded-lg border border-[#cbdbe6] p-3 text-sm"><option value="">Tidak terkait layanan tertentu</option>@foreach($services as $service)<option value="{{ $service->id }}" @selected(old('partner_service_id')==$service->id)>{{ $service->name }}</option>@endforeach</select></div>
        <div><label class="mb-2 block text-xs font-bold" for="complaint-review">Ulasan saya (opsional)</label><select id="complaint-review" name="service_review_id" class="w-full rounded-lg border border-[#cbdbe6] p-3 text-sm"><option value="">Tidak terkait ulasan</option>@foreach($reviews as $review)<option value="{{ $review->id }}" @selected(old('service_review_id')==$review->id)>#{{ $review->id }} — {{ $review->booking_reference }}</option>@endforeach</select><p class="mt-1 text-[11px] text-[#6d8395]">Jika memilih ulasan, layanan dan kode diambil dari ulasan tersebut.</p></div>
        <div><label class="mb-2 block text-xs font-bold" for="complaint-ref">Kode pesanan (opsional)</label><input id="complaint-ref" name="booking_reference" value="{{ old('booking_reference') }}" maxlength="80" class="w-full rounded-lg border border-[#cbdbe6] p-3 text-sm" placeholder="Referensi PostgreSQL, bukan demo"></div>
        <div><label class="mb-2 block text-xs font-bold" for="complaint-subject">Judul masalah *</label><input required id="complaint-subject" name="subject" value="{{ old('subject') }}" minlength="8" maxlength="140" class="w-full rounded-lg border border-[#cbdbe6] p-3 text-sm" placeholder="Ringkasan masalah"></div>
        <div><label class="mb-2 block text-xs font-bold" for="complaint-description">Kronologi (30–4.000 karakter) *</label><textarea required minlength="30" maxlength="4000" rows="6" id="complaint-description" name="description" class="w-full rounded-lg border border-[#cbdbe6] p-3 text-sm" placeholder="Apa yang terjadi dan kapan?">{{ old('description') }}</textarea></div>
        <button type="submit" class="w-full rounded-xl bg-[#006d9e] px-4 py-3 text-sm font-bold text-white">Kirim pengaduan</button>
    </form>
</aside></div>
@endsection
