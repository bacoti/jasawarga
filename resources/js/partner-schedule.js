/** Interaksi Jadwal & Ketersediaan Mitra - tanpa API/server. */
import { readOrders, ORDER_KEY, ORDER_LABELS } from './orders-store.js';
import { getPartnerAvailability, setPartnerAvailability, PARTNER_SETTINGS_EVENT, PARTNER_SETTINGS_KEY } from './partner-store.js';
import {
  getSchedule, saveSchedule, validateSchedule, availableSlots,
  bookedOrders, dateClosedReason, makassarToday, addDays, weekday,
  SCHEDULE_KEY, SCHEDULE_EVENT,
} from './schedule-store.js';

const root = document.querySelector('#jw-partner-schedule');
if (root) {
  const providerId = Number(root.dataset.partnerId);
  const options = { allowSunday: root.dataset.allowSunday === '1' };
  const $ = selector => root.querySelector(selector);
  const $$ = selector => [...root.querySelectorAll(selector)];
  const today = makassarToday();
  const displayDate = iso => new Intl.DateTimeFormat('id-ID', {
    day: 'numeric', month: 'long', year: 'numeric', weekday: 'long', timeZone: 'UTC',
  }).format(new Date(`${iso}T00:00:00Z`));
  const briefDate = iso => new Intl.DateTimeFormat('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric', timeZone: 'UTC',
  }).format(new Date(`${iso}T00:00:00Z`));
  const dateKey = date => date.toISOString().slice(0, 10);
  const state = { schedule: getSchedule(providerId, options), slots: [], selectedDate: addDays(today, 1), calendarMonth: today.slice(0, 7) };
  let toastTimeout;

  function toast(message) {
    const element = $('[data-sch-toast]');
    element.textContent = message;
    element.classList.remove('hidden');
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => element.classList.add('hidden'), 3500);
  }
  function error(selector, message) {
    const element = $(selector);
    element.textContent = message;
    element.classList.toggle('hidden', !message);
  }
  function el(tag, className, text) {
    const node = document.createElement(tag);
    node.className = className;
    if (text !== undefined) node.textContent = String(text);
    return node;
  }
  function drawSlots() {
    const container = $('[data-sch-slots-list]');
    container.replaceChildren();
    state.slots.slice().sort().forEach(time => {
      const chip = el('span', 'inline-flex items-center gap-1.5 rounded-lg border border-outline-variant/60 bg-surface-container-low px-2 py-1.5 text-xs font-bold', `${time.replace(':', '.')} WITA`);
      const remove = el('button', 'rounded-md p-0.5 text-outline hover:text-error', '×');
      remove.type = 'button';
      remove.setAttribute('aria-label', `Hapus slot ${time}`);
      remove.addEventListener('click', () => {
        state.slots = state.slots.filter(value => value !== time);
        drawSlots();
      });
      chip.append(remove);
      container.append(chip);
    });
    if (!state.slots.length) container.append(el('p', 'text-xs text-error', 'Tambahkan minimal satu slot.'));
  }
  function renderForm() {
    const schedule = state.schedule;
    $$('[data-sch-day]').forEach(input => { input.checked = schedule.workDays.includes(Number(input.value)); });
    $('[data-sch-open]').value = schedule.open;
    $('[data-sch-close]').value = schedule.close;
    $('[data-sch-break-start]').value = schedule.breakStart;
    $('[data-sch-break-end]').value = schedule.breakEnd;
    state.slots = [...schedule.slots];
    drawSlots();
  }
  function proposed() {
    return {
      version: 1,
      workDays: $$('[data-sch-day]:checked').map(input => Number(input.value)),
      open: $('[data-sch-open]').value,
      close: $('[data-sch-close]').value,
      breakStart: $('[data-sch-break-start]').value,
      breakEnd: $('[data-sch-break-end]').value,
      slots: [...state.slots],
      holidays: [...state.schedule.holidays],
    };
  }
  $('#jw-sch-form').addEventListener('submit', event => {
    event.preventDefault();
    try {
      const next = validateSchedule(proposed());
      state.schedule = saveSchedule(providerId, next, readOrders());
      error('[data-sch-form-error]', '');
      renderAll();
      toast('Jam operasional dan slot tersimpan di browser ini.');
    } catch (failure) { error('[data-sch-form-error]', failure.message); }
  });
  $('[data-sch-add-slot]').addEventListener('click', () => {
    const time = $('[data-sch-new-slot]').value;
    if (!/^\d{2}:\d{2}$/.test(time)) return toast('Isi jam slot terlebih dahulu.');
    if (state.slots.includes(time)) return toast('Slot tersebut sudah tersedia.');
    if (state.slots.length >= 16) return toast('Maksimal 16 slot per hari.');
    state.slots.push(time);
    $('[data-sch-new-slot]').value = '';
    drawSlots();
    error('[data-sch-form-error]', '');
  });

  $('[data-sch-holiday-date]').min = today;
  $('[data-sch-holiday-date]').max = addDays(today, 365);
  $('#jw-sch-holiday-form').addEventListener('submit', event => {
    event.preventDefault();
    const date = $('[data-sch-holiday-date]').value;
    const label = $('[data-sch-holiday-label]').value.trim() || 'Libur mitra';
    if (date < today || date > addDays(today, 365)) return error('[data-sch-holiday-error]', 'Pilih tanggal hari ini hingga 365 hari mendatang.');
    try {
      state.schedule = saveSchedule(providerId, {
        ...state.schedule,
        holidays: [...state.schedule.holidays, { date, label }],
      }, readOrders());
      $('[data-sch-holiday-date]').value = '';
      $('[data-sch-holiday-label]').value = '';
      error('[data-sch-holiday-error]', '');
      renderAll();
      toast('Tanggal libur ditambahkan.');
    } catch (failure) { error('[data-sch-holiday-error]', failure.message); }
  });
  function drawHolidays() {
    const list = $('[data-sch-holidays-list]');
    list.replaceChildren();
    if (!state.schedule.holidays.length) {
      list.append(el('p', 'rounded-xl bg-surface-container-low p-3 text-xs text-outline', 'Belum ada tanggal libur khusus.'));
      return;
    }
    state.schedule.holidays.forEach(item => {
      const row = el('div', 'flex items-center justify-between gap-3 rounded-xl border border-outline-variant/50 p-3');
      const data = el('div', 'min-w-0');
      data.append(el('p', 'text-xs font-extrabold', briefDate(item.date)));
      data.append(el('p', 'mt-0.5 break-words text-[11px] text-outline', item.label));
      const remove = el('button', 'shrink-0 rounded-lg border border-outline-variant/60 px-3 py-1.5 text-xs font-bold text-error', 'Hapus');
      remove.type = 'button';
      remove.setAttribute('aria-label', `Hapus libur ${item.date}`);
      remove.addEventListener('click', () => {
        try {
          state.schedule = saveSchedule(providerId, {
            ...state.schedule, holidays: state.schedule.holidays.filter(holiday => holiday.date !== item.date),
          }, readOrders());
          renderAll(); toast('Tanggal libur dihapus.');
        } catch (failure) { toast(failure.message); }
      });
      row.append(data, remove); list.append(row);
    });
  }
  function renderAvailability() {
    const available = getPartnerAvailability(providerId).available;
    const button = $('[data-sch-availability]');
    button.setAttribute('aria-pressed', String(available));
    button.textContent = available ? 'Jeda penerimaan' : 'Aktifkan penerimaan';
    $('[data-sch-availability-label]').textContent = available
      ? 'Menerima pesanan simulasi pada hari dan slot yang dibuka.'
      : 'Ditutup sementara — semua slot baru tidak bisa dipilih Warga.';
  }
  $('[data-sch-availability]').addEventListener('click', () => {
    try {
      const nowAvailable = getPartnerAvailability(providerId).available;
      setPartnerAvailability(providerId, !nowAvailable);
      renderAll();
      toast(nowAvailable ? 'Pemesanan baru dijeda.' : 'Pemesanan baru kembali dibuka.');
    } catch (failure) { toast(failure.message); }
  });

  function ordersForDate(date) {
    return readOrders().filter(item => Number(item.providerId) === providerId && item.date === date)
      .sort((a, b) => a.time.localeCompare(b.time));
  }
  function renderCalendar() {
    const [year, month] = state.calendarMonth.split('-').map(Number);
    const first = new Date(Date.UTC(year, month - 1, 1));
    const offset = (first.getUTCDay() + 6) % 7;
    const start = new Date(first.getTime());
    start.setUTCDate(first.getUTCDate() - offset);
    const calendar = $('[data-sch-calendar]');
    calendar.replaceChildren();
    $('[data-sch-month]').textContent = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric', timeZone: 'UTC' }).format(first);
    const allOrders = readOrders();
    for (let i = 0; i < 42; i += 1) {
      const date = new Date(start.getTime());
      date.setUTCDate(date.getUTCDate() + i);
      const dateStr = dateKey(date);
      const active = bookedOrders(allOrders, providerId, dateStr).length;
      const holiday = state.schedule.holidays.some(item => item.date === dateStr);
      const working = state.schedule.workDays.includes(weekday(dateStr));
      const outside = date.getUTCMonth() !== month - 1;
      const button = el('button', 'relative flex h-10 min-w-0 flex-col items-center justify-center rounded-lg text-xs font-semibold transition focus-visible:ring-2 focus-visible:ring-primary', String(date.getUTCDate()));
      button.type = 'button';
      button.title = `${briefDate(dateStr)} · ${active} reservasi${holiday ? ' · libur' : !working ? ' · tutup' : ''}`;
      button.setAttribute('aria-label', button.title);
      button.setAttribute('aria-pressed', String(dateStr === state.selectedDate));
      button.classList.add(dateStr === state.selectedDate ? 'bg-primary text-white' : outside ? 'text-outline/50' : holiday ? 'bg-tertiary-fixed/50 text-tertiary' : !working ? 'bg-surface-container-low text-outline' : 'hover:bg-primary-fixed text-on-surface');
      if (active > 0) button.append(el('span', `mt-0.5 h-1.5 w-1.5 rounded-full ${dateStr === state.selectedDate ? 'bg-white' : 'bg-primary'}`));
      if (holiday && !active) button.append(el('span', `mt-0.5 h-1.5 w-1.5 rounded-full ${dateStr === state.selectedDate ? 'bg-white' : 'bg-tertiary'}`));
      button.addEventListener('click', () => {
        state.selectedDate = dateStr;
        if (outside) state.calendarMonth = dateStr.slice(0, 7);
        renderCalendar(); renderDay();
      });
      calendar.append(button);
    }
  }
  function moveMonth(delta) {
    const [year, month] = state.calendarMonth.split('-').map(Number);
    const date = new Date(Date.UTC(year, month - 1 + delta, 1));
    state.calendarMonth = dateKey(date).slice(0, 7);
    renderCalendar();
  }
  $('[data-sch-prev-month]').addEventListener('click', () => moveMonth(-1));
  $('[data-sch-next-month]').addEventListener('click', () => moveMonth(1));

  function renderDay() {
    const date = state.selectedDate;
    $('[data-sch-selected-title]').textContent = displayDate(date);
    const closed = dateClosedReason(date, state.schedule, { today });
    const available = getPartnerAvailability(providerId).available;
    $('[data-sch-day-note]').textContent = closed || (!available ? 'Mitra sedang menjeda penerimaan booking baru.' : 'Reservasi dan slot 60 menit · WITA');
    const container = $('[data-sch-day-orders]');
    container.replaceChildren();
    const dayOrders = ordersForDate(date);
    if (!dayOrders.length) container.append(el('p', 'rounded-lg bg-surface-container-low p-3 text-xs text-outline', 'Belum ada pesanan untuk tanggal ini.'));
    dayOrders.forEach(order => {
      const row = el('div', 'rounded-xl border border-outline-variant/40 p-3');
      row.append(el('p', 'text-xs font-bold', `${order.time.replace(':', '.')} · ${order.service}`));
      row.append(el('p', 'mt-1 text-[11px] text-outline', `${ORDER_LABELS[order.status] || order.status} · ${order.reference}`));
      container.append(row);
    });
    const slotContainer = $('[data-sch-day-slots]');
    slotContainer.replaceChildren();
    availableSlots({ providerId, date, schedule: state.schedule, orders: readOrders(), available, today }).forEach(slot => {
      const chip = el('div', `rounded-lg border px-2 py-2.5 text-center text-xs font-bold ${slot.ok ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-outline-variant/50 bg-surface-container-low text-outline'}`, `${slot.time.replace(':', '.')} · ${slot.ok ? 'Tersedia' : 'Tidak tersedia'}`);
      chip.title = slot.ok ? 'Siap untuk booking demo' : slot.reason;
      slotContainer.append(chip);
    });
    if (!state.schedule.slots.length) slotContainer.append(el('p', 'text-xs text-outline', 'Belum ada slot.'));
  }
  function renderUpcoming() {
    const orders = bookedOrders(readOrders(), providerId).filter(order => order.date >= today)
      .sort((a, b) => `${a.date}T${a.time}`.localeCompare(`${b.date}T${b.time}`));
    $('[data-sch-upcoming-count]').textContent = String(orders.length);
    const list = $('[data-sch-upcoming]');
    list.replaceChildren();
    if (!orders.length) list.append(el('p', 'rounded-lg bg-surface-container-low p-3 text-xs text-outline', 'Belum ada reservasi aktif yang akan datang.'));
    orders.slice(0, 8).forEach(order => {
      const link = el('a', 'block rounded-xl border border-outline-variant/40 p-3 transition hover:bg-surface-container-low');
      link.href = `/pesanan-saya/${encodeURIComponent(order.reference)}`;
      link.append(el('p', 'text-xs font-bold', `${briefDate(order.date)} · ${order.time.replace(':', '.')}`));
      link.append(el('p', 'mt-1 truncate text-xs text-on-surface-variant', order.service));
      link.append(el('p', 'mt-1 text-[11px] text-outline', ORDER_LABELS[order.status] || order.status));
      list.append(link);
    });
    if (orders.length > 8) list.append(el('p', 'text-xs text-outline', `+${orders.length - 8} reservasi lainnya. Buka Kelola Pesanan.`));
  }
  function renderKpis() {
    $('[data-sch-days-count]').textContent = `${state.schedule.workDays.length} hari`;
    $('[data-sch-slots-count]').textContent = `${state.schedule.slots.length} slot`;
    $('[data-sch-holiday-count]').textContent = String(state.schedule.holidays.length);
  }
  function renderAll() {
    state.schedule = getSchedule(providerId, options);
    renderForm(); renderAvailability(); drawHolidays(); renderKpis(); renderCalendar(); renderDay(); renderUpcoming();
  }
  window.addEventListener('storage', event => {
    if ([ORDER_KEY, SCHEDULE_KEY, PARTNER_SETTINGS_KEY].includes(event.key)) renderAll();
  });
  window.addEventListener('jasawarga:orders-updated', renderAll);
  window.addEventListener(SCHEDULE_EVENT, renderAll);
  window.addEventListener(PARTNER_SETTINGS_EVENT, renderAll);
  renderAll();
}
