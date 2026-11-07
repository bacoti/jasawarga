// JasaWarga Bandung demo map. Leaflet is included only on the Home page.
// Tiles copyright OpenStreetMap contributors; usage subject to OSMF tile policy.
const mapRoots = [...document.querySelectorAll('[data-jw-osm-map]')];
const seedNode = document.getElementById('jw-map-providers');
const mapProviders = seedNode ? JSON.parse(seedNode.textContent) : [];
const BANDUNG = [-6.9147, 107.6098];
const maps = new Map();
const isVisible = (root) => getComputedStyle(root).display !== 'none' && root.getClientRects().length > 0;

function buildMap(root) {
  if (maps.has(root) || !isVisible(root) || !window.L) return;
  const canvas = root.querySelector('[data-jw-osm-canvas]');
  if (!canvas) return;
  const map = window.L.map(canvas, { scrollWheelZoom: false, zoomControl: false }).setView(BANDUNG, 12);
  window.L.control.zoom({ position: 'topright' }).addTo(map);
  window.L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap contributors</a>',
  }).addTo(map);
  for (const item of mapProviders) {
    const latitude = Number(item.lat);
    const longitude = Number(item.lng);
    if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) continue;
    const marker = window.L.circleMarker([latitude, longitude], {
      radius: 9, color: '#ffffff', weight: 2.5,
      fillColor: item.available ? '#089FDA' : '#64748B', fillOpacity: 0.98,
    }).addTo(map);
    const popup = document.createElement('div');
    const name = document.createElement('strong'); name.textContent = item.name;
    const area = document.createElement('p'); area.textContent = `Kecamatan ${item.area} · Titik ilustrasi`;
    const link = document.createElement('a'); link.href = `/mitra/${item.id}`; link.textContent = 'Lihat profil →';
    popup.append(name, area, link);
    marker.bindPopup(popup);
    marker.on('click', () => {
      root.querySelectorAll('[data-jw-map-selected]').forEach(node => { node.textContent = `${item.name} · ${item.area}`; });
      root.querySelectorAll('[data-jw-map-profile-link]').forEach(node => { node.href = `/mitra/${item.id}`; });
    });
  }
  maps.set(root, map);
  root.querySelector('[data-jw-osm-loading]')?.remove();
  window.setTimeout(() => map.invalidateSize(), 100);
}
function tryInit() {
  if (!mapRoots.length) return;
  if (!window.L) {
    mapRoots.forEach(root => {
      const note = root.querySelector('[data-jw-osm-loading]');
      if (note) {
        note.classList.remove('pointer-events-none');
        note.replaceChildren();
        const link = document.createElement('a');
        link.href = 'https://www.openstreetmap.org/#map=12/-6.9147/107.6098';
        link.target = '_blank'; link.rel = 'noopener noreferrer';
        link.textContent = 'Peta belum termuat. Buka OpenStreetMap ↗';
        link.className = 'rounded-xl bg-white px-4 py-3 text-center text-xs font-bold text-[#0C3065]';
        note.append(link);
      }
    });
    return;
  }
  mapRoots.forEach(buildMap);
}
if (mapRoots.length) {
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', tryInit, { once: true });
  else tryInit();
  window.addEventListener('resize', () => {
    if (window.L) mapRoots.forEach(root => {
      if (isVisible(root)) buildMap(root);
      if (isVisible(root)) maps.get(root)?.invalidateSize();
    });
  });
  window.addEventListener('jasawarga:map-focus', () => maps.forEach(map => map.invalidateSize()));
  window.addEventListener('jasawarga:map-center', () => maps.forEach(map => map.setView(BANDUNG, 12)));
  window.addEventListener('jasawarga:map-zoom', (event) => maps.forEach(map => map.setZoom(map.getZoom() + (Number(event.detail) || 1))));
}
