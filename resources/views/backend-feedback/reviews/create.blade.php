@extends('backend-feedback.layout')
@section('title','Ajukan Ulasan Database | JasaWarga')
@section('feedback_content')
<div class="max-w-3xl"><p class="text-xs font-bold uppercase tracking-widest text-[#006e9d]">Warga / Ulasan</p><h1 class="mt-2 text-3xl font-extrabold">Tulis ulasan layanan</h1>
<p class="mt-3 rounded-xl border border-[#c7ddeb] bg-[#edf6fb] p-4 text-sm leading-6 text-[#275877]"><strong>Integrasi pemesanan PostgreSQL:</strong> ulasan hanya terverifikasi jika kode pesanan benar-benar milik akun Warga ini, layanannya cocok, dan pekerjaan sudah berstatus selesai. Ulasan yang belum memenuhi syarat tetap berstatus menunggu dan tidak tampil publik. Kode demo localStorage tidak berlaku.</p>
<form method="POST" action="{{ route('db-feedback.reviews.store') }}" enctype="multipart/form-data" class="mt-6 space-y-5 rounded-2xl border border-[#dce6ef] bg-white p-6 sm:p-8">@csrf
    <div><label class="mb-2 block text-sm font-bold" for="partner_service_id">Layanan dari katalog database *</label><select id="partner_service_id" name="partner_service_id" required class="w-full rounded-lg border border-[#cbdbe6] px-3 py-3 text-sm"><option value="">Pilih layanan</option>@foreach($services as $service)<option value="{{ $service->id }}" @selected(old('partner_service_id',$selectedService) == $service->id)>{{ $service->name }} — {{ $service->partner->business_name ?? 'Mitra' }}</option>@endforeach</select></div>
    <div><label for="booking_reference" class="mb-2 block text-sm font-bold">Kode pesanan PostgreSQL *</label><input id="booking_reference" name="booking_reference" value="{{ old('booking_reference') }}" required maxlength="80" minlength="5" placeholder="Contoh: JW-2026-00001" class="w-full rounded-lg border border-[#cbdbe6] px-3 py-3 text-sm"><p class="mt-1 text-xs text-[#6a8498]">Akan dicocokkan ke pesanan PostgreSQL milikmu yang sudah selesai.</p></div>
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach(['rating'=>'Rating keseluruhan','quality_rating'=>'Kualitas pekerjaan','timeliness_rating'=>'Ketepatan waktu','friendliness_rating'=>'Keramahan'] as $field=>$label)
            <div><label class="mb-2 block text-sm font-bold" for="{{ $field }}">{{ $label }} *</label><select id="{{ $field }}" name="{{ $field }}" required class="w-full rounded-lg border border-[#cbdbe6] px-3 py-3 text-sm"><option value="">Pilih rating</option>@for($n=5;$n>=1;$n--)<option value="{{ $n }}" @selected(old($field) == $n)>{{ $n }} / 5</option>@endfor</select></div>
        @endforeach
    </div>
    <div><label class="mb-2 block text-sm font-bold" for="comment">Komentar (20–2.000 karakter) *</label><textarea id="comment" name="comment" rows="5" minlength="20" maxlength="2000" required class="w-full rounded-lg border border-[#cbdbe6] px-3 py-3 text-sm" placeholder="Ceritakan kualitas layanan yang benar-benar kamu alami...">{{ old('comment') }}</textarea></div>
    <div><label class="mb-2 block text-sm font-bold" for="photo">Foto hasil pekerjaan (opsional, maks. 2 MB)</label><input type="file" accept="image/jpeg,image/png,image/webp" id="photo" name="photo" class="block w-full rounded-lg border border-[#cbdbe6] p-3 text-sm"><p class="mt-1 text-xs text-[#6a8498]">Hindari foto yang memperlihatkan dokumen atau identitas pribadi. File disimpan pada disk publik saat diunggah.</p></div>
    <button type="submit" class="w-full rounded-xl bg-[#006d9e] px-5 py-3 font-bold text-white sm:w-auto">Kirim pengajuan ulasan</button>
</form></div>
@endsection
