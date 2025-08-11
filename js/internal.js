// // File: /js/internal.js

// document.addEventListener('DOMContentLoaded', () => {
//   const bidangWrapper = document.getElementById('bidangWrapper');
//   const searchInput = document.getElementById('searchInternal');
//   const iframe = document.getElementById('iframeViewer');
//   const iframeContainer = document.getElementById('iframeContainer');
//   const btnClose = document.getElementById('btnCloseIframe');
//   const loader = document.getElementById('iframeLoader');
//   let layananList = [];

//   // Load layanan data
//   fetch('api/get_layanan.php?jenis=internal')
//     .then(res => {
//       if (!res.ok) throw new Error("Gagal memuat data layanan.");
//       return res.json();
//     })
//     .then(data => {
//       if (!Array.isArray(data)) throw new Error("Data bukan array");
//       layananList = data;
//       tampilkanPerBidang(data);
//     })
//     .catch(err => {
//       console.error('Error loading layanan:', err);
//       if (bidangWrapper) {
//         bidangWrapper.innerHTML = '<p style="color:red; text-align:center; padding:20px;">Terjadi kesalahan saat mengambil data layanan.</p>';
//       }
//     });

//   // Search functionality
//   if (searchInput) {
//     searchInput.addEventListener('input', () => {
//       const keyword = searchInput.value.toLowerCase().trim();
//       const hasilFilter = layananList.filter(item =>
//         item.nama && item.nama.toLowerCase().includes(keyword)
//       );
//       tampilkanPerBidang(hasilFilter);
//     });
//   }

//   // Close iframe button
//   if (btnClose) {
//     btnClose.addEventListener('click', tutupIframe);
//   }

//   // Handle viewport changes for mobile
//   function handleViewportChange() {
//     if (iframeContainer && iframeContainer.style.display !== 'none') {
//       setTimeout(() => {
//         window.scrollTo(0, 0);
//       }, 100);
//     }
//   }

//   // Add event listeners for orientation changes
//   window.addEventListener('orientationchange', handleViewportChange);
//   window.addEventListener('resize', handleViewportChange);

//   // Global function to show iframe
//   window.tampilkanIframe = function(url) {
//     if (!iframe || !iframeContainer) {
//       console.error('Iframe elements not found');
//       return;
//     }

//     // Optional URL validation (uncomment if needed)
//     // if (!isValidUrl(url)) {
//     //   alert("URL tidak valid atau tidak diperbolehkan.");
//     //   return;
//     // }

//     // Show loader
//     if (loader) {
//       loader.style.display = 'block';
//     }

//     // Set iframe src and show container
//     iframe.src = url;
//     iframeContainer.style.display = 'flex';

//     // Prevent body scrolling
//     document.body.classList.add('no-scroll');
//     document.documentElement.classList.add('no-scroll');

//     // Scroll to top
//     window.scrollTo({ top: 0, behavior: 'smooth' });

//     // Handle iframe load events
//     iframe.onload = function() {
//       if (loader) {
//         loader.style.display = 'none';
//       }
//     };

//     iframe.onerror = function() {
//       if (loader) {
//         loader.style.display = 'none';
//       }
//       console.error('Failed to load iframe:', url);
//     };

//     // Add escape key listener
//     const escapeListener = function(e) {
//       if (e.key === 'Escape') {
//         tutupIframe();
//       }
//     };
//     document.addEventListener('keydown', escapeListener);
    
//     // Store escape listener for cleanup
//     iframeContainer.escapeListener = escapeListener;
//   };

//   function tutupIframe() {
//     if (!iframe || !iframeContainer) {
//       return;
//     }

//     // Clear iframe and hide container
//     iframe.src = '';
//     iframeContainer.style.display = 'none';

//     // Hide loader
//     if (loader) {
//       loader.style.display = 'none';
//     }

//     // Restore body scrolling
//     document.body.classList.remove('no-scroll');
//     document.documentElement.classList.remove('no-scroll');

//     // Remove escape key listener
//     if (iframeContainer.escapeListener) {
//       document.removeEventListener('keydown', iframeContainer.escapeListener);
//       delete iframeContainer.escapeListener;
//     }

//     // Clear iframe event handlers
//     iframe.onload = null;
//     iframe.onerror = null;
//   }

//   function tampilkanPerBidang(data) {
//     if (!bidangWrapper) return;

//     bidangWrapper.innerHTML = '';
    
//     if (!Array.isArray(data) || data.length === 0) {
//       bidangWrapper.innerHTML = '<p style="text-align:center; padding:20px; color:#666;">Tidak ada layanan yang ditemukan.</p>';
//       return;
//     }

//     const bidangMap = {};

//     // Group data by bidang
//     data.forEach(item => {
//       if (!item || !item.nama) return;
//       const bidang = item.bidang?.trim() || '';
//       if (!bidangMap[bidang]) bidangMap[bidang] = [];
//       bidangMap[bidang].push(item);
//     });

//     // Render Layanan Umum first (empty bidang)
//     if (bidangMap['']) {
//       bidangWrapper.appendChild(renderBidangSection('Layanan Umum', bidangMap['']));
//       delete bidangMap[''];
//     }

//     // Render other bidang sections
//     Object.keys(bidangMap).sort().forEach(bidang => {
//       bidangWrapper.appendChild(renderBidangSection(bidang, bidangMap[bidang]));
//     });
//   }

//   function renderBidangSection(judul, layanan) {
//     const section = document.createElement('div');
//     section.classList.add('bidang-section');

//     const title = document.createElement('h3');
//     title.className = 'bidang-title';
//     title.textContent = judul;

//     const grid = document.createElement('div');
//     grid.className = 'grid-container';

//     grid.innerHTML = layanan
//       .filter(item => item && item.nama && item.url)
//       .map(item => `
//         <a class="layanan-card" href="javascript:void(0);" onclick="tampilkanIframe('${item.url}')" title="${item.nama}">
//           <div class="logo-wrapper">
//             <img src="assets/layanan/${item.logo}" alt="${item.nama}" onerror="this.src='assets/logo/default.png'" />
//           </div>
//           <span>${item.nama}</span>
//         </a>
//       `).join('');

//     section.appendChild(title);
//     section.appendChild(grid);
//     return section;
//   }

//   function isValidUrl(url) {
//     try {
//       const trustedDomains = ['probolinggokota.go.id', 'localhost'];
//       const parsed = new URL(url);
//       return trustedDomains.some(domain => parsed.hostname.includes(domain));
//     } catch (e) {
//       return false;
//     }
//   }

//   // Handle back button for iframe
//   window.addEventListener('popstate', function(e) {
//     if (iframeContainer && iframeContainer.style.display !== 'none') {
//       tutupIframe();
//     }
//   });

//   // Add click outside to close iframe (optional)
//   document.addEventListener('click', function(e) {
//     if (iframeContainer && iframeContainer.style.display !== 'none') {
//       // Check if click is outside iframe but inside container (on overlay)
//       if (e.target === iframeContainer) {
//         tutupIframe();
//       }
//     }
//   });
// });