import { readAdminFeedback, reviewStatus, ADMIN_FEEDBACK_KEY, ADMIN_FEEDBACK_EVENT } from './admin-feedback-store.js';
import { getPartnerReply, PARTNER_REPLIES_KEY, PARTNER_REPLIES_EVENT } from './partner-review-replies-store.js';
import { getOrder } from './orders-store.js';
import {
  REVIEW_KEY, REVIEW_EVENT, readReviews, getReview,
  reviewsForProvider, saveReview, clearReviews,
} from './reviews-store.js';

const formatDate = stamp => new Intl.DateTimeFormat('id-ID', {
  day: 'numeric', month: 'short', year: 'numeric',
  hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Makassar',
}).format(new Date(stamp)) + ' WITA';
const aspectNames = { quality: 'Kualitas', punctuality: 'Ketepatan', friendliness: 'Keramahan' };

function addPhotoThumbnails(container, photos) {
  container.replaceChildren();
  photos.forEach((src, index) => {
    const img = document.createElement('img');
    img.src = src;
    img.alt = `Foto ilustratif ${index + 1}`;
    img.loading = 'lazy';
    img.className = 'h-20 w-20 rounded-lg border border-outline-variant/40 object-cover sm:h-24 sm:w-24';
    container.appendChild(img);
  });
}

function addAspects(container, details) {
  container.replaceChildren();
  Object.entries(aspectNames).forEach(([key, name]) => {
    const badge = document.createElement('span');
    badge.className = 'rounded-full bg-surface-container-low px-3 py-1 text-[11px] font-semibold text-on-surface-variant';
    badge.textContent = `${name}: ${details[key]}/5`;
    container.appendChild(badge);
  });
}

/** Ubah gambar lokal menjadi JPEG kecil sehingga tidak menyimpan file asli. */
function resizedJpeg(file) {
  return new Promise((resolve, reject) => {
    const objectUrl = URL.createObjectURL(file);
    const image = new Image();
    image.onload = () => {
      try {
        const maxSide = 600;
        const scale = Math.min(1, maxSide / Math.max(image.naturalWidth, image.naturalHeight));
        const canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
        canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
        const ctx = canvas.getContext('2d');
        if (!ctx) throw new Error('Gagal menyiapkan pratinjau gambar.');
        ctx.drawImage(image, 0, 0, canvas.width, canvas.height);
        let encoded = canvas.toDataURL('image/jpeg', 0.66);
        if (encoded.length > 180000) encoded = canvas.toDataURL('image/jpeg', 0.42);
        if (encoded.length > 180000) {
          canvas.width = Math.max(1, Math.round(canvas.width * 0.6));
          canvas.height = Math.max(1, Math.round(canvas.height * 0.6));
          ctx.drawImage(image, 0, 0, canvas.width, canvas.height);
          encoded = canvas.toDataURL('image/jpeg', 0.5);
        }
        if (encoded.length > 180000 || !encoded.startsWith('data:image/jpeg;base64,')) {
          throw new Error('Foto terlalu kompleks untuk disimpan. Gunakan gambar lain yang lebih sederhana.');
        }
        resolve(encoded);
      } catch (error) { reject(error); }
      finally { URL.revokeObjectURL(objectUrl); }
    };
    image.onerror = () => { URL.revokeObjectURL(objectUrl); reject(new Error('File gambar tidak dapat dibaca.')); };
    image.src = objectUrl;
  });
}

const createRoot = document.querySelector('#jw-review-create');
if (createRoot) {
  const reference = createRoot.dataset.reference;
  const blocked = createRoot.querySelector('[data-review-blocked]');
  const formContent = createRoot.querySelector('[data-review-form-content]');
  const form = createRoot.querySelector('#jw-review-form');
  const comment = form.querySelector('#jw-review-comment');
  const photoInput = form.querySelector('#jw-review-photos');
  const photoPreview = form.querySelector('[data-review-photo-preview]');
  const submit = form.querySelector('[data-review-submit]');
  const errorBox = form.querySelector('[data-review-error]');
  let photos = [];
  let processing = false;

  function showError(message) {
    errorBox.textContent = message;
    errorBox.classList.remove('hidden');
    errorBox.focus();
  }
  function block(title, explanation, target) {
    formContent.classList.add('hidden');
    blocked.classList.remove('hidden');
    blocked.querySelector('[data-review-blocked-title]').textContent = title;
    blocked.querySelector('[data-review-blocked-message]').textContent = explanation;
    const link = blocked.querySelector('[data-review-blocked-link]');
    if (target) link.href = target;
    link.textContent = target?.includes('ulasan-saya') ? 'Lihat Ulasan Saya' : 'Lihat Pesanan Saya';
  }
  function prepare() {
    const order = getOrder(reference);
    if (!order) {
      block('Pesanan tidak ditemukan', 'Data pesanan ini tidak tersedia di browser. Buat atau coba pesanan demo melalui Pesanan Saya.');
    } else if (getReview(reference)) {
      block('Pesanan sudah diulas', 'Satu pesanan hanya boleh menerima satu ulasan. Penilaian Anda dapat dilihat di Riwayat Ulasan Saya.', `${createRoot.dataset.reviewsUrl}?ref=${encodeURIComponent(reference)}`);
    } else if (order.status !== 'completed') {
      block('Pesanan belum selesai', 'Ulasan hanya dapat dibuat setelah status simulasi mencapai Selesai.', `${createRoot.dataset.orderBase}/${encodeURIComponent(reference)}`);
    } else {
      blocked.classList.add('hidden');
      formContent.classList.remove('hidden');
      createRoot.querySelector('[data-review-order-reference]').textContent = order.reference;
      createRoot.querySelector('[data-review-order-provider]').textContent = order.provider;
      createRoot.querySelector('[data-review-order-service]').textContent = order.service;
    }
  }

  // Radio native mendukung keyboard; warna bintang mengikuti pilihan yang dipilih.
  form.querySelectorAll('[data-review-rating]').forEach(group => {
    group.addEventListener('change', () => {
      const selected = Number(group.querySelector('input:checked')?.value || 0);
      group.querySelectorAll('[data-review-star]').forEach(star => {
        const enabled = Number(star.dataset.reviewStar) <= selected;
        star.classList.toggle('text-tertiary', enabled);
        star.classList.toggle('text-outline-variant', !enabled);
        star.style.fontVariationSettings = enabled ? "'FILL' 1" : "'FILL' 0";
      });
      const label = group.querySelector('[data-review-rating-label]');
      label.textContent = selected ? `${selected}/5` : 'Pilih nilai';
      label.classList.toggle('text-primary', selected > 0);
    });
  });

  comment.addEventListener('input', () => {
    createRoot.querySelector('[data-review-count]').textContent = String(comment.value.length);
  });

  photoInput.addEventListener('change', async () => {
    errorBox.classList.add('hidden');
    photos = [];
    photoPreview.replaceChildren();
    const files = [...photoInput.files];
    if (!files.length) return;
    if (files.length > 2) { showError('Pilih maksimal dua foto.'); photoInput.value = ''; return; }
    if (files.some(file => !['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024)) {
      showError('Foto harus JPG, PNG atau WebP dengan ukuran maksimal 2 MB per file.');
      photoInput.value = ''; return;
    }
    processing = true;
    submit.disabled = true;
    try {
      photos = await Promise.all(files.map(resizedJpeg));
      addPhotoThumbnails(photoPreview, photos);
    } catch (error) {
      photos = [];
      showError(error.message || 'Tidak dapat menyiapkan foto.');
      photoInput.value = '';
    } finally { processing = false; submit.disabled = false; }
  });

  form.addEventListener('submit', event => {
    event.preventDefault();
    if (processing) return;
    errorBox.classList.add('hidden');
    const selected = key => Number(form.querySelector(`[name="${key}"]:checked`)?.value || 0);
    if (![selected('overall'), selected('quality'), selected('punctuality'), selected('friendliness')]
      .every(score => Number.isInteger(score) && score >= 1 && score <= 5)) {
      showError('Pilih rating 1–5 bintang untuk semua aspek.'); return;
    }
    if (comment.value.trim().length < 20 || comment.value.trim().length > 800) {
      showError('Ulasan tertulis harus berisi 20–800 karakter.'); comment.focus(); return;
    }
    submit.disabled = true;
    try {
      saveReview({
        reference, rating: selected('overall'),
        details: { quality: selected('quality'), punctuality: selected('punctuality'), friendliness: selected('friendliness') },
        comment: comment.value.trim(), photos,
      });
      const url = new URL(createRoot.dataset.reviewsUrl, window.location.origin);
      url.searchParams.set('ref', reference);
      url.searchParams.set('tersimpan', '1');
      window.location.assign(url.toString());
    } catch (error) {
      showError(error.message || 'Tidak dapat menyimpan ulasan. Pastikan localStorage diizinkan dan ruang penyimpanan cukup.');
      submit.disabled = false;
    }
  });
  window.addEventListener('storage', event => {
    if ([REVIEW_KEY, 'jasawarga:prototype-orders:v1'].includes(event.key)) prepare();
  });
  prepare();
}

const historyRoot = document.querySelector('#jw-review-history');
if (historyRoot) {
  const list = historyRoot.querySelector('[data-review-history-list]');
  const empty = historyRoot.querySelector('[data-review-history-empty]');
  const template = historyRoot.querySelector('#jw-review-history-item');
  const dialog = historyRoot.querySelector('#jw-review-clear-dialog');
  const notice = historyRoot.querySelector('[data-review-history-notice]');
  const params = new URLSearchParams(window.location.search);

  function render() {
    const allReviews = readReviews();
    const focused = params.get('ref');
    const sorted = [...allReviews].sort((a, b) => Number(b.reference === focused) - Number(a.reference === focused));
    list.replaceChildren();
    const fragment = document.createDocumentFragment();
    sorted.forEach(review => {
      const item = template.content.firstElementChild.cloneNode(true);
      item.dataset.reference = review.reference;
      const set = (selector, text) => { item.querySelector(selector).textContent = String(text); };
      set('[data-review-item-provider]', review.provider);
      set('[data-review-item-service]', review.service);
      set('[data-review-item-reference]', review.reference);
      set('[data-review-item-rating]', `${review.rating}/5`);
      set('[data-review-item-date]', formatDate(review.createdAt));
      set('[data-review-item-comment]', review.comment);
      addAspects(item.querySelector('[data-review-item-aspects]'), review.details);
      addPhotoThumbnails(item.querySelector('[data-review-item-photos]'), review.photos);
      item.querySelector('[data-review-item-order]').href = `${historyRoot.dataset.ordersBase}/${encodeURIComponent(review.reference)}`;
      item.querySelector('[data-review-item-provider-link]').href = `${historyRoot.dataset.providerBase}/${review.providerId}#ulasan`;
      if (review.reference === focused) item.classList.add('ring-2', 'ring-primary/50');
      fragment.appendChild(item);
    });
    list.appendChild(fragment);
    empty.classList.toggle('hidden', allReviews.length !== 0);
    historyRoot.querySelector('[data-review-history-count]').textContent = String(allReviews.length);
    historyRoot.querySelector('[data-review-history-average]').textContent = allReviews.length
      ? (allReviews.reduce((sum, review) => sum + review.rating, 0) / allReviews.length).toFixed(1)
      : '—';
    if (params.get('tersimpan') === '1' && focused && allReviews.some(review => review.reference === focused)) {
      notice.textContent = 'Ulasan simulasi berhasil disimpan di browser ini.';
      notice.classList.remove('hidden');
    }
  }
  historyRoot.querySelector('[data-review-clear]').addEventListener('click', () => dialog.showModal());
  historyRoot.querySelector('[data-review-cancel-clear]').addEventListener('click', () => dialog.close());
  historyRoot.querySelector('[data-review-confirm-clear]').addEventListener('click', () => {
    try {
      clearReviews(); dialog.close();
      params.delete('tersimpan'); params.delete('ref');
      window.history.replaceState(null, '', window.location.pathname);
      notice.textContent = 'Ulasan lokal telah dihapus.';
      notice.classList.remove('hidden'); render();
    } catch (_) {
      dialog.close(); notice.textContent = 'Penyimpanan browser tidak dapat dihapus.'; notice.classList.remove('hidden');
    }
  });
  dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
  window.addEventListener('storage', event => { if (event.key === REVIEW_KEY) render(); });
  window.addEventListener(REVIEW_EVENT, render);
  render();
}

// Ulasan lokal pada profil mitra: dipisahkan dari rating dummy bawaan.
const providerRoot = document.querySelector('#jw-provider-detail');
if (providerRoot) {
  const mount = providerRoot.querySelector('[data-provider-local-review-list]');
  const empty = providerRoot.querySelector('[data-provider-local-review-empty]');
  const summary = providerRoot.querySelector('[data-provider-local-review-summary]');
  if (mount && empty && summary) {
    function renderProviderReviews() {
      const feedback = readAdminFeedback();
      const reviews = reviewsForProvider(Number(providerRoot.dataset.providerId)).filter(review => reviewStatus(review.reference,feedback) === 'visible');
      mount.replaceChildren();
      reviews.forEach(review => {
        const card = document.createElement('article');
        card.className = 'border-b border-outline-variant/30 py-4 last:border-0';
        const top = document.createElement('div');
        top.className = 'flex flex-wrap items-start justify-between gap-3';
        const title = document.createElement('div');
        const name = document.createElement('p');
        name.className = 'text-sm font-bold'; name.textContent = 'Warga JasaWarga · Ulasan lokal';
        const date = document.createElement('p'); date.className = 'mt-1 text-[11px] text-outline'; date.textContent = `${formatDate(review.createdAt)} · Simulasi`;
        title.append(name, date);
        const stars = document.createElement('span');
        stars.className = 'text-sm tracking-wider text-tertiary';
        stars.textContent = '★'.repeat(review.rating) + '☆'.repeat(5 - review.rating);
        stars.setAttribute('aria-label', `${review.rating} dari 5 bintang`);
        top.append(title, stars);
        const comment = document.createElement('p');
        comment.className = 'mt-3 whitespace-pre-wrap break-words text-sm leading-relaxed text-on-surface-variant';
        comment.textContent = review.comment;
        const aspects = document.createElement('div'); aspects.className = 'mt-3 flex flex-wrap gap-2';
        addAspects(aspects, review.details);
        const photos = document.createElement('div'); photos.className = 'mt-3 flex flex-wrap gap-2';
        addPhotoThumbnails(photos, review.photos);
        card.append(top, comment, aspects, photos);
        // Balasan Mitra disimpan terpisah, ulasan Warga tidak pernah dimodifikasi.
        const reply = getPartnerReply(review.reference, Number(providerRoot.dataset.providerId));
        if (reply) {
          const response = document.createElement('div');
          response.className = 'mt-4 rounded-xl border-l-[3px] border-primary bg-primary-fixed/35 px-4 py-3';
          const replyHead = document.createElement('div');
          replyHead.className = 'flex flex-wrap items-center justify-between gap-2';
          const label = document.createElement('strong');
          label.className = 'text-xs text-primary'; label.textContent = 'Balasan Mitra · Demo';
          const when = document.createElement('span');
          when.className = 'text-[10px] text-outline';
          when.textContent = `Diperbarui ${formatDate(reply.updatedAt)}`;
          replyHead.append(label, when);
          const replyMessage = document.createElement('p');
          replyMessage.className = 'mt-2 whitespace-pre-wrap break-words text-sm leading-relaxed text-on-surface-variant';
          replyMessage.textContent = reply.text; // textContent mencegah HTML / skrip dieksekusi.
          response.append(replyHead, replyMessage);
          card.appendChild(response);
        }
        mount.appendChild(card);
      });
      empty.classList.toggle('hidden', reviews.length !== 0);
      summary.textContent = reviews.length
        ? `${reviews.length} ulasan lokal · rata-rata ${(reviews.reduce((n, r) => n + r.rating, 0) / reviews.length).toFixed(1)}/5`
        : 'Belum ada ulasan lokal';
    }
    window.addEventListener('storage', event => { if ([REVIEW_KEY, PARTNER_REPLIES_KEY, ADMIN_FEEDBACK_KEY].includes(event.key)) renderProviderReviews(); });
    window.addEventListener(REVIEW_EVENT, renderProviderReviews);
    window.addEventListener(PARTNER_REPLIES_EVENT, renderProviderReviews);
    window.addEventListener(ADMIN_FEEDBACK_EVENT, renderProviderReviews);
    renderProviderReviews();
  }
}
