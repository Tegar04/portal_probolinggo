let internalData = [];
let publikData = [];

function tampilkanLayananKeGrid(gridId, data) {
  const grid = document.getElementById(gridId);
  if (!Array.isArray(data)) {
    grid.innerHTML = "<p>Data layanan tidak valid.</p>";
    return;
  }

  grid.innerHTML = data
    .filter(item => item && item.nama && item.logo && item.url && item.hash)
    .map(item => `
      <div class="card">
        <div class="logo-wrapper">
          <img src="assets/layanan/${item.logo}" alt="${item.nama}" onerror="this.src='assets/logo/default.png'" />
        </div>
        <h3 class="card-title">${item.nama}</h3>
        <button onclick="tampilkanIframe('${item.url}', '${item.hash}')" class="card-button">Kunjungi</button>
      </div>
    `).join('');
}

function filterLayanan(gridId, keyword) {
  const data = gridId === 'internalGrid' ? internalData : publikData;
  tampilkanLayananKeGrid(gridId, data.filter(item =>
    item.nama.toLowerCase().includes(keyword.toLowerCase())
  ));
}

document.addEventListener('DOMContentLoaded', () => {
  const inputInternal = document.getElementById('searchInternal');
  const inputPublik = document.getElementById('searchPublik');

  if (inputInternal) {
    inputInternal.addEventListener('input', () => filterLayanan('internalGrid', inputInternal.value));
  }
  if (inputPublik) {
    inputPublik.addEventListener('input', () => filterLayanan('publikGrid', inputPublik.value));
  }

  // Load internal services
  fetch('api/get_layanan.php?jenis=internal&highlight=1')
    .then(res => res.json())
    .then(data => {
      if (!Array.isArray(data)) throw new Error("Data bukan array");
      internalData = data;
      tampilkanLayananKeGrid('internalGrid', internalData);
    })
    .catch(err => {
      console.error('Gagal memuat layanan internal:', err);
      document.getElementById('internalGrid').innerHTML = "<p>Gagal memuat layanan internal.</p>";
    });

  // Load public services
  fetch('api/get_layanan.php?jenis=publik&highlight=1')
    .then(res => res.json())
    .then(data => {
      if (!Array.isArray(data)) throw new Error("Data bukan array");
      publikData = data;
      tampilkanLayananKeGrid('publikGrid', publikData);
    })
    .catch(err => {
      console.error('Gagal memuat layanan publik:', err);
      document.getElementById('publikGrid').innerHTML = "<p>Gagal memuat layanan publik.</p>";
    });
});

function tampilkanIframe(url, hash) {
  const iframe = document.getElementById('iframeViewer');
  const container = document.getElementById('iframeContainer');
  const closeBtn = document.getElementById('iframeCloseBtn');
  const loader = document.getElementById('iframeLoader');

  // Pastikan URL + hash cocok dengan data dari server
  const semuaData = [...internalData, ...publikData];
  const valid = semuaData.some(item => item.url === url && item.hash === hash);
  if (!valid) {
    alert("URL tidak valid atau telah dimodifikasi!");
    return;
  }

  // Tampilkan loader dan iframe
  if (loader) loader.style.display = 'block';
  iframe.src = url;
  container.style.display = 'flex';
  document.body.classList.add('no-scroll');
  document.documentElement.classList.add('no-scroll');

  if (closeBtn) closeBtn.onclick = tutupIframe;

  iframe.onload = () => { if (loader) loader.style.display = 'none'; };
  iframe.onerror = () => {
    if (loader) loader.style.display = 'none';
    console.error('Gagal memuat iframe:', url);
  };

  const escapeListener = e => { if (e.key === 'Escape') tutupIframe(); };
  document.addEventListener('keydown', escapeListener);
  container.escapeListener = escapeListener;
}

function tutupIframe() {
  const iframe = document.getElementById('iframeViewer');
  const container = document.getElementById('iframeContainer');
  const loader = document.getElementById('iframeLoader');

  iframe.src = '';
  container.style.display = 'none';
  if (loader) loader.style.display = 'none';

  document.body.classList.remove('no-scroll');
  document.documentElement.classList.remove('no-scroll');

  if (container.escapeListener) {
    document.removeEventListener('keydown', container.escapeListener);
    delete container.escapeListener;
  }
}

document.addEventListener('click', e => {
  const container = document.getElementById('iframeContainer');
  if (container && container.style.display !== 'none' && e.target === container) {
    tutupIframe();
  }
});
