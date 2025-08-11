// document.addEventListener("DOMContentLoaded", () => {
//   fetch("data/layanan.json")
//     .then(res => res.json())
//     .then(data => {
//       renderLayanan(data);
//       setupSearch(data);
//     });
// });

// function renderLayanan(data) {
//   const internalGrid = document.querySelector("#internal .grid-container");
//   const publikGrid = document.querySelector("#publik .grid-container");

//   data.forEach(item => {
//     const card = document.createElement("a");
//     card.className = "layanan-card";
//     card.href = item.url;
//     card.target = "_blank";
//     card.innerHTML = `
//       <img src="assets/layanan/${item.logo}" alt="${item.nama}" />
//       <span>${item.nama}</span>
//     `;

//     if (item.kategori === "internal") {
//       internalGrid.appendChild(card);
//     } else {
//       publikGrid.appendChild(card);
//     }
//   });
// }

// function setupSearch(data) {
//   const internalInput = document.querySelector("#internal .search-input");
//   const publikInput = document.querySelector("#publik .search-input");
//   const internalGrid = document.querySelector("#internal .grid-container");
//   const publikGrid = document.querySelector("#publik .grid-container");

//   internalInput.addEventListener("input", () => {
//     const value = internalInput.value.toLowerCase();
//     internalGrid.innerHTML = "";
//     const filtered = data.filter(d => d.kategori === "internal" && d.nama.toLowerCase().includes(value));
//     renderFiltered(filtered, internalGrid);
//   });

//   publikInput.addEventListener("input", () => {
//     const value = publikInput.value.toLowerCase();
//     publikGrid.innerHTML = "";
//     const filtered = data.filter(d => d.kategori === "publik" && d.nama.toLowerCase().includes(value));
//     renderFiltered(filtered, publikGrid);
//   });
// }

// function renderFiltered(data, container) {
//   data.forEach(item => {
//     const card = document.createElement("a");
//     card.className = "layanan-card";
//     card.href = item.url;
//     card.target = "_blank";
//     card.innerHTML = `
//       <img src="assets/layanan/${item.logo}" alt="${item.nama}" />
//       <span>${item.nama}</span>
//     `;
//     container.appendChild(card);
//   });
// }
