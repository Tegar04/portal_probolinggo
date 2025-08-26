document.addEventListener('DOMContentLoaded', () => {
  const bidangWrapper = document.getElementById('bidangWrapper');
  const iframe = document.getElementById('iframeViewer');
  const iframeContainer = document.getElementById('iframeContainer');
  const btnClose = document.getElementById('btnCloseIframe');
  const loader = document.getElementById('iframeLoader');
  let layananList = [];
  
  // Auto-detect service type
  const searchInternal = document.getElementById('searchInternal');
  const searchPublik = document.getElementById('searchPublik');
  
  let serviceType = 'internal';
  let searchInput = null;
  
  if (searchInternal) {
    serviceType = 'internal';
    searchInput = searchInternal;
  } else if (searchPublik) {
    serviceType = 'publik';
    searchInput = searchPublik;
  }

  // Load layanan data
  fetch(`api/get_layanan.php?jenis=${serviceType}`)
    .then(res => {
      if (!res.ok) throw new Error("Gagal memuat data layanan.");
      return res.json();
    })
    .then(data => {
      if (!Array.isArray(data)) throw new Error("Data bukan array");
      layananList = data;
      tampilkanPerBidang(data);
    })
    .catch(err => {
      console.error('Error loading layanan:', err);
      if (bidangWrapper) {
        bidangWrapper.innerHTML = '<p style="color:red; text-align:center; padding:20px;">Terjadi kesalahan saat mengambil data layanan.</p>';
      }
    });

  // Search
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      const keyword = searchInput.value.toLowerCase().trim();
      const hasilFilter = layananList.filter(item =>
        (item.nama && item.nama.toLowerCase().includes(keyword)) ||
        (item.bidang && item.bidang.toLowerCase().includes(keyword)) ||
        (item.deskripsi && item.deskripsi.toLowerCase().includes(keyword))
      );
      tampilkanPerBidang(hasilFilter);
    });
  }

  // Close iframe
  if (btnClose) {
    btnClose.addEventListener('click', tutupIframe);
  }

  function handleViewportChange() {
    if (iframeContainer && iframeContainer.style.display !== 'none') {
      setTimeout(() => window.scrollTo(0, 0), 100);
    }
  }
  window.addEventListener('orientationchange', handleViewportChange);
  window.addEventListener('resize', handleViewportChange);

  // Show iframe with hash validation
  window.tampilkanIframe = function(url, hash) {
    if (!iframe || !iframeContainer) {
      console.error('Iframe elements not found');
      return;
    }

    // Validasi hash terhadap data asli dari server
    const valid = layananList.some(item => item.url === url && item.hash === hash);
    if (!valid) {
      alert("URL tidak valid atau telah dimodifikasi!");
      return;
    }

    if (loader) loader.style.display = 'block';
    iframe.src = url;
    iframeContainer.style.display = 'flex';
    document.body.classList.add('no-scroll');
    document.documentElement.classList.add('no-scroll');
    window.scrollTo({ top: 0, behavior: 'smooth' });

    iframe.onload = () => { if (loader) loader.style.display = 'none'; };
    iframe.onerror = () => {
      if (loader) loader.style.display = 'none';
      console.error('Failed to load iframe:', url);
    };

    const escapeListener = e => { if (e.key === 'Escape') tutupIframe(); };
    document.addEventListener('keydown', escapeListener);
    iframeContainer.escapeListener = escapeListener;
  };

  function tutupIframe() {
    if (!iframe || !iframeContainer) return;

    iframe.src = '';
    iframeContainer.style.display = 'none';
    if (loader) loader.style.display = 'none';
    document.body.classList.remove('no-scroll');
    document.documentElement.classList.remove('no-scroll');

    if (iframeContainer.escapeListener) {
      document.removeEventListener('keydown', iframeContainer.escapeListener);
      delete iframeContainer.escapeListener;
    }
    iframe.onload = null;
    iframe.onerror = null;
  }

  function tampilkanPerBidang(data) {
    if (!bidangWrapper) return;
    bidangWrapper.innerHTML = '';

    if (!Array.isArray(data) || data.length === 0) {
      bidangWrapper.innerHTML = '<p style="text-align:center; padding:20px; color:#666;">Tidak ada layanan yang ditemukan.</p>';
      return;
    }

    const bidangMap = {};
    data.forEach(item => {
      if (!item || !item.nama) return;
      const bidang = item.bidang?.trim() || '';
      if (!bidangMap[bidang]) bidangMap[bidang] = [];
      bidangMap[bidang].push(item);
    });

    if (bidangMap['']) {
      bidangWrapper.appendChild(renderBidangSectionSafe('Layanan Umum', bidangMap['']));
      delete bidangMap[''];
    }

    Object.keys(bidangMap).sort().forEach(bidang => {
      bidangWrapper.appendChild(renderBidangSectionSafe(bidang, bidangMap[bidang]));
    });
  }

  // --- Fungsi baru: versi aman tanpa innerHTML dengan deskripsi ---
  function renderBidangSectionSafe(judul, layanan) {
    const section = document.createElement('div');
    section.classList.add('bidang-section');

    const title = document.createElement('h3');
    title.className = 'bidang-title';
    title.textContent = judul;
    section.appendChild(title);

    const grid = document.createElement('div');
    grid.className = 'grid-container';

    layanan
      .filter(item => item && item.nama && item.url && item.hash)
      .forEach(item => {
        const link = document.createElement('a');
        link.className = 'layanan-card';
        link.href = "javascript:void(0);";
        link.title = item.nama;
        link.addEventListener('click', () => tampilkanIframe(item.url, item.hash));

        const logoWrapper = document.createElement('div');
        logoWrapper.className = 'logo-wrapper';

        const img = document.createElement('img');

        // Normalisasi path (ubah "\" ke "/")
        let logoPath = (item.logo || '').replace(/\\/g, "/");
        if (logoPath.startsWith("assets/layanan/")) {
          img.src = logoPath;
        } else {
          img.src = "assets/layanan/" + logoPath;
        }

        img.alt = item.nama;
        img.onerror = () => { img.src = "assets/logo/default.png"; };

        logoWrapper.appendChild(img);

        const namaSpan = document.createElement('span');
        namaSpan.className = 'layanan-nama';
        namaSpan.textContent = item.nama;

        // Tambahkan deskripsi
        const deskripsiSpan = document.createElement('span');
        deskripsiSpan.className = 'layanan-deskripsi';
        deskripsiSpan.textContent = item.deskripsi || 'Tidak ada deskripsi tersedia';
        deskripsiSpan.title = item.deskripsi || 'Tidak ada deskripsi tersedia'; // Tooltip

        link.appendChild(logoWrapper);
        link.appendChild(namaSpan);
        link.appendChild(deskripsiSpan);

        grid.appendChild(link);
      });

    section.appendChild(grid);
    return section;
  }

  // --- Fungsi lama dengan deskripsi (untuk kompatibilitas) ---
  function renderBidangSection(judul, layanan) {
    const section = document.createElement('div');
    section.classList.add('bidang-section');

    const title = document.createElement('h3');
    title.className = 'bidang-title';
    title.textContent = judul;

    const grid = document.createElement('div');
    grid.className = 'grid-container';

    grid.innerHTML = layanan
      .filter(item => item && item.nama && item.url && item.hash)
      .map(item => `
        <a class="layanan-card" href="javascript:void(0);" 
           onclick="tampilkanIframe('${item.url}', '${item.hash}')" 
           title="${item.nama}">
          <div class="logo-wrapper">
            <img src="assets/layanan/${item.logo}" alt="${item.nama}" 
                 onerror="this.src='assets/logo/default.png'" />
          </div>
          <span class="layanan-nama">${item.nama}</span>
          <span class="layanan-deskripsi" title="${item.deskripsi || 'Tidak ada deskripsi tersedia'}">${item.deskripsi || 'Tidak ada deskripsi tersedia'}</span>
        </a>
      `).join('');

    section.appendChild(title);
    section.appendChild(grid);
    return section;
  }

  // Back button handler
  window.addEventListener('popstate', () => {
    if (iframeContainer && iframeContainer.style.display !== 'none') {
      tutupIframe();
    }
  });

  document.addEventListener('click', e => {
    if (iframeContainer && iframeContainer.style.display !== 'none' && e.target === iframeContainer) {
      tutupIframe();
    }
  });

  // --- Tombol Kembali ke Atas ---
  const backToTopBtn = document.getElementById("backToTopBtn");

  if (backToTopBtn) {
    window.addEventListener("scroll", () => {
      if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
        backToTopBtn.style.display = "block";
      } else {
        backToTopBtn.style.display = "none";
      }
    });

    backToTopBtn.addEventListener("click", () => {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

});