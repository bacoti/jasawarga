/**
 * Penyimpanan riwayat pesanan demo di localStorage (bukan backend).
 * Alamat & deskripsi demo tersimpan di browser; jangan memasukkan data asli.
 */
export const ORDER_KEY = 'jasawarga:prototype-orders:v1';
export const ORDER_STEPS = ['pending', 'accepted', 'enroute', 'working', 'completed'];
export const ORDER_LABELS = Object.freeze({
  pending: 'Menunggu konfirmasi',
  accepted: 'Diterima mitra',
  enroute: 'Dalam perjalanan',
  working: 'Sedang dikerjakan',
  completed: 'Selesai',
  cancelled: 'Dibatalkan oleh Warga',
  rejected: 'Ditolak mitra',
});
const LIMIT = 50;
const referencePattern = /^JW-DEMO-[0-9]{8}-[A-F0-9]{6}$/;

function validOrder(value) {
  return value && typeof value === 'object' && value.version === 1 &&
    referencePattern.test(value.reference || '') &&
    Object.hasOwn(ORDER_LABELS, value.status) &&
    Number.isSafeInteger(value.providerId) && value.providerId > 0 &&
    typeof value.provider === 'string' &&
    typeof value.service === 'string' &&
    Number.isFinite(value.price) && value.price >= 0 &&
    typeof value.date === 'string' && typeof value.time === 'string' &&
    typeof value.createdAt === 'number' && Number.isFinite(value.createdAt) &&
    Array.isArray(value.history);
}

export function readOrders() {
  let decoded;
  try {
    decoded = JSON.parse(localStorage.getItem(ORDER_KEY) || '[]');
  } catch (_error) {
    return [];
  }
  if (!Array.isArray(decoded)) return [];
  return decoded.filter(validOrder).slice(0, LIMIT).sort((a, b) => b.createdAt - a.createdAt);
}

function writeOrders(orders) {
  // Error (mis. quota/private mode) diteruskan agar UI tidak mengklaim sukses.
  localStorage.setItem(ORDER_KEY, JSON.stringify(orders.slice(0, LIMIT)));
  window.dispatchEvent(new CustomEvent('jasawarga:orders-updated'));
}

export function createOrderFromBooking(booking) {
  if (!booking || !referencePattern.test(booking.reference || '')) {
    throw new Error('Ringkasan pemesanan tidak valid.');
  }
  const stored = readOrders();
  const existing = stored.find(order => order.reference === booking.reference);
  if (existing) return existing;
  const createdAt = Number.isFinite(booking.createdAt) ? booking.createdAt : Date.now();
  const record = {
    version: 1,
    reference: booking.reference,
    accountId: /^jw-warga-[a-z0-9-]+$/.test(String(booking.accountId || '')) ? booking.accountId : null,
    providerId: Number(booking.providerId),
    provider: String(booking.provider || '').slice(0, 120),
    providerUrl: String(booking.providerUrl || ''),
    customerName: String(booking.name || booking.customerName || 'Warga demo').slice(0, 80),
    customerPhone: String(booking.phone || booking.customerPhone || '').slice(0, 32),
    serviceIndex: Number(booking.serviceIndex) || 0,
    serviceId: typeof booking.serviceId === 'string' ? booking.serviceId.slice(0, 80) : undefined,
    service: String(booking.service || '').slice(0, 150),
    price: Number(booking.price),
    date: String(booking.date || ''),
    time: String(booking.time || ''),
    area: String(booking.area || '').slice(0, 80),
    address: String(booking.address || '').slice(0, 250),
    description: String(booking.description || '').slice(0, 600),
    createdAt,
    status: 'pending',
    history: [{ status: 'pending', at: createdAt }],
  };
  if (!validOrder(record)) throw new Error('Data pesanan tidak lengkap.');
  writeOrders([record, ...stored]);
  return record;
}

export function getOrder(reference) {
  return readOrders().find(order => order.reference === reference) || null;
}

export function nextOrderStatus(status) {
  const currentIndex = ORDER_STEPS.indexOf(status);
  return currentIndex >= 0 && currentIndex < ORDER_STEPS.length - 1
    ? ORDER_STEPS[currentIndex + 1]
    : null;
}

export function changeOrderStatus(reference, requestedStatus) {
  const orders = readOrders();
  const order = orders.find(item => item.reference === reference);
  if (!order) return null;
  const next = nextOrderStatus(order.status);
  const allowed = next === requestedStatus ||
    (order.status === 'pending' && requestedStatus === 'cancelled');
  if (!allowed) return null;
  const now = Date.now();
  order.status = requestedStatus;
  order.history = [...order.history, { status: requestedStatus, at: now, actor: requestedStatus === 'cancelled' ? 'warga' : 'mitra' }];
  writeOrders(orders);
  return order;
}

/** Penolakan oleh mitra hanya untuk status menunggu dan wajib disertai alasan. */
export function rejectPartnerOrder(reference, reason) {
  const normalized = String(reason || '').trim().replace(/\s+/g, ' ');
  if (normalized.length < 10 || normalized.length > 300) {
    throw new Error('Alasan penolakan harus 10–300 karakter.');
  }
  const orders = readOrders();
  const order = orders.find(item => item.reference === reference);
  if (!order || order.status !== 'pending') return null;
  const now = Date.now();
  order.status = 'rejected';
  order.rejectionReason = normalized;
  order.history = [...order.history, {
    status: 'rejected', at: now, actor: 'mitra', reason: normalized,
  }];
  writeOrders(orders);
  return order;
}

export function clearOrders() {
  localStorage.removeItem(ORDER_KEY);
  window.dispatchEvent(new CustomEvent('jasawarga:orders-updated'));
}

export function createSampleOrders() {
  const timestamp = Date.now();
  const samples = [
    { providerId: 1, provider: 'Budi Teknik', service: 'Cuci AC standar', serviceIndex: 0, price: 75000, area: 'Panakkukang', status: 'pending', address: 'Alamat ilustrasi di Panakkukang, Makassar', description: 'Cuci satu unit AC (contoh)', days: 2 },
    { providerId: 2, provider: 'Pak Joko Santoso', service: 'Pemeriksaan saluran mampet', serviceIndex: 0, price: 60000, area: 'Tamalate', status: 'working', address: 'Alamat ilustrasi di Tamalate, Makassar', description: 'Periksa saluran air (contoh)', days: 3 },
    { providerId: 3, provider: 'Elektrik Jaya Makassar', service: 'Pemeriksaan MCB', serviceIndex: 0, price: 85000, area: 'Panakkukang', status: 'completed', address: 'Alamat ilustrasi di Panakkukang, Makassar', description: 'Periksa MCB (contoh)', days: -1 },
  ];
  const existing = readOrders();
  const generated = samples.map((sample, index) => {
    const createdAt = timestamp - (index + 1) * 120000;
    const date = new Date(timestamp + sample.days * 86400000).toISOString().slice(0, 10);
    const sequence = sample.status === 'completed' ? ORDER_STEPS :
      sample.status === 'working' ? ORDER_STEPS.slice(0, 4) : ['pending'];
    return {
      version: 1,
      reference: `JW-DEMO-${new Date(timestamp).toISOString().slice(0,10).replaceAll('-', '')}-${Array.from(crypto.getRandomValues(new Uint8Array(3))).map(b => b.toString(16).padStart(2, '0')).join('').toUpperCase()}`,
      providerId: sample.providerId,
      provider: sample.provider,
      providerUrl: `/mitra/${sample.providerId}`,
      serviceIndex: sample.serviceIndex,
      service: sample.service,
      price: sample.price,
      date, time: '11:00', area: sample.area,
      address: sample.address, description: sample.description,
      createdAt,
      status: sample.status,
      history: sequence.map((status, i) => ({ status, at: createdAt + i * 20000 })),
    };
  });
  writeOrders([...generated, ...existing]);
  return generated;
}
