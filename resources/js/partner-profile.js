import {
  PROFILE_KEY, PROFILE_EVENT, MAX_PROJECTS, MAX_IMAGE_CHARS,
  readPartnerProfile, savePartnerProfile, savePartnerProject,
  removePartnerProject, isSafeProfileImage,
} from './partner-profile-store.js';
import { readPartnerModeration, partnerVerification, ADMIN_PARTNER_KEY, ADMIN_MANAGEMENT_EVENT } from './admin-management-store.js';

/** Hanya foto lokal, dikompresi sebelum disimpan sebagai JPEG base64. */
export async function compressLocalPhoto(file, maxWidth = 900) {
  if (!file || !['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
    throw new Error('Pilih foto berformat JPG, PNG, atau WebP.');
  }
  if (file.size > 2 * 1024 * 1024) throw new Error('Ukuran file maksimal 2 MB.');
  const objectUrl = URL.createObjectURL(file);
  let img;
  try {
    img = await new Promise((resolve, reject) => {
      const node = new Image();
      node.onload = () => resolve(node);
      node.onerror = () => reject(new Error('Gambar tidak dapat dibaca.'));
      node.src = objectUrl;
    });
  } finally { URL.revokeObjectURL(objectUrl); }
  if (!img.naturalWidth || !img.naturalHeight) throw new Error('Dimensi gambar tidak valid.');
  const scale = Math.min(1, maxWidth / Math.max(img.naturalWidth, img.naturalHeight));
  const canvas = document.createElement('canvas');
  canvas.width = Math.max(1, Math.round(img.naturalWidth * scale));
  canvas.height = Math.max(1, Math.round(img.naturalHeight * scale));
  const context = canvas.getContext('2d');
  if (!context) throw new Error('Browser tidak mendukung pemrosesan gambar.');
  context.fillStyle = '#FFFFFF';
  context.fillRect(0, 0, canvas.width, canvas.height);
  context.drawImage(img, 0, 0, canvas.width, canvas.height);
  let output = '';
  for (const quality of [0.78, 0.62, 0.44]) {
    output = canvas.toDataURL('image/jpeg', quality);
    if (output.length <= MAX_IMAGE_CHARS) break;
  }
  if (!isSafeProfileImage(output)) throw new Error('Foto masih terlalu besar untuk penyimpanan browser. Pilih gambar lain.');
  return output;
}

const root = document.querySelector('#jw-partner-profile');
if (root) {
  const partnerId = Number(root.dataset.partnerId);
  function updateReadOnlyVerification() {
    const text = root.querySelector('[data-partner-readonly-verify]');
    if (!text) return;
    const seed = JSON.parse(root.querySelector('[data-pp-seed]').textContent);
    const status = partnerVerification(seed.provider, readPartnerModeration());
    text.textContent = ({verified: 'Terverifikasi (data demo)', pending: 'Belum diverifikasi (data demo)', rejected: 'Ditolak (data demo)'})[status];
    const icon = root.querySelector('[data-partner-readonly-verify-icon]');
    if (icon) icon.textContent = status === 'verified' ? 'verified' : 'info';
  }
  window.addEventListener(ADMIN_MANAGEMENT_EVENT, updateReadOnlyVerification);
  window.addEventListener('storage', event => { if(event.key === ADMIN_PARTNER_KEY || event.key === null) updateReadOnlyVerification(); });
  updateReadOnlyVerification();
  const seed = JSON.parse(root.querySelector('[data-pp-seed]').textContent);
  const form = root.querySelector('#jw-pp-profile-form');
  const projectForm = root.querySelector('#jw-pp-project-form');
  const projectDialog = root.querySelector('#jw-pp-project-dialog');
  const deleteDialog = root.querySelector('#jw-pp-delete-dialog');
  const projectList = root.querySelector('[data-pp-project-list]');
  const $ = selector => root.querySelector(selector);
  const $$ = selector => [...root.querySelectorAll(selector)];
  const photoDraft = { avatarDataUrl: '', coverDataUrl: '' };
  let projectPhoto = '';
  let editId = '';
  let deleteId = '';
  let timer;

  function notify(message) {
    const toast = $('[data-pp-toast]');
    toast.textContent = message;
    toast.classList.remove('hidden');
    clearTimeout(timer);
    timer = setTimeout(() => toast.classList.add('hidden'), 3400);
  }
  function errorTo(selector, message) {
    const node = $(selector);
    node.textContent = message || '';
    node.classList.toggle('hidden', !message);
  }
  const create = (tag, classes, text) => {
    const node = document.createElement(tag);
    node.className = classes;
    if (text !== undefined) node.textContent = String(text);
    return node;
  };
  function showImage(photo, imgSelector, placeholderSelector) {
    const image = $(imgSelector);
    const placeholder = $(placeholderSelector);
    const valid = isSafeProfileImage(photo);
    image.classList.toggle('hidden', !valid);
    placeholder?.classList.toggle('hidden', valid);
    if (valid) image.src = photo;
    else image.removeAttribute('src');
  }
  function previewIdentity(profile) {
    $$('[data-pp-brand-name]').forEach(node => { node.textContent = profile.name; });
    $('[data-pp-avatar-placeholder]').textContent = profile.name
      .split(/\s+/).map(part => part[0]).slice(0, 2).join('').toUpperCase() || 'JW';
    showImage(photoDraft.avatarDataUrl, '[data-pp-avatar-preview]', '[data-pp-avatar-placeholder]');
    showImage(photoDraft.coverDataUrl, '[data-pp-cover-preview]', '[data-pp-cover-placeholder]');
  }
  function populate() {
    const state = readPartnerProfile(partnerId, seed.provider, seed.profile);
    for (const name of ['name', 'headline', 'about', 'phone']) form.elements[name].value = state[name];
    form.elements.experienceYears.value = String(state.experienceYears);
    form.elements.specialties.value = state.specialties.join(', ');
    $$('[data-pp-coverage]').forEach(input => { input.checked = state.coverage.includes(input.value); });
    photoDraft.avatarDataUrl = state.avatarDataUrl;
    photoDraft.coverDataUrl = state.coverDataUrl;
    $('[data-pp-about-count]').textContent = String(state.about.length);
    previewIdentity(state);
    errorTo('[data-pp-form-error]', '');
    renderProjects();
  }
  function projectCard(project) {
    const card = create('article', 'overflow-hidden rounded-2xl border border-outline-variant/50 bg-white shadow-sm');
    const media = create('div', 'relative flex aspect-[4/3] items-center justify-center overflow-hidden bg-primary-fixed');
    if (isSafeProfileImage(project.imageDataUrl)) {
      const img = create('img', 'h-full w-full object-cover');
      img.src = project.imageDataUrl;
      img.alt = `Foto portofolio: ${project.title}`;
      img.loading = 'lazy';
      media.append(img);
    } else {
      media.style.backgroundColor = project.color;
      media.append(create('span', 'material-symbols-outlined text-[64px] text-primary/60', project.icon || 'construction'));
    }
    if (project.isDemo) {
      media.append(create('span', 'absolute left-3 top-3 rounded-full bg-white/95 px-2.5 py-1 text-[10px] font-extrabold text-primary', 'Ilustrasi bawaan'));
    }
    const body = create('div', 'p-4');
    body.append(create('h4', 'text-sm font-extrabold leading-snug text-on-surface', project.title));
    body.append(create('p', 'mt-1 line-clamp-2 text-xs leading-relaxed text-on-surface-variant', project.description));
    const area = create('p', 'mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-outline');
    area.append(create('span', 'material-symbols-outlined text-[15px]', 'location_on'));
    area.append(document.createTextNode(`${project.area}, Makassar`));
    body.append(area);
    const buttons = create('div', 'mt-4 flex gap-2 border-t border-outline-variant/30 pt-3');
    const edit = create('button', 'flex-1 rounded-xl border border-outline-variant/60 px-3 py-2 text-xs font-bold text-primary transition hover:bg-surface-container-low', 'Edit');
    edit.type = 'button'; edit.dataset.ppProjectEdit = project.id;
    const del = create('button', 'flex-1 rounded-xl border border-outline-variant/60 px-3 py-2 text-xs font-bold text-error transition hover:bg-error-container/40', 'Hapus');
    del.type = 'button'; del.dataset.ppProjectDelete = project.id;
    buttons.append(edit, del);
    body.append(buttons); card.append(media, body);
    return card;
  }
  function renderProjects() {
    const state = readPartnerProfile(partnerId, seed.provider, seed.profile);
    projectList.replaceChildren(...state.projects.map(projectCard));
    $('[data-pp-project-count]').textContent = String(state.projects.length);
    $('[data-pp-project-empty]').classList.toggle('hidden', state.projects.length !== 0);
    $('[data-pp-add-project]').disabled = state.projects.length >= MAX_PROJECTS;
    $('[data-pp-add-project]').classList.toggle('opacity-50', state.projects.length >= MAX_PROJECTS);
  }
  function projectPreview() {
    const visible = isSafeProfileImage(projectPhoto);
    $('[data-pp-project-image-box]').classList.toggle('hidden', !visible);
    const preview = $('[data-pp-project-image-preview]');
    if (visible) preview.src = projectPhoto;
    else preview.removeAttribute('src');
  }
  function openProject(project = null) {
    projectForm.reset();
    editId = project?.id || '';
    projectPhoto = project?.imageDataUrl || '';
    projectForm.elements.title.value = project?.title || '';
    projectForm.elements.description.value = project?.description || '';
    projectForm.elements.area.value = project?.area || 'Panakkukang';
    $('[data-pp-project-dialog-heading]').textContent = project ? 'Edit portofolio' : 'Tambah portofolio';
    $('[data-pp-project-image]').value = '';
    errorTo('[data-pp-project-error]', '');
    projectPreview();
    projectDialog.showModal();
    projectForm.elements.title.focus();
  }

  $$('[data-pp-image-input]').forEach(input => input.addEventListener('change', async () => {
    const file = input.files?.[0];
    if (!file) return;
    input.disabled = true;
    try {
      const key = input.dataset.ppImageInput;
      photoDraft[key] = await compressLocalPhoto(file, key === 'avatarDataUrl' ? 420 : 1100);
      previewIdentity({ name: form.elements.name.value.trim() || seed.provider.name });
      notify('Foto siap. Tekan “Simpan profil” untuk menerapkan perubahan.');
    } catch (error) { errorTo('[data-pp-form-error]', error.message); }
    finally { input.disabled = false; input.value = ''; }
  }));
  $$('[data-pp-image-clear]').forEach(button => button.addEventListener('click', () => {
    photoDraft[button.dataset.ppImageClear] = '';
    previewIdentity({ name: form.elements.name.value.trim() || seed.provider.name });
  }));
  form.elements.about.addEventListener('input', () => {
    $('[data-pp-about-count]').textContent = String(form.elements.about.value.length);
  });
  form.addEventListener('submit', event => {
    event.preventDefault();
    const input = {
      name: form.elements.name.value,
      headline: form.elements.headline.value,
      about: form.elements.about.value,
      experienceYears: form.elements.experienceYears.value,
      phone: form.elements.phone.value,
      specialties: form.elements.specialties.value,
      coverage: $$('[data-pp-coverage]:checked').map(item => item.value),
      avatarDataUrl: photoDraft.avatarDataUrl,
      coverDataUrl: photoDraft.coverDataUrl,
    };
    try {
      savePartnerProfile(partnerId, seed.provider, seed.profile, input);
      errorTo('[data-pp-form-error]', '');
      previewIdentity({ name: String(input.name).trim() });
      notify('Profil disimpan. Perubahan muncul pada profil publik di browser ini.');
    } catch (error) {
      errorTo('[data-pp-form-error]', error.message);
      $('[data-pp-form-error]').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
  $('[data-pp-add-project]').addEventListener('click', () => {
    const state = readPartnerProfile(partnerId, seed.provider, seed.profile);
    if (state.projects.length >= MAX_PROJECTS) return notify('Batas 9 portofolio tercapai.');
    openProject();
  });
  projectList.addEventListener('click', event => {
    const editButton = event.target.closest('[data-pp-project-edit]');
    const deleteButton = event.target.closest('[data-pp-project-delete]');
    const state = readPartnerProfile(partnerId, seed.provider, seed.profile);
    if (editButton) {
      const item = state.projects.find(row => row.id === editButton.dataset.ppProjectEdit);
      if (item) openProject(item);
    } else if (deleteButton) {
      deleteId = deleteButton.dataset.ppProjectDelete;
      const item = state.projects.find(row => row.id === deleteId);
      if (!item) return;
      $('[data-pp-delete-title]').textContent = item.title;
      deleteDialog.showModal();
    }
  });
  $('[data-pp-project-image]').addEventListener('change', async event => {
    const file = event.target.files?.[0];
    if (!file) return;
    event.target.disabled = true;
    try { projectPhoto = await compressLocalPhoto(file, 900); projectPreview(); errorTo('[data-pp-project-error]', ''); }
    catch (error) { errorTo('[data-pp-project-error]', error.message); }
    finally { event.target.disabled = false; event.target.value = ''; }
  });
  $('[data-pp-clear-project-image]').addEventListener('click', () => { projectPhoto = ''; projectPreview(); });
  projectForm.addEventListener('submit', event => {
    event.preventDefault();
    try {
      savePartnerProject(partnerId, seed.provider, seed.profile, {
        title: projectForm.elements.title.value,
        description: projectForm.elements.description.value,
        area: projectForm.elements.area.value,
        imageDataUrl: projectPhoto,
      }, editId);
      projectDialog.close();
      renderProjects();
      notify(editId ? 'Portofolio berhasil diperbarui.' : 'Portofolio baru ditambahkan.');
    } catch (error) { errorTo('[data-pp-project-error]', error.message); }
  });
  $$('[data-pp-close-project]').forEach(button => button.addEventListener('click', () => projectDialog.close()));
  $('[data-pp-cancel-delete]').addEventListener('click', () => deleteDialog.close());
  $('[data-pp-confirm-delete]').addEventListener('click', () => {
    try {
      removePartnerProject(partnerId, seed.provider, seed.profile, deleteId);
      deleteDialog.close(); renderProjects(); notify('Portofolio dihapus.');
    } catch (error) { notify(error.message); }
  });
  // Dialog native Esc, klik backdrop (tanpa bergantung library tambahan).
  for (const dialog of [projectDialog, deleteDialog]) {
    dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
  }
  window.addEventListener('storage', event => {
    if (event.key === PROFILE_KEY) populate();
  });
  window.addEventListener(PROFILE_EVENT, event => {
    if (event.detail?.providerId === partnerId) renderProjects();
  });
  populate();
}
