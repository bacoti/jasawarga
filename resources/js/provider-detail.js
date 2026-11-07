import { PROFILE_KEY, PROFILE_EVENT, hasPartnerProfileOverride, readPartnerProfile, isSafeProfileImage } from './partner-profile-store.js';
import { activeServices, normalizeServiceId, SERVICES_KEY, SERVICES_EVENT } from './services-store.js';
import { ADMIN_CATEGORIES_KEY, ADMIN_CATEGORIES_EVENT } from './admin-category-store.js';
import { readPartnerAccount, PARTNER_ACCOUNT_KEY, PARTNER_ACCOUNT_EVENT } from './partner-account-store.js';
import { readPartnerModeration, partnerVerification, ADMIN_PARTNER_KEY, ADMIN_MANAGEMENT_EVENT } from './admin-management-store.js';

/** Profil publik: harga/layanan selalu mengikuti etalase localStorage mitra ini. */
const detail = document.querySelector('#jw-provider-detail');
if (detail) {
  const providerId = Number(detail.dataset.providerId);
  const providerCategory = detail.dataset.providerCategory;
  window.addEventListener(ADMIN_MANAGEMENT_EVENT, applyVerification);
  window.addEventListener('storage', event => { if (event.key === ADMIN_PARTNER_KEY || event.key === null) applyVerification(); });
  const seed = JSON.parse(detail.querySelector('[data-provider-services-seed]').textContent);
  const categorySeed = JSON.parse(detail.querySelector('[data-provider-categories-seed]').textContent);
  const list = detail.querySelector('[data-provider-service-list]');
  const empty = detail.querySelector('[data-provider-service-empty]');
  const count = detail.querySelector('[data-provider-service-count]');
  const idr = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
  const publicSeed = JSON.parse(detail.querySelector('[data-provider-profile-seed]').textContent);
  function applyVerification() {
    const badge = detail.querySelector('[data-public-verification]');
    if (!badge) return;
    const seedVerified = badge.dataset.catalogVerified === '1';
    const status = partnerVerification({ id: providerId, verified: seedVerified }, readPartnerModeration());
    badge.textContent = ({ verified: 'Terverifikasi (data demo)', pending: 'Belum diverifikasi (data demo)', rejected: 'Verifikasi ditolak (data demo)' })[status];
    badge.className = `inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold ${status === 'verified' ? 'bg-secondary-fixed text-on-secondary-fixed' : status === 'rejected' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-800'}`;
  }
  function applyPrivacy() {
    const privacy = readPartnerAccount(providerId).privacy;
    const phoneEl = detail.querySelector('[data-public-contact-wrap]');
    const phoneValue = detail.querySelector('[data-public-contact]');
    phoneEl?.classList.toggle('hidden', !privacy.showBusinessPhone || !phoneValue?.textContent?.trim());
    detail.querySelector('[data-public-coverage-wrap]')?.classList.toggle('hidden', !privacy.showCoverage);
  }
  function hydratePublicProfile() {
    if (!hasPartnerProfileOverride(providerId)) {
      applyPrivacy(); // fallback PHP tidak boleh melewati preferensi privasi
      return;
    }
    const state = readPartnerProfile(providerId, publicSeed.provider, publicSeed.profile);
    detail.querySelectorAll('[data-public-name]').forEach(el => { el.textContent = state.name; });
    detail.dataset.providerName = state.name;
    detail.querySelectorAll('[data-public-primary-area]').forEach(el => { el.textContent = state.coverage[0]; });
    detail.querySelector('[data-public-headline]').textContent = state.headline;
    detail.querySelector('[data-public-experience]').textContent = `${state.experienceYears} tahun`;
    detail.querySelector('[data-public-about]').textContent = state.about;
    const contact = detail.querySelector('[data-public-contact-wrap]');
    contact.classList.toggle('hidden', !state.phone);
    contact.querySelector('[data-public-contact]').textContent = state.phone;
    const skills = detail.querySelector('[data-public-specialties]');
    skills.replaceChildren(...state.specialties.map(item => {
      const tag = document.createElement('span');
      tag.className = 'rounded-full bg-primary-fixed px-2.5 py-1 text-xs font-semibold text-primary';
      tag.textContent = item;
      return tag;
    }));
    const areas = detail.querySelector('[data-public-coverage]');
    areas.replaceChildren(...state.coverage.map(area => {
      const tag = document.createElement('span');
      tag.className = 'rounded-full bg-surface-container-low px-2.5 py-1 text-xs text-on-surface-variant';
      tag.textContent = area;
      return tag;
    }));
    for (const [key, selector, fallback] of [
      ['avatarDataUrl', '[data-public-avatar-img]', '[data-public-avatar-fallback]'],
      ['coverDataUrl', '[data-public-cover-img]', '[data-public-cover-fallback]'],
    ]) {
      const img = detail.querySelector(selector);
      const valid = isSafeProfileImage(state[key]);
      img.classList.toggle('hidden', !valid);
      detail.querySelector(fallback).classList.toggle('hidden', valid);
      if (valid) img.src = state[key]; else img.removeAttribute('src');
    }
    detail.querySelector('[data-public-avatar-fallback]').textContent = state.name
      .split(/\s+/).map(word => word[0]).slice(0, 2).join('').toUpperCase();
    const cards = detail.querySelector('[data-public-projects]');
    cards.replaceChildren();
    if (state.projects.length === 0) {
      const p = document.createElement('p');
      p.className = 'col-span-full rounded-xl bg-surface-container-low p-5 text-center text-xs text-outline';
      p.textContent = 'Belum ada portofolio yang ditampilkan oleh mitra ini.';
      cards.append(p);
    }
    for (const project of state.projects) {
      const article = document.createElement('article');
      article.className = 'overflow-hidden rounded-xl border border-outline-variant/40';
      const media = document.createElement('div');
      media.className = 'relative flex aspect-[4/3] items-center justify-center overflow-hidden';
      if (isSafeProfileImage(project.imageDataUrl)) {
        const photo = document.createElement('img');
        photo.className = 'h-full w-full object-cover';
        photo.alt = `Foto portofolio: ${project.title}`;
        photo.loading = 'lazy';
        photo.src = project.imageDataUrl;
        media.append(photo);
      } else {
        media.style.backgroundColor = project.color;
        const icon = document.createElement('span');
        icon.className = 'material-symbols-outlined text-[64px] text-primary/70';
        icon.textContent = project.icon;
        media.append(icon);
      }
      if (project.isDemo) {
        const demo = document.createElement('span');
        demo.className = 'absolute left-2 top-2 rounded-full bg-white/90 px-2 py-1 text-[10px] font-bold text-primary';
        demo.textContent = 'Ilustrasi';
        media.append(demo);
      }
      const content = document.createElement('div');
      content.className = 'p-3';
      const title = document.createElement('h3');
      title.className = 'text-xs font-bold leading-snug sm:text-sm';
      title.textContent = project.title;
      const description = document.createElement('p');
      description.className = 'mt-1 text-[11px] leading-relaxed text-outline';
      description.textContent = project.description;
      const area = document.createElement('p');
      area.className = 'mt-2 text-[11px] font-semibold text-primary';
      area.textContent = `${project.area}, Makassar`;
      content.append(title, description, area);
      article.append(media, content);
      cards.append(article);
    }
    applyPrivacy();
  }
  window.addEventListener('storage', event => {
    if (event.key === PROFILE_KEY) hydratePublicProfile();
    if (event.key === PARTNER_ACCOUNT_KEY) applyPrivacy();
  });
  window.addEventListener(PARTNER_ACCOUNT_EVENT, event => {
    if (event.detail?.providerId === providerId) applyPrivacy();
  });
  window.addEventListener(PROFILE_EVENT, event => {
    if (event.detail?.providerId === providerId) hydratePublicProfile();
  });
  hydratePublicProfile();
  applyVerification();
  const toast = detail.querySelector('#jw-provider-toast');
  let toastTimer;

  const selected = () => detail.querySelector('[data-provider-service]:checked');
  const element = (tag, className, text) => {
    const node = document.createElement(tag);
    node.className = className;
    if (text !== undefined) node.textContent = String(text);
    return node;
  };
  function updateSummary() {
    const current = selected();
    const name = current?.dataset.serviceName || 'Tidak ada layanan aktif';
    const price = current ? idr.format(Number(current.dataset.servicePrice)) : '—';
    detail.querySelectorAll('[data-provider-selected-name]').forEach(node => { node.textContent = name; });
    detail.querySelectorAll('[data-provider-price]').forEach(node => { node.textContent = price; });
    detail.querySelectorAll('[data-provider-book]').forEach(link => {
      link.setAttribute('aria-disabled', String(!current));
      link.classList.toggle('opacity-50', !current);
      link.classList.toggle('cursor-not-allowed', !current);
    });
  }
  function renderServices() {
    const previousId = selected()?.value;
    const available = activeServices(providerId, seed, providerCategory, categorySeed);
    count.textContent = String(available.length);
    empty.classList.toggle('hidden', available.length > 0);
    list.replaceChildren();
    available.forEach((item, index) => {
      const label = element('label', 'block cursor-pointer');
      label.htmlFor = `provider-service-${item.id}`;
      const input = element('input', 'peer sr-only');
      input.type = 'radio';
      input.name = 'provider-service';
      input.id = label.htmlFor;
      input.value = item.id;
      input.dataset.providerService = '';
      input.dataset.serviceName = item.name;
      input.dataset.servicePrice = String(item.price);
      input.checked = previousId ? item.id === previousId : index === 0;
      const card = element('span', 'block rounded-xl border border-outline-variant/60 p-3.5 transition hover:border-primary/60 peer-checked:border-primary peer-checked:bg-primary-fixed/30 peer-focus-visible:ring-2 peer-focus-visible:ring-primary sm:p-4');
      const head = element('span', 'flex items-start justify-between gap-3');
      const left = element('span', 'min-w-0');
      left.append(element('span', 'block text-sm font-bold text-on-surface sm:text-base', item.name), element('span', 'mt-1 block text-xs leading-relaxed text-on-surface-variant', item.description));
      head.append(left, element('span', 'mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-outline-variant/80 bg-white text-xs text-primary', '✓'));
      const footer = element('span', 'mt-3 flex flex-wrap items-center justify-between gap-2');
      footer.append(element('span', 'text-[11px] text-outline', `${item.duration} · ${item.unit}`), element('strong', 'text-sm text-primary sm:text-base', idr.format(item.price)));
      card.append(head, footer, element('span', 'mt-2 block text-[11px] leading-relaxed text-outline', item.feeNote || 'Biaya tambahan menyesuaikan kondisi pekerjaan.'));
      label.append(input, card);
      list.append(label);
    });
    if (!selected() && available.length) list.querySelector('[data-provider-service]').checked = true;
    updateSummary();
  }
  function notify(message) {
    if (!toast) return;
    toast.textContent = message;
    toast.classList.remove('hidden');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.add('hidden'), 3000);
  }

  detail.addEventListener('change', event => {
    if (event.target.matches('[data-provider-service]')) updateSummary();
  });
  detail.addEventListener('click', async event => {
    const trigger = event.target.closest('[data-provider-book], [data-provider-more-reviews], [data-provider-share]');
    if (!trigger) return;
    if (trigger.hasAttribute('data-provider-book')) {
      event.preventDefault();
      // Revalidasi untuk kasus layanan dinonaktifkan di tab browser lain.
      const active = activeServices(providerId, seed, providerCategory, categorySeed);
      const current = selected();
      if (!current || !active.some(item => item.id === normalizeServiceId(current.value))) {
        renderServices();
        notify('Layanan ini tidak tersedia. Silakan pilih layanan aktif lainnya.');
        return;
      }
      const url = new URL(trigger.href, location.origin);
      url.searchParams.set('layanan', current.value);
      location.assign(url.toString());
    } else if (trigger.hasAttribute('data-provider-more-reviews')) {
      const expanded = trigger.getAttribute('aria-expanded') === 'true';
      detail.querySelectorAll('[data-provider-extra-review]').forEach(review => review.classList.toggle('hidden', expanded));
      trigger.setAttribute('aria-expanded', String(!expanded));
      trigger.textContent = expanded ? 'Lihat semua ulasan contoh' : 'Tampilkan lebih sedikit';
    } else if (trigger.hasAttribute('data-provider-share')) {
      try {
        if (navigator.share) await navigator.share({ title: detail.dataset.providerName, url: location.href });
        else if (navigator.clipboard?.writeText) {
          await navigator.clipboard.writeText(location.href);
          notify('Tautan profil berhasil disalin.');
        } else window.prompt('Salin tautan profil:', location.href);
      } catch (error) {
        if (error.name !== 'AbortError') window.prompt('Salin tautan profil:', location.href);
      }
    }
  });
  window.addEventListener('storage', event => { if ([SERVICES_KEY, ADMIN_CATEGORIES_KEY].includes(event.key)) renderServices(); });
  window.addEventListener(SERVICES_EVENT, renderServices);
  window.addEventListener(ADMIN_CATEGORIES_EVENT, renderServices);
  renderServices();
}
