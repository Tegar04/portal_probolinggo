let internalData = [];
let publikData = [];

// --- Fungsi tambahan: versi aman dari tampilkanLayananKeGrid ---
function tampilkanLayananKeGridSafe(gridId, data) {
  const grid = document.getElementById(gridId);
  if (!Array.isArray(data)) {
    grid.innerHTML = "<p>Data layanan tidak valid.</p>";
    return;
  }

  // Kosongkan kontainer
  grid.innerHTML = "";

  data
    .filter(item => item && item.nama && item.logo && item.url && item.hash)
    .forEach(item => {
      const card = document.createElement("div");
      card.className = "card";

      const logoWrapper = document.createElement("div");
      logoWrapper.className = "logo-wrapper";

      const img = document.createElement("img");
      let logoPath = item.logo.replace(/\\/g, "/");
      if (logoPath.startsWith("assets/layanan/")) {
        img.src = logoPath;
      } else {
        img.src = "assets/layanan/" + logoPath;
      }
      img.alt = item.nama;
      img.onerror = () => { img.src = "assets/logo/default.png"; };

      logoWrapper.appendChild(img);

      const title = document.createElement("h3");
      title.className = "card-title";
      title.textContent = item.nama;

      const btn = document.createElement("button");
      btn.className = "card-button";
      btn.textContent = "Kunjungi";
      btn.addEventListener("click", () => tampilkanIframe(item.url, item.hash));

      card.appendChild(logoWrapper);
      card.appendChild(title);
      card.appendChild(btn);

      grid.appendChild(card);
    });
}

// --- Fungsi lama tetap ada ---
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
  tampilkanLayananKeGridSafe(
    gridId,
    data.filter(item => item.nama.toLowerCase().includes(keyword.toLowerCase()))
  );
}

document.addEventListener('DOMContentLoaded', () => {
  const inputInternal = document.getElementById('searchInternal');
  const inputPublik = document.getElementById('searchPublik');

  if (inputInternal) {
    inputInternal.addEventListener('input', () => 
      filterLayanan('internalGrid', inputInternal.value)
    );
  }
  if (inputPublik) {
    inputPublik.addEventListener('input', () => 
      filterLayanan('publikGrid', inputPublik.value)
    );
  }

  // Load internal services
  fetch('api/get_layanan.php?jenis=internal&highlight=1')
    .then(res => res.json())
    .then(data => {
      if (!Array.isArray(data)) throw new Error("Data bukan array");
      internalData = data;
      tampilkanLayananKeGridSafe('internalGrid', internalData);
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
      tampilkanLayananKeGridSafe('publikGrid', publikData);
    })
    .catch(err => {
      console.error('Gagal memuat layanan publik:', err);
      document.getElementById('publikGrid').innerHTML = "<p>Gagal memuat layanan publik.</p>";
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

  // --- Hero Slider (ambil dari database via API) ---
  fetch('api/get_slider.php')
    .then(res => res.json())
    .then(data => initSlider(data))
    .catch(err => console.error("Gagal memuat slider:", err));
});

function tampilkanIframe(url, hash) {
  const iframe = document.getElementById('iframeViewer');
  const container = document.getElementById('iframeContainer');
  const closeBtn = document.getElementById('iframeCloseBtn');
  const loader = document.getElementById('iframeLoader');

  const semuaData = [...internalData, ...publikData];
  const valid = semuaData.some(item => item.url === url && item.hash === hash);
  if (!valid) {
    alert("URL tidak valid atau telah dimodifikasi!");
    return;
  }

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

// --- Fungsi Slider Dinamis ---
function initSlider(slidesData) {
  const container = document.getElementById("sliderContainer");
  const dotsContainer = document.querySelector(".hero-slider .dots");
  let current = 0, autoPlay;

  if (!container || !slidesData.length) return;

  slidesData.forEach((item, i) => {
    const slide = document.createElement("div");
    slide.className = "slide" + (i===0 ? " active" : "");
    slide.style.backgroundImage = `url(${item.gambar})`;
    slide.innerHTML = `<div class="hero-content"><h1>${item.judul}</h1></div>`;
    container.appendChild(slide);

    const dot = document.createElement("span");
    if (i===0) dot.classList.add("active");
    dot.addEventListener("click", () => goToSlide(i));
    dotsContainer.appendChild(dot);
  });

  const slides = container.querySelectorAll(".slide");
  const dots = dotsContainer.querySelectorAll("span");

  function showSlide(index) {
    slides.forEach((s,i) => s.classList.toggle("active", i===index));
    dots.forEach((d,i) => d.classList.toggle("active", i===index));
    current = index;
  }

  function nextSlide() { showSlide((current+1)%slides.length); }
  function prevSlide() { showSlide((current-1+slides.length)%slides.length); }
  function goToSlide(index) { showSlide(index); resetAutoPlay(); }

  function startAutoPlay() { autoPlay = setInterval(nextSlide, 5000); }
  function resetAutoPlay() { clearInterval(autoPlay); startAutoPlay(); }

  document.querySelector(".next").onclick = () => { nextSlide(); resetAutoPlay(); };
  document.querySelector(".prev").onclick = () => { prevSlide(); resetAutoPlay(); };

  startAutoPlay();
}
