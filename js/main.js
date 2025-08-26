let internalData = [];
let publikData = [];

// --- Enhanced function with info button and tooltip ---
function tampilkanLayananKeGridSafe(gridId, data) {
  const grid = document.getElementById(gridId);
  if (!Array.isArray(data)) {
    grid.innerHTML = "<p>Data layanan tidak valid.</p>";
    return;
  }

  // Clear container
  grid.innerHTML = "";

  data
    .filter(item => item && item.nama && item.logo && item.url && item.hash)
    .forEach(item => {
      const cardWrapper = document.createElement("div");
      cardWrapper.className = "card-wrapper";

      const card = document.createElement("div");
      card.className = "card";
      card.addEventListener('click', () => tampilkanIframe(item.url, item.hash));

      const logoWrapper = document.createElement("div");
      logoWrapper.className = "logo-wrapper";

      const img = document.createElement("img");
      let logoPath = (item.logo || '').replace(/\\/g, "/");
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

      // Enhanced description with smart truncation
      const description = document.createElement("p");
      description.className = "card-description";
      const fullDesc = item.deskripsi || "Tidak ada deskripsi tersedia";
      const shortDesc = fullDesc.length > 80 ? fullDesc.substring(0, 80) + '...' : fullDesc;
      description.textContent = shortDesc;
      description.title = fullDesc; // Basic tooltip fallback

      const btn = document.createElement("button");
      btn.className = "card-button";
      btn.textContent = "Kunjungi";
      btn.addEventListener("click", (e) => {
        e.stopPropagation();
        tampilkanIframe(item.url, item.hash);
      });

      // Info button
      const infoBtn = document.createElement('button');
      infoBtn.className = 'card-info-btn';
      infoBtn.innerHTML = 'ℹ';
      infoBtn.title = 'Lihat detail lengkap';
      infoBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        tampilkanInfoModal(item.nama, fullDesc, item.bidang || 'Layanan Umum');
      });

      card.appendChild(logoWrapper);
      card.appendChild(title);
      card.appendChild(description);
      card.appendChild(btn);
      
      cardWrapper.appendChild(card);
      cardWrapper.appendChild(infoBtn);

      // Custom tooltip for desktop
      if (fullDesc.length > 80) {
        createCustomTooltip(cardWrapper, fullDesc);
      }

      grid.appendChild(cardWrapper);
    });
}

// Legacy function for compatibility
function tampilkanLayananKeGrid(gridId, data) {
  tampilkanLayananKeGridSafe(gridId, data);
}

// Custom tooltip system
function createCustomTooltip(element, text) {
  element.addEventListener('mouseenter', (e) => {
    if (window.innerWidth < 768) return; // No tooltip on mobile
    
    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    tooltip.textContent = text;
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    const tooltipRect = tooltip.getBoundingClientRect();
    
    let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
    let top = rect.top - tooltipRect.height - 10;
    
    // Keep tooltip in viewport
    if (left < 10) left = 10;
    if (left + tooltipRect.width > window.innerWidth - 10) {
      left = window.innerWidth - tooltipRect.width - 10;
    }
    if (top < 10) {
      top = rect.bottom + 10;
      tooltip.classList.add('bottom');
    }
    
    tooltip.style.left = left + 'px';
    tooltip.style.top = top + 'px';
    
    requestAnimationFrame(() => tooltip.classList.add('show'));
  });
  
  element.addEventListener('mouseleave', () => {
    const tooltip = document.querySelector('.custom-tooltip');
    if (tooltip) {
      tooltip.classList.remove('show');
      setTimeout(() => tooltip.remove(), 200);
    }
  });
}

// Info modal function
window.tampilkanInfoModal = function(nama, deskripsi, bidang) {
  const modal = createInfoModal(nama, deskripsi, bidang);
  document.body.appendChild(modal);
  
  // Animate in
  requestAnimationFrame(() => {
    modal.classList.add('show');
  });
  
  // Close handlers
  const closeBtn = modal.querySelector('.info-modal-close');
  const overlay = modal.querySelector('.info-modal-overlay');
  
  const closeModal = () => {
    modal.classList.remove('show');
    setTimeout(() => {
      if (document.body.contains(modal)) {
        document.body.removeChild(modal);
      }
    }, 300);
  };
  
  closeBtn.onclick = closeModal;
  overlay.onclick = closeModal;
  
  // Escape key
  const escapeHandler = (e) => {
    if (e.key === 'Escape') {
      closeModal();
      document.removeEventListener('keydown', escapeHandler);
    }
  };
  document.addEventListener('keydown', escapeHandler);
};

function createInfoModal(nama, deskripsi, bidang) {
  const modal = document.createElement('div');
  modal.className = 'info-modal';
  modal.innerHTML = `
    <div class="info-modal-overlay"></div>
    <div class="info-modal-content">
      <button class="info-modal-close">&times;</button>
      <h3 class="info-modal-title">${nama}</h3>
      <div class="info-modal-badge">${bidang || 'Layanan Umum'}</div>
      <div class="info-modal-description">${deskripsi || 'Tidak ada deskripsi tersedia'}</div>
    </div>
  `;
  return modal;
}

function filterLayanan(gridId, keyword) {
  const data = gridId === 'internalGrid' ? internalData : publikData;
  const filteredData = data.filter(item => 
    (item.nama && item.nama.toLowerCase().includes(keyword.toLowerCase())) ||
    (item.deskripsi && item.deskripsi.toLowerCase().includes(keyword.toLowerCase())) ||
    (item.bidang && item.bidang.toLowerCase().includes(keyword.toLowerCase()))
  );
  tampilkanLayananKeGridSafe(gridId, filteredData);
  initTooltipSystem(); // Re-init tooltip after search
}

function initTooltipSystem() {
  // Remove existing tooltips
  document.querySelectorAll('.custom-tooltip').forEach(tip => tip.remove());
}

// Enhanced iframe function with fallback mechanism
function tampilkanIframe(url, hash) {
  const iframe = document.getElementById('iframeViewer');
  const container = document.getElementById('iframeContainer');
  const closeBtn = document.getElementById('iframeCloseBtn');
  const loader = document.getElementById('iframeLoader');

  if (!iframe || !container) {
    console.error('Iframe elements not found');
    return;
  }

  // Validate hash against original data from server
  const semuaData = [...internalData, ...publikData];
  const valid = semuaData.some(item => item.url === url && item.hash === hash);
  if (!valid) {
    alert("URL tidak valid atau telah dimodifikasi!");
    return;
  }

  // Show loading state
  if (loader) loader.style.display = 'block';
  
  // Create fallback notification
  const fallbackNotification = createFallbackNotification();
  
  // Set up iframe with enhanced error handling
  iframe.src = url;
  container.style.display = 'flex';
  document.body.classList.add('no-scroll');
  document.documentElement.classList.add('no-scroll');
  window.scrollTo({ top: 0, behavior: 'smooth' });

  if (closeBtn) closeBtn.onclick = tutupIframe;

  // Enhanced load handling with timeout
  let loadTimeout;
  let hasLoaded = false;

  // Set timeout for detecting connection issues
  loadTimeout = setTimeout(() => {
    if (!hasLoaded) {
      console.log('Iframe load timeout - showing fallback option');
      showFallbackOption(url, fallbackNotification);
    }
  }, 8000); // 8 second timeout

  iframe.onload = () => {
    hasLoaded = true;
    clearTimeout(loadTimeout);
    if (loader) loader.style.display = 'none';
    
    // Check if iframe actually loaded content or shows error
    setTimeout(() => {
      try {
        // Try to access iframe content to check for errors
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        // If we can't access or if it's about:blank, it might be blocked
        if (!iframeDoc || iframeDoc.location.href === 'about:blank') {
          showFallbackOption(url, fallbackNotification);
        }
      } catch (e) {
        // Cross-origin or blocked - show fallback
        console.log('Cross-origin or blocked content detected');
        showFallbackOption(url, fallbackNotification);
      }
    }, 1000);
  };

  iframe.onerror = () => {
    hasLoaded = false;
    clearTimeout(loadTimeout);
    if (loader) loader.style.display = 'none';
    console.error('Iframe failed to load:', url);
    showFallbackOption(url, fallbackNotification);
  };

  // Handle iframe load errors and X-Frame-Options blocks
  iframe.addEventListener('load', () => {
    setTimeout(() => {
      try {
        // Additional check for common error scenarios
        if (iframe.contentWindow.location.href === 'about:blank' || 
            iframe.contentDocument?.title?.includes('refused to connect') ||
            iframe.contentDocument?.body?.innerHTML?.includes('ERR_CONNECTION_REFUSED')) {
          showFallbackOption(url, fallbackNotification);
        }
      } catch (e) {
        // This is expected for cross-origin, but we can still show fallback
        // Do nothing here as this is normal for many external sites
      }
    }, 2000);
  });

  const escapeListener = e => { if (e.key === 'Escape') tutupIframe(); };
  document.addEventListener('keydown', escapeListener);
  container.escapeListener = escapeListener;
}

// Create fallback notification UI
function createFallbackNotification() {
  const notification = document.createElement('div');
  notification.className = 'iframe-fallback-notification';
  notification.innerHTML = `
    <div class="fallback-content">
      <div class="fallback-icon">⚠️</div>
      <h3>Koneksi Terblokir</h3>
      <p>Website ini tidak dapat dibuka dalam frame. Klik tombol di bawah untuk membuka di tab baru.</p>
      <div class="fallback-buttons">
        <button class="fallback-open-btn">Buka di Tab Baru</button>
        <button class="fallback-close-btn">Tutup</button>
      </div>
    </div>
  `;
  return notification;
}

// Show fallback option when iframe fails
function showFallbackOption(url, notification) {
  const container = document.getElementById('iframeContainer');
  if (!container || container.querySelector('.iframe-fallback-notification')) return;
  
  // Add notification to container
  container.appendChild(notification);
  
  // Set up event listeners
  const openBtn = notification.querySelector('.fallback-open-btn');
  const closeBtn = notification.querySelector('.fallback-close-btn');
  
  openBtn.addEventListener('click', () => {
    // Open in new tab
    window.open(url, '_blank', 'noopener,noreferrer');
    tutupIframe();
  });
  
  closeBtn.addEventListener('click', () => {
    tutupIframe();
  });

  // Auto-hide loader
  const loader = document.getElementById('iframeLoader');
  if (loader) loader.style.display = 'none';
}

function tutupIframe() {
  const iframe = document.getElementById('iframeViewer');
  const container = document.getElementById('iframeContainer');
  const loader = document.getElementById('iframeLoader');

  if (!iframe || !container) return;

  iframe.src = '';
  container.style.display = 'none';
  if (loader) loader.style.display = 'none';

  // Remove fallback notification if exists
  const notification = container.querySelector('.iframe-fallback-notification');
  if (notification) {
    notification.remove();
  }

  document.body.classList.remove('no-scroll');
  document.documentElement.classList.remove('no-scroll');

  if (container.escapeListener) {
    document.removeEventListener('keydown', container.escapeListener);
    delete container.escapeListener;
  }
  iframe.onload = null;
  iframe.onerror = null;
}

// Rest of the file remains the same...
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

  // Load internal highlight (untuk ditampilkan)
  fetch('api/get_layanan.php?jenis=internal&highlight=1')
    .then(res => {
      if (!res.ok) throw new Error("Gagal memuat data layanan internal.");
      return res.json();
    })
    .then(data => {
      if (!Array.isArray(data)) throw new Error("Data bukan array");
      internalData = data;
      tampilkanLayananKeGridSafe('internalGrid', internalData);
      initTooltipSystem();
    })
    .catch(err => {
      console.error('Gagal memuat layanan internal:', err);
      const grid = document.getElementById('internalGrid');
      if (grid) {
        grid.innerHTML = "<p style='color:red; text-align:center; padding:20px;'>Terjadi kesalahan saat mengambil data layanan internal.</p>";
      }
    });

  // Load total internal (hanya untuk jumlah)
  fetch('api/get_layanan.php?jenis=internal')
    .then(res => {
      if (!res.ok) throw new Error("Gagal memuat data layanan internal.");
      return res.json();
    })
    .then(allData => {
      if (!Array.isArray(allData)) throw new Error("Data bukan array");
      const countEl = document.getElementById("countInternal");
      if (countEl) {
        countEl.textContent = `Jumlah Layanan: ${allData.length}`;
      }
    })
    .catch(err => console.error('Gagal menghitung jumlah layanan internal:', err));

  // Load public highlight (untuk ditampilkan)
  fetch('api/get_layanan.php?jenis=publik&highlight=1')
    .then(res => {
      if (!res.ok) throw new Error("Gagal memuat data layanan publik.");
      return res.json();
    })
    .then(data => {
      if (!Array.isArray(data)) throw new Error("Data bukan array");
      publikData = data;
      tampilkanLayananKeGridSafe('publikGrid', publikData);
      initTooltipSystem();
    })
    .catch(err => {
      console.error('Gagal memuat layanan publik:', err);
      const grid = document.getElementById('publikGrid');
      if (grid) {
        grid.innerHTML = "<p style='color:red; text-align:center; padding:20px;'>Terjadi kesalahan saat mengambil data layanan publik.</p>";
      }
    });

  // Load total public (hanya untuk jumlah)
  fetch('api/get_layanan.php?jenis=publik')
    .then(res => {
      if (!res.ok) throw new Error("Gagal memuat data layanan publik.");
      return res.json();
    })
    .then(allData => {
      if (!Array.isArray(allData)) throw new Error("Data bukan array");
      const countEl = document.getElementById("countPublik");
      if (countEl) {
        countEl.textContent = `Jumlah Layanan: ${allData.length}`;
      }
    })
    .catch(err => console.error('Gagal menghitung jumlah layanan publik:', err));

  // --- Back to Top Button ---
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

  // --- Hero Slider (fetch from database via API) ---
  fetch('api/get_slider.php')
    .then(res => res.json())
    .then(data => initSlider(data))
    .catch(err => console.error("Gagal memuat slider:", err));
});

// Click outside to close iframe
document.addEventListener('click', e => {
  const container = document.getElementById('iframeContainer');
  if (container && container.style.display !== 'none' && e.target === container) {
    tutupIframe();
  }
});

// Back button handler
window.addEventListener('popstate', () => {
  const container = document.getElementById('iframeContainer');
  if (container && container.style.display !== 'none') {
    tutupIframe();
  }
});

// --- Dynamic Slider Function ---
function initSlider(slidesData) {
  const container = document.getElementById("sliderContainer");
  const dotsContainer = document.querySelector(".hero-slider .dots");
  let current = 0, autoPlay;

  if (!container || !slidesData.length) return;

  slidesData.forEach((item, i) => {
    const slide = document.createElement("div");
    slide.className = "slide" + (i===0 ? " active" : "");
    slide.style.backgroundImage = `url(${item.gambar})`;

    // No title needed, just image
    slide.innerHTML = `<div class="hero-content"></div>`;
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

  const nextBtn = document.querySelector(".next");
  const prevBtn = document.querySelector(".prev");
  
  if (nextBtn) nextBtn.onclick = () => { nextSlide(); resetAutoPlay(); };
  if (prevBtn) prevBtn.onclick = () => { prevSlide(); resetAutoPlay(); };

  startAutoPlay();
}