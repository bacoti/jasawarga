/** Dashboard Ulasan Mitra JasaWarga — antarmuka browser demo. */
import {
  REVIEW_KEY, REVIEW_EVENT, reviewsForProvider, getReview, saveReview,
} from './reviews-store.js';
import {
  PARTNER_REPLIES_KEY, PARTNER_REPLIES_EVENT,
  getPartnerReply, savePartnerReply, deletePartnerReply,
} from './partner-review-replies-store.js';
import { readAdminFeedback, reviewStatus, ADMIN_FEEDBACK_KEY, ADMIN_FEEDBACK_EVENT } from './admin-feedback-store.js';
import { createOrderFromBooking, changeOrderStatus, ORDER_STEPS } from './orders-store.js';

const root = document.querySelector('#jw-partner-reviews');

if (root) {
  const providerId = Number(root.dataset.partnerId);
  const select = selector => root.querySelector(selector);
  const dateFormatter = new Intl.DateTimeFormat('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
    timeZone: 'Asia/Makassar',
  });
  const dateText = stamp => `${dateFormatter.format(new Date(stamp))} WITA`;
  const list = select('[data-pr-list]');
  const template = select('#jw-pr-card-template');
  const replyDialog = select('#jw-pr-reply-dialog');
  const deleteDialog = select('#jw-pr-delete-dialog');
  const photoDialog = select('#jw-pr-photo-dialog');
  const form = select('#jw-pr-reply-form');
  const input = select('[data-pr-reply-input]');
  const error = select('[data-pr-reply-error]');
  const toast = select('[data-pr-toast]');
  let currentReference = null;
  let deletingReference = null;
  let toastTimeout;

  const filters = {
    search: select('[data-pr-search]'), stars: select('[data-pr-stars]'),
    period: select('[data-pr-period]'), response: select('[data-pr-response]'),
    service: select('[data-pr-service]'), sort: select('[data-pr-sort]'),
  };

  function notify(message) {
    toast.textContent = message;
    toast.classList.remove('hidden');
    window.clearTimeout(toastTimeout);
    toastTimeout = window.setTimeout(() => toast.classList.add('hidden'), 3600);
  }

  function ratingText(value) { return value.toFixed(1).replace('.', ','); }
  const average = (items, get) => items.length
    ? items.reduce((sum, item) => sum + get(item), 0) / items.length : null;

  function fillSelectServices(reviews) {
    const selected = filters.service.value;
    const names = [...new Set(reviews.map(item => item.service))].sort((a, b) => a.localeCompare(b, 'id-ID'));
    filters.service.replaceChildren();
    const all = new Option('Semua layanan', 'all');
    filters.service.append(all);
    names.forEach(name => filters.service.append(new Option(name, name)));
    filters.service.value = names.includes(selected) ? selected : 'all';
  }

  function renderSummary(reviews) {
    const total = reviews.length;
    const replied = reviews.filter(item => getPartnerReply(item.reference, providerId)).length;
    const avg = average(reviews, item => item.rating);
    select('[data-pr-average]').textContent = avg === null ? '—' : ratingText(avg);
    select('[data-pr-total]').textContent = String(total);
    select('[data-pr-total-inline]').textContent = String(total);
    select('[data-pr-replied]').textContent = String(replied);
    select('[data-pr-unanswered]').textContent = String(total - replied);
    ['quality', 'punctuality', 'friendliness'].forEach(key => {
      const result = average(reviews, item => item.details[key]);
      select(`[data-pr-aspect="${key}"]`).textContent = result === null ? '—' : `${ratingText(result)} / 5`;
    });
    for (let star = 1; star <= 5; star += 1) {
      const row = select(`[data-pr-star-row="${star}"]`);
      const amount = reviews.filter(item => item.rating === star).length;
      row.querySelector('[data-pr-star-count]').textContent = String(amount);
      row.querySelector('[data-pr-star-bar]').style.width = `${total ? 100 * amount / total : 0}%`;
      row.setAttribute('aria-label', `${star} bintang, ${amount} ulasan`);
    }
  }

  function filteredReviews(reviews) {
    const q = filters.search.value.trim().toLocaleLowerCase('id-ID');
    const stars = filters.stars.value;
    const period = Number(filters.period.value);
    const response = filters.response.value;
    const service = filters.service.value;
    const now = Date.now();
    return reviews.filter(item => {
      const replied = !!getPartnerReply(item.reference, providerId);
      return (!q || `${item.comment} ${item.service} ${item.reference}`.toLocaleLowerCase('id-ID').includes(q)) &&
        (stars === 'all' || item.rating === Number(stars)) &&
        (!period || item.createdAt >= now - period * 86400000) &&
        (response === 'all' || (response === 'answered' ? replied : !replied)) &&
        (service === 'all' || item.service === service);
    }).sort((a, b) => {
      if (filters.sort.value === 'oldest') return a.createdAt - b.createdAt;
      if (filters.sort.value === 'high') return b.rating - a.rating || b.createdAt - a.createdAt;
      if (filters.sort.value === 'low') return a.rating - b.rating || b.createdAt - a.createdAt;
      return b.createdAt - a.createdAt;
    });
  }

  function renderCard(review) {
    const card = template.content.firstElementChild.cloneNode(true);
    card.dataset.reference = review.reference;
    const text = (selector, value) => { card.querySelector(selector).textContent = String(value); };
    text('[data-pr-card-meta]', `${review.service} · ${review.reference} · ${dateText(review.createdAt)}`);
    text('[data-pr-card-stars]', '★'.repeat(review.rating) + '☆'.repeat(5 - review.rating));
    text('[data-pr-card-rating]', `${review.rating}/5`);
    text('[data-pr-card-comment]', review.comment);
    if (reviewStatus(review.reference, readAdminFeedback()) === 'hidden') {
      const hint = document.createElement('p');
      hint.className = 'mt-2 rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800';
      hint.textContent = 'Ulasan disembunyikan pada profil publik oleh Admin Demo; komentar asli masih tersedia di sini.';
      card.querySelector('[data-pr-card-comment]').after(hint);
    }

    const details = card.querySelector('[data-pr-card-aspects]');
    const aspects = [['quality','Kualitas'], ['punctuality','Ketepatan'], ['friendliness','Keramahan']];
    for (const [key, label] of aspects) {
      const badge = document.createElement('span');
      badge.className = 'rounded-full bg-surface-container-low px-3 py-1 text-[11px] font-semibold text-on-surface-variant';
      badge.textContent = `${label} ${review.details[key]}/5`;
      details.append(badge);
    }

    const photos = card.querySelector('[data-pr-card-photos]');
    review.photos.forEach((src, index) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'overflow-hidden rounded-xl border border-outline-variant/50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary';
      btn.dataset.prPhoto = String(index);
      btn.setAttribute('aria-label', `Perbesar foto ulasan ${index + 1}`);
      const img = document.createElement('img');
      img.src = src; img.loading = 'lazy';
      img.alt = `Foto ulasan lokal ${index + 1}`;
      img.className = 'h-20 w-20 object-cover sm:h-24 sm:w-24';
      btn.append(img); photos.append(btn);
    });

    const reply = getPartnerReply(review.reference, providerId);
    if (reply) {
      card.querySelector('[data-pr-card-reply-box]').classList.remove('hidden');
      text('[data-pr-card-reply-text]', reply.text);
      text('[data-pr-card-reply-date]', `Diperbarui ${dateText(reply.updatedAt)}`);
      text('[data-pr-action-label]', 'Edit balasan');
      card.querySelector('[data-pr-delete]').classList.remove('hidden');
    }
    return card;
  }

  function render() {
    const reviews = reviewsForProvider(providerId);
    fillSelectServices(reviews);
    renderSummary(reviews);
    const results = filteredReviews(reviews);
    list.replaceChildren();
    const fragment = document.createDocumentFragment();
    results.forEach(review => fragment.append(renderCard(review)));
    list.append(fragment);
    select('[data-pr-visible]').textContent = String(results.length);
    select('[data-pr-empty]').classList.toggle('hidden', results.length !== 0);
    const emptyTitle = select('[data-pr-empty-title]');
    const emptyDesc = select('[data-pr-empty-desc]');
    emptyTitle.textContent = reviews.length ? 'Tidak ada ulasan yang cocok' : 'Belum ada ulasan pelanggan';
    emptyDesc.textContent = reviews.length
      ? 'Coba ubah kata kunci atau reset seluruh filter.'
      : 'Selesaikan pesanan dan tulis ulasan melalui halaman Warga. Untuk mencoba dashboard, tambahkan tiga ulasan fiktif.';
  }

  function showReply(reference) {
    const review = getReview(reference);
    if (!review || review.providerId !== providerId) { notify('Ulasan tidak tersedia.'); render(); return; }
    currentReference = reference;
    const existing = getPartnerReply(reference, providerId);
    select('[data-pr-dialog-title]').textContent = existing ? 'Edit balasan Mitra' : 'Balas ulasan pelanggan';
    select('[data-pr-dialog-hint]').textContent = `${review.service} · Rating ${review.rating}/5`;
    input.value = existing?.text || '';
    select('[data-pr-reply-count]').textContent = String(input.value.length);
    error.classList.add('hidden');
    replyDialog.showModal();
    input.focus();
  }

  function showDelete(reference) {
    const existing = getPartnerReply(reference, providerId);
    if (!existing) { notify('Balasan sudah tidak ada.'); render(); return; }
    deletingReference = reference;
    deleteDialog.showModal();
  }

  function seedDemoReviews() {
    const data = [
      { rating: 5, service: 'Cuci AC standar', comment: 'Teknisi datang sesuai jadwal, pekerjaan rapi, dan AC kembali dingin. Sangat membantu.', details: {quality:5,punctuality:5,friendliness:5}},
      { rating: 4, service: 'Servis AC rumah', comment: 'Penjelasan kerusakan mudah dipahami, hasil perbaikan bagus. Semoga respons berikutnya bisa lebih cepat.', details: {quality:5,punctuality:3,friendliness:4}},
      { rating: 3, service: 'Pemeriksaan AC', comment: 'Pekerjaan cukup baik, tetapi waktu kedatangan melewati perkiraan. Mohon jadwal lebih diperhatikan.', details: {quality:4,punctuality:2,friendliness:4}},
    ];
    const now = Date.now();
    const dateKey = new Intl.DateTimeFormat('en-CA', {timeZone:'Asia/Makassar',year:'numeric',month:'2-digit',day:'2-digit'}).format(now).replaceAll('-', '');
    const day = new Date(now + 2 * 86400000);
    const bookingDate = [day.getFullYear(), String(day.getMonth() + 1).padStart(2,'0'),String(day.getDate()).padStart(2,'0')].join('-');
    let added = 0;
    data.forEach((sample, index) => {
      // Kode acak dan seluruh data fiktif, tidak berisi data orang sungguhan.
      const random = Array.from(crypto.getRandomValues(new Uint8Array(3)), value => value.toString(16).padStart(2,'0')).join('').toUpperCase();
      const reference = `JW-DEMO-${dateKey}-${random}`;
      createOrderFromBooking({
        reference, providerId, provider: root.dataset.partnerName || 'Mitra demo', service: sample.service,
        price: 75000 + index * 15000, date: bookingDate, time: '11:00',
        area: 'Panakkukang', address: 'Alamat demo fiktif, Panakkukang, Makassar',
        description: 'Pekerjaan contoh untuk pengujian ulasan Mitra.',
        createdAt: now - index * 86400000,
      });
      for (const stage of ORDER_STEPS.slice(1)) changeOrderStatus(reference, stage);
      saveReview({ reference, rating: sample.rating, details: sample.details, comment: sample.comment, photos: [] });
      added += 1;
    });
    return added;
  }

  Object.values(filters).forEach(el => el.addEventListener(el.tagName === 'INPUT' ? 'input' : 'change', render));
  select('[data-pr-reset]').addEventListener('click', () => {
    filters.search.value = '';
    filters.stars.value = 'all'; filters.period.value = 'all';
    filters.response.value = 'all'; filters.service.value = 'all';
    filters.sort.value = 'newest';
    render();
  });
  select('[data-pr-seed]').addEventListener('click', () => {
    try { const added = seedDemoReviews(); render(); notify(`${added} ulasan dan pesanan fiktif berhasil ditambahkan.`); }
    catch (error) { render(); notify(error.message || 'Tidak dapat menyimpan ulasan contoh.'); }
  });
  list.addEventListener('click', event => {
    const button = event.target.closest('button');
    const card = button?.closest('[data-pr-card]');
    if (!card) return;
    if (button.matches('[data-pr-reply]')) showReply(card.dataset.reference);
    else if (button.matches('[data-pr-delete]')) showDelete(card.dataset.reference);
    else if (button.matches('[data-pr-photo]')) {
      const review = getReview(card.dataset.reference);
      const photo = review?.photos[Number(button.dataset.prPhoto)];
      if (photo) { select('[data-pr-photo-large]').src = photo; photoDialog.showModal(); }
    }
  });
  input.addEventListener('input', () => {
    select('[data-pr-reply-count]').textContent = String(input.value.length);
    error.classList.add('hidden');
  });
  form.addEventListener('submit', event => {
    event.preventDefault();
    try {
      if (!currentReference) throw new Error('Pilih ulasan terlebih dahulu.');
      const hadReply = !!getPartnerReply(currentReference, providerId);
      savePartnerReply(providerId, currentReference, input.value);
      replyDialog.close(); currentReference = null; render();
      notify(hadReply ? 'Balasan Mitra diperbarui di browser ini.' : 'Balasan Mitra disimpan di browser ini.');
    } catch (reason) { error.textContent = reason.message || 'Tidak dapat menyimpan balasan.'; error.classList.remove('hidden'); }
  });
  select('[data-pr-confirm-delete]').addEventListener('click', () => {
    try {
      const deleted = deletePartnerReply(providerId, deletingReference);
      deleteDialog.close(); deletingReference = null; render();
      notify(deleted ? 'Balasan telah dihapus; ulasan Warga tetap ada.' : 'Balasan tidak ditemukan.');
    } catch (reason) { notify(reason.message || 'Tidak dapat menghapus balasan.'); }
  });
  root.querySelectorAll('[data-pr-close-reply]').forEach(button => button.addEventListener('click', () => replyDialog.close()));
  select('[data-pr-close-delete]').addEventListener('click', () => deleteDialog.close());
  select('[data-pr-close-photo]').addEventListener('click', () => photoDialog.close());
  for (const dialog of [replyDialog, deleteDialog, photoDialog]) {
    dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
  }
  for (const event of [REVIEW_EVENT, PARTNER_REPLIES_EVENT, ADMIN_FEEDBACK_EVENT]) window.addEventListener(event, render);
  window.addEventListener('storage', event => {
    if ([REVIEW_KEY, PARTNER_REPLIES_KEY, ADMIN_FEEDBACK_KEY].includes(event.key)) render();
  });
  render();
}
