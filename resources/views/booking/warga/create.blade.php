@extends('layouts.profile-stage2')
@section('title', 'Booking Jasa')
@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div><a href="{{ route('partners.public.show', $partner->slug) }}" class="text-sm text-slate-500">← Kembali ke profil Mitra</a><h1 class="mt-2 text-2xl font-bold text-slate-900">Booking jasa</h1><p class="text-slate-600">{{ $partner->business_name }}</p></div>
    @if ($errors->any()) <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div> @endif
    <form method="POST" action="{{ route('bookings.store', $partner->slug) }}" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <div><label class="mb-1 block text-sm font-semibold">Pilih layanan</label><select name="service_id" id="service_id" required class="w-full rounded-xl border-slate-300">@foreach($services as $service)<option value="{{ $service->id }}" data-duration="{{ $service->duration_minutes }}">{{ $service->name }} · {{ $service->duration_minutes }} menit</option>@endforeach</select></div>
        <div><label class="mb-1 block text-sm font-semibold">Alamat pengerjaan</label><select name="address_id" required class="w-full rounded-xl border-slate-300"><option value="">Pilih alamat</option>@foreach($addresses as $address)<option value="{{ $address->id }}">{{ $address->label }} — {{ $address->subdistrict }}, {{ $address->district }} · {{ $address->address_detail }}</option>@endforeach</select>@if($addresses->isEmpty())<p class="mt-2 text-sm text-amber-700">Anda belum memiliki alamat. Tambahkan alamat melalui menu Profil → Alamat terlebih dahulu.</p>@endif</div>
        <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-1 block text-sm font-semibold">Tanggal</label><input id="booking_date" type="date" min="{{ now()->addHours(36)->format('Y-m-d') }}" class="w-full rounded-xl border-slate-300" required></div><div><label class="mb-1 block text-sm font-semibold">Jam tersedia</label><select id="slot_select" name="scheduled_at" required class="w-full rounded-xl border-slate-300"><option value="">Pilih tanggal terlebih dahulu</option></select></div></div>
        <p id="slot_hint" class="text-sm text-slate-500">Pemesanan harus dilakukan minimal 36 jam sebelum waktu layanan. Jam yang ditampilkan mengikuti jadwal Mitra.</p>
        <div><label class="mb-1 block text-sm font-semibold">Catatan kebutuhan (opsional)</label><textarea name="notes" rows="4" maxlength="1500" class="w-full rounded-xl border-slate-300" placeholder="Jelaskan kebutuhan atau kondisi pekerjaan..."></textarea></div>
        <button type="submit" class="w-full rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white hover:bg-slate-700" @if($services->isEmpty() || $addresses->isEmpty()) disabled @endif>Kirim booking</button>
        <p class="text-xs text-slate-500">Setelah dikirim, slot ditahan sementara. Mitra memiliki waktu maksimal 24 jam untuk menerima atau menolak.</p>
    </form>
</div>
<script>
(() => {
 const date = document.getElementById('booking_date');
 const service = document.getElementById('service_id');
 const slots = document.getElementById('slot_select');
 const hint = document.getElementById('slot_hint');
 async function loadSlots() {
   slots.innerHTML = '<option value="">Memuat jadwal...</option>';
   if (!date.value || !service.value) return;
   const url = @json(route('bookings.availability', $partner->slug)) + '?service_id=' + encodeURIComponent(service.value) + '&date=' + encodeURIComponent(date.value);
   try {
     const response = await fetch(url, {headers: {'Accept':'application/json'}});
     const data = await response.json();
     slots.innerHTML = '';
     if (!response.ok || !data.slots || data.slots.length === 0) {
       slots.innerHTML = '<option value="">Tidak ada slot tersedia</option>';
       hint.textContent = 'Tidak ada jadwal tersedia pada tanggal tersebut. Pilih tanggal lain atau hubungi Mitra.';
       return;
     }
     slots.innerHTML = '<option value="">Pilih jam</option>';
     data.slots.forEach(slot => { const option = document.createElement('option'); option.value = slot.value; option.textContent = slot.label; slots.appendChild(option); });
     hint.textContent = 'Pilih salah satu jam yang tersedia. Pemesanan minimal 36 jam sebelumnya; respons Mitra maksimal 24 jam.';
   } catch (e) { slots.innerHTML = '<option value="">Gagal memuat jadwal</option>'; hint.textContent = 'Periksa koneksi lalu coba kembali.'; }
 }
 date.addEventListener('change', loadSlots); service.addEventListener('change', loadSlots);
})();
</script>
@endsection
