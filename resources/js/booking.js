import { activeServices, normalizeServiceId, SERVICES_KEY, SERVICES_EVENT } from './services-store.js';
import { ADMIN_CATEGORIES_KEY, ADMIN_CATEGORIES_EVENT } from './admin-category-store.js';
import { createOrderFromBooking, readOrders, ORDER_KEY } from './orders-store.js';
import { getSchedule, dateClosedReason, availableSlots, currentSlotCheck, SCHEDULE_KEY, SCHEDULE_EVENT } from './schedule-store.js';
import { PARTNER_SETTINGS_KEY, PARTNER_SETTINGS_EVENT, getPartnerAvailability } from './partner-store.js';

/**
 * JasaWarga - alur pemesanan MOCK, tanpa database, POST, atau API.
 * Data dikirim antarhalaman memakai sessionStorage tab browser saja.
 * Jangan gunakan alur ini untuk pesanan sungguhan.
 */
import { currentAccount } from './account-store.js';
const STORAGE_KEY = 'jasawarga:prototype-booking:v1';
const LIFETIME_MS = 30 * 60 * 1000; // 30 menit
const idr = new Intl.NumberFormat('id-ID', {
  style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
  minimumFractionDigits: 0,
});

function formatDateId(iso) {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(iso || '')) return '—';
  const date = new Date(`${iso}T00:00:00Z`);
  if (Number.isNaN(date.getTime())) return '—';
  return new Intl.DateTimeFormat('id-ID', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
    timeZone: 'UTC',
  }).format(date);
}

function localDateMakassar() {
  const parts = new Intl.DateTimeFormat('en-GB', {
    timeZone: 'Asia/Makassar', year: 'numeric', month: '2-digit', day: '2-digit',
  }).formatToParts(new Date());
  const get = (type) => parts.find(p => p.type === type).value;
  return `${get('year')}-${get('month')}-${get('day')}`;
}

function addUtcDays(iso, days) {
  const date = new Date(`${iso}T00:00:00Z`);
  date.setUTCDate(date.getUTCDate() + days);
  return date.toISOString().slice(0, 10);
}

function maskPhone(raw) {
  const digits = String(raw).replace(/\D/g, '');
  return `${'*'.repeat(Math.max(0, digits.length - 4))}${digits.slice(-4)}`;
}

const booking = document.querySelector('#jw-booking');
if (booking) {
  const form = booking.querySelector('#jw-booking-form');
  const providerId = Number(booking.dataset.providerId);
  const providerCategory = booking.dataset.providerCategory;
  const seed = JSON.parse(booking.querySelector('[data-booking-services-seed]').textContent);
  const categorySeed = JSON.parse(booking.querySelector('[data-booking-categories-seed]').textContent);
  const serviceNotice = booking.querySelector('[data-booking-service-notice]');
  const submitButtons = [...booking.querySelectorAll('[data-booking-submit]')];
  const requestId = normalizeServiceId(new URL(location.href).searchParams.get('layanan'));
  let firstHydration = true;
  let lastSelectedId = '';
  function renderAvailableServices(preserveId) {
    const items = activeServices(providerId, seed, providerCategory, categorySeed);
    const requested = firstHydration ? requestId : preserveId;
    firstHydration = false;
    fields.service.replaceChildren();
    const matched = items.find(item => item.id === requested);
    const invalidRequest = Boolean(requested && !matched);
    if (items.length === 0 || invalidRequest) {
      const placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = items.length === 0
        ? 'Belum ada layanan aktif' : 'Layanan sebelumnya tidak tersedia — pilih kembali';
      fields.service.append(placeholder);
    }
    items.forEach(item => {
      const option = document.createElement('option');
      option.value = item.id;
      option.textContent = item.name;
      option.dataset.price = String(item.price);
      option.dataset.duration = item.duration;
      option.dataset.unit = item.unit;
      option.dataset.feeNote = item.feeNote || '';
      fields.service.append(option);
    });
    fields.service.value = matched?.id || (requested ? '' : (items[0]?.id || ''));
    lastSelectedId = fields.service.value;
    serviceNotice.textContent = items.length === 0
      ? 'Mitra ini tidak memiliki layanan aktif. Pemesanan baru dinonaktifkan.'
      : invalidRequest ? 'Layanan pada tautan ini sudah dinonaktifkan atau dihapus. Pilih layanan lain yang masih aktif.' : '';
    serviceNotice.classList.toggle('hidden', !!items.length && !invalidRequest);
    submitButtons.forEach(button => { button.disabled = !fields.service.value; button.classList.toggle('opacity-50', !fields.service.value); });
    renderSummary();
  }
  const fields = {
    service: form.querySelector('#booking-service'),
    name: form.querySelector('#booking-name'),
    phone: form.querySelector('#booking-phone'),
    area: form.querySelector('#booking-area'),
    address: form.querySelector('#booking-address'),
    landmark: form.querySelector('#booking-landmark'),
    date: form.querySelector('#booking-date'),
    time: form.querySelector('#booking-time'),
    description: form.querySelector('#booking-description'),
    note: form.querySelector('#booking-note'),
    consent: form.querySelector('#booking-consent'),
  };
  const errors = booking.querySelector('#jw-booking-errors');
  const dateHint = booking.querySelector('#jw-booking-date-hint');
  const allowSunday = booking.dataset.allowSunday === '1';
  const scheduleOptions = { allowSunday };

  /** Memuat slot aktif dan slot terpakai pada tanggal ini dari localStorage. */
  function updateSlotOptions(preserve = true) {
    const oldTime = preserve ? fields.time.value : '';
    const schedule = getSchedule(providerId, scheduleOptions);
    const available = getPartnerAvailability(providerId).available;
    const orders = readOrders();
    fields.time.replaceChildren();
    const blank = document.createElement('option');
    blank.value = '';
    blank.textContent = 'Pilih jam kedatangan';
    fields.time.append(blank);
    const day = fields.date.value;
    if (!day) {
      dateHint.textContent = 'Pilih tanggal terlebih dahulu untuk melihat slot mitra yang tersedia (WITA).';
      renderSummary();
      return;
    }
    const closed = dateClosedReason(day, schedule, { today: todayWita });
    const slots = availableSlots({ providerId, date: day, schedule, orders, available, today: todayWita });
    slots.forEach(slot => {
      const option = document.createElement('option');
      option.value = slot.time;
      option.disabled = !slot.ok;
      option.textContent = `${slot.time.replace(':', '.')} WITA${slot.ok ? '' : ' · Tidak tersedia'}`;
      option.title = slot.reason;
      fields.time.append(option);
    });
    if (slots.some(slot => slot.time === oldTime && slot.ok)) fields.time.value = oldTime;
    else fields.time.value = '';
    const count = slots.filter(slot => slot.ok).length;
    dateHint.textContent = closed || (!available
      ? 'Mitra sedang tidak menerima pesanan baru.'
      : count ? `${count} slot kedatangan tersedia. Pesanan menunggu/aktif mengunci jendela slot 60 menit.`
      : 'Semua slot tanggal ini sudah terpakai atau tidak tersedia. Pilih tanggal lain.');
    dateHint.classList.toggle('text-error', !!closed || !available || !count);
    renderSummary();
  }


  function selectedService() {
    return fields.service.selectedOptions[0];
  }

  function renderSummary() {
    const option = selectedService();
    const price = option?.value ? Number(option.dataset.price) || 0 : 0;
    booking.querySelectorAll('[data-booking-base-price], [data-booking-total]')
      .forEach(node => { node.textContent = option?.value ? idr.format(price) : '—'; });
    booking.querySelectorAll('[data-booking-service-label]')
      .forEach(node => { node.textContent = option?.value ? option.textContent.trim() : 'Belum ada layanan dipilih'; });
    booking.querySelectorAll('[data-booking-duration]')
      .forEach(node => { node.textContent = option?.value ? option.dataset.duration || 'Menyesuaikan' : '—'; });
    booking.querySelectorAll('[data-booking-unit]')
      .forEach(node => { node.textContent = option?.value ? option.dataset.unit || 'per pekerjaan' : '—'; });
    booking.querySelectorAll('[data-booking-fee-note]')
      .forEach(node => { node.textContent = option?.value ? option.dataset.feeNote || 'Biaya tambahan menyesuaikan kondisi pekerjaan.' : ''; });
    const dateValue = fields.date.value;
    const schedule = dateValue && fields.time.value
      ? `${formatDateId(dateValue)}, ${fields.time.value.replace(':', '.')} WITA`
      : 'Jadwal belum dipilih';
    booking.querySelectorAll('[data-booking-schedule-label]')
      .forEach(node => { node.textContent = schedule; });
  }

  function validateDate() {
    fields.date.setCustomValidity('');
    if (!fields.date.value) return;
    if (fields.date.value < fields.date.min || fields.date.value > fields.date.max) {
      fields.date.setCustomValidity('Pilih tanggal dari besok hingga 30 hari ke depan.');
    } else {
      const reason = dateClosedReason(fields.date.value, getSchedule(providerId, scheduleOptions), { today: todayWita });
      if (reason) fields.date.setCustomValidity(reason);
    }
    const invalid = fields.date.validationMessage !== '';
    fields.date.setAttribute('aria-invalid', String(invalid));
    dateHint.classList.toggle('text-error', invalid);
  }

  function validatePhone() {
    fields.phone.setCustomValidity('');
    const clean = fields.phone.value.replace(/[\s()\-]/g, '');
    if (clean && !/^(?:\+62|62|0)8[1-9]\d{7,10}$/.test(clean)) {
      fields.phone.setCustomValidity('Masukkan nomor WhatsApp Indonesia valid (contoh: 081234567890).');
    }
    fields.phone.setAttribute('aria-invalid', String(fields.phone.validationMessage !== ''));
  }

  function validateText() {
    for (const [element, min, label] of [
      [fields.name, 3, 'Nama penerima'],
      [fields.address, 10, 'Alamat'],
      [fields.description, 15, 'Detail pekerjaan'],
    ]) {
      element.setCustomValidity('');
      const value = element.value.trim();
      if (value && value.length < min) {
        element.setCustomValidity(`${label} minimal ${min} karakter.`);
      }
      element.setAttribute('aria-invalid', String(element.validationMessage !== ''));
    }
  }

  const todayWita = localDateMakassar();
  fields.date.min = addUtcDays(todayWita, 1);
  fields.date.max = addUtcDays(todayWita, 30);

  fields.service.addEventListener('change', () => {
    lastSelectedId = fields.service.value;
    submitButtons.forEach(button => { button.disabled = !lastSelectedId; button.classList.toggle('opacity-50', !lastSelectedId); });
    serviceNotice.classList.add('hidden');
    const url = new URL(window.location.href);
    url.searchParams.set('layanan', fields.service.value);
    window.history.replaceState(null, '', url);
    renderSummary();
  });
  fields.date.addEventListener('change', () => { updateSlotOptions(false); validateDate(); });
  fields.time.addEventListener('change', renderSummary);
  fields.phone.addEventListener('input', validatePhone);
  [fields.name, fields.address, fields.description].forEach(input => {
    input.addEventListener('input', validateText);
  });
  fields.description.addEventListener('input', () => {
    booking.querySelector('[data-booking-desc-count]').textContent =
      String(fields.description.value.length);
  });

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    validateDate();
    validatePhone();
    validateText();
    errors.classList.add('hidden');
    errors.textContent = '';

    const service = selectedService();
    // Pilihan dan harga harus diverifikasi ulang dari localStorage tepat sebelum submit.
    // Ini mencegah tautan lama maupun item nonaktif/dihapus diproses oleh UI.
    const fresh = activeServices(providerId, seed, providerCategory, categorySeed).find(item => item.id === service?.value);
    if (!fresh || (service?.value && Number(service.dataset.price) !== fresh.price)) {
      renderAvailableServices(service?.value || lastSelectedId);
      errors.textContent = 'Layanan sudah tidak tersedia atau tarif berubah. Periksa pilihan layanan dan harga terbaru sebelum mengonfirmasi.';
      errors.classList.remove('hidden');
      errors.focus();
      return;
    }
    if (!service || !service.value || !Number.isFinite(Number(service.dataset.price))) {
      errors.textContent = 'Pilihan layanan tidak tersedia. Muat ulang halaman dan pilih kembali.';
      errors.classList.remove('hidden');
      errors.focus();
      return;
    }

    fields.time.setCustomValidity('');
    if (fields.time.value) {
      const slotNow = currentSlotCheck(providerId, fields.date.value, fields.time.value, readOrders(), scheduleOptions);
      if (!slotNow.ok) {
        updateSlotOptions();
        errors.textContent = `${slotNow.reason} Pilih jadwal lain sebelum melanjutkan.`;
        errors.classList.remove('hidden');
        errors.focus();
        return;
      }
    }

    if (!form.checkValidity()) {
      errors.textContent = 'Lengkapi kolom wajib dan perbaiki isian yang belum valid.';
      errors.classList.remove('hidden');
      const firstInvalid = form.querySelector(':invalid');
      firstInvalid?.focus({ preventScroll: false });
      firstInvalid?.reportValidity();
      return;
    }

    // Kode DEMO unik secara lokal, bukan nomor pesanan dari sistem.
    const token = Array.from(crypto.getRandomValues(new Uint8Array(3)))
      .map(byte => byte.toString(16).padStart(2, '0')).join('').toUpperCase();
    const reference = `JW-DEMO-${todayWita.replaceAll('-', '')}-${token}`;
    const payload = {
      version: 1,
      createdAt: Date.now(),
      reference,
      accountId: document.documentElement.dataset.jwServerAuth === '1'
        ? null // identitas session Laravel belum dipetakan ke order demo localStorage
        : currentAccount()?.id || null, // hanya akun DEMO lama dalam browser
      providerId: Number(booking.dataset.providerId),
      provider: booking.dataset.providerName,
      providerUrl: booking.dataset.providerUrl,
      service: service.textContent.trim(),
      serviceId: fields.service.value,
      serviceIndex: /^base-\d+$/.test(fields.service.value) ? Number(fields.service.value.slice(5)) : -1,
      price: Number(service.dataset.price),
      date: fields.date.value,
      time: fields.time.value,
      name: fields.name.value.trim(),
      // Nomor WA asli tidak disimpan maupun dikirim ke server.
      phone: maskPhone(fields.phone.value),
      area: fields.area.value,
      address: fields.address.value.trim(),
      landmark: fields.landmark.value.trim(),
      description: fields.description.value.trim(),
      note: fields.note.value.trim(),
    };

    try {
      sessionStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
      createOrderFromBooking(payload);
      window.location.assign(booking.dataset.successUrl);
    } catch (error) {
      // Hindari ringkasan yang mengklaim berhasil ketika riwayat tidak tersimpan.
      try { sessionStorage.removeItem(STORAGE_KEY); } catch (_) { /* no-op */ }
      errors.textContent = 'Penyimpanan browser dibatasi atau penuh. Aktifkan localStorage dan sessionStorage, lalu coba kembali (gunakan data fiktif).';
      errors.classList.remove('hidden');
      errors.focus();
    }
  });
  renderAvailableServices('');
  updateSlotOptions();
  window.addEventListener('storage', event => {
    if ([SERVICES_KEY, ADMIN_CATEGORIES_KEY].includes(event.key)) renderAvailableServices(fields.service.value);
    if ([SCHEDULE_KEY, PARTNER_SETTINGS_KEY, ORDER_KEY].includes(event.key)) {
      updateSlotOptions(); validateDate();
    }
  });
  window.addEventListener(SERVICES_EVENT, () => renderAvailableServices(fields.service.value));
  window.addEventListener(ADMIN_CATEGORIES_EVENT, () => renderAvailableServices(fields.service.value));
  window.addEventListener(SCHEDULE_EVENT, () => { updateSlotOptions(); validateDate(); });
  window.addEventListener(PARTNER_SETTINGS_EVENT, () => { updateSlotOptions(); validateDate(); });
  window.addEventListener('jasawarga:orders-updated', () => updateSlotOptions());
}

const success = document.querySelector('#jw-booking-success');
if (success) {
  const content = success.querySelector('#jw-success-content');
  const empty = success.querySelector('#jw-success-empty');
  const status = success.querySelector('[data-booking-copy-status]');
  let record = null;
  try {
    record = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || 'null');
  } catch (error) {
    record = null;
  }

  const isValid = record && record.version === 1 &&
    typeof record.createdAt === 'number' &&
    Date.now() - record.createdAt <= LIFETIME_MS &&
    record.createdAt <= Date.now() + 60 * 1000 &&
    record.reference?.startsWith('JW-DEMO-') &&
    typeof record.price === 'number';

  if (!isValid) {
    try { sessionStorage.removeItem(STORAGE_KEY); } catch (error) { /* no-op */ }
    empty.classList.remove('hidden');
  } else {
    // Memigrasikan ringkasan sesi dari versi lama ke daftar lokal (idempotent).
    // Versi baru sudah menyimpan pada submit; fungsi ini tidak menggandakan kode.
    try { createOrderFromBooking(record); } catch (_) {
      status.textContent = 'Riwayat lokal tidak dapat disimpan di browser ini.';
    }
    content.classList.remove('hidden');
    const values = {
      reference: record.reference,
      provider: record.provider,
      service: record.service,
      schedule: `${formatDateId(record.date)}, ${record.time.replace(':', '.')} WITA`,
      name: record.name,
      phone: record.phone,
      area: `${record.area}, Makassar`,
      address: [record.address, record.landmark ? `Patokan: ${record.landmark}` : ''].filter(Boolean).join(' · '),
      description: [record.description, record.note ? `Catatan: ${record.note}` : ''].filter(Boolean).join('\n'),
      total: idr.format(record.price),
    };

    // textContent menghindari interpretasi HTML dari input bebas pengguna.
    for (const [key, value] of Object.entries(values)) {
      const element = success.querySelector(`[data-booking-result="${key}"]`);
      if (element) element.textContent = String(value ?? '—');
    }

    success.querySelector('[data-booking-copy]').addEventListener('click', async () => {
      const message = [
        'JasaWarga - RINGKASAN SIMULASI (BUKAN PESANAN NYATA)',
        `Kode: ${values.reference}`,
        `Mitra: ${values.provider}`,
        `Layanan: ${values.service}`,
        `Jadwal: ${values.schedule}`,
        `Area: ${values.area}`,
        `Estimasi: ${values.total}`,
      ].join('\n');
      try {
        await navigator.clipboard.writeText(message);
        status.textContent = 'Ringkasan simulasi disalin.';
      } catch (error) {
        status.textContent = 'Tidak dapat menyalin otomatis. Pilih teks pada halaman untuk disalin.';
      }
    });

    success.querySelector('[data-booking-clear]').addEventListener('click', () => {
      try { sessionStorage.removeItem(STORAGE_KEY); } catch (error) { /* no-op */ }
      content.classList.add('hidden');
      empty.classList.remove('hidden');
    });
  }
}
