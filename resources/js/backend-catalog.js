// Hanya interaksi UI katalog: bukan sumber data dan tidak menyimpan ke localStorage.
const catalogForm = document.querySelector('[data-slug-source]');
const slugTarget = document.querySelector('[data-slug-target]');
if (catalogForm && slugTarget) {
    catalogForm.addEventListener('input', () => {
        if (slugTarget.dataset.manual === 'true') return;
        slugTarget.value = catalogForm.value.toLowerCase()
            .normalize('NFKD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    });
    slugTarget.addEventListener('input', () => { slugTarget.dataset.manual = 'true'; });
}
for (const form of document.querySelectorAll('form[data-catalog-confirm]')) {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.catalogConfirm || 'Lanjutkan tindakan ini?')) {
            event.preventDefault();
        }
    });
}
