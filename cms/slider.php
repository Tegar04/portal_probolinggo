<?php
session_start();
if (!isset($_SESSION['login'])) { 
  header("Location: login.php"); 
  exit; 
}
include '../db/koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Slider Header</title>
  <link rel="stylesheet" href="/cms/css/cms.css">
</head>
<body class="slider-page">
  <div class="layout">
    
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div>
        <div class="logo-wrapper">
          <a href="index.php">
            <img src="../assets/logo/probolinggo-logo.png" alt="Logo" class="logo-img" style="cursor:pointer;">
          </a>
        </div>
        <a href="index.php">Semua Layanan</a>
        <a href="index.php?jenis=publik">Layanan Publik</a>
        <a href="index.php?jenis=internal">Layanan Pemerintahan</a>
        <a href="slider.php" class="active">Slider Header</a>
      </div>
      <a href="logout.php" class="logout-link">Logout</a>
    </aside>
    
    <!-- Main Content -->
    <div class="main-content">
      <div class="top-bar">
        <h1>Kelola Slider Header</h1>
        <a href="slider_tambah.php" class="btn">Add New</a>
      </div>

      <div class="card">
        <table>
          <thead>
            <tr>
              <th>No</th>
              <th>Gambar</th>
              <th>Urutan</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $result = mysqli_query($conn, "SELECT * FROM slider ORDER BY urutan ASC");
            $no = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                $status_class = $row['status'] ? 'status-active' : 'status-inactive';
                $status_icon = $row['status'] ? '✅' : '❌';
            ?>
              <tr>
                <td data-label="No"><?= $no++ ?></td>
                <td class="thumb-cell" data-label="Gambar">
                  <img src="../assets/slider/<?= htmlspecialchars($row['gambar']) ?>" 
                      class="slider-thumb" 
                      alt="Slider <?= $no-1 ?>"
                      loading="lazy">
                </td>
                <td data-label="Urutan">
                  <span class="urutan-badge"><?= (int)$row['urutan'] ?></span>
                </td>
                <td data-label="Status">
                  <span class="<?= $status_class ?>"><?= $status_icon ?></span>
                </td>
                <td class="actions" data-label="Actions">
                  <a href="slider_edit.php?id=<?= $row['id'] ?>" class="edit" title="Edit Slider">Edit</a>
                  <a href="slider_hapus.php?id=<?= $row['id'] ?>" class="delete" 
                     onclick="return confirm('Apakah Anda yakin ingin menghapus slider ini?')" 
                     title="Hapus Slider">Delete</a>
                </td>
              </tr>
            <?php } ?>
            
            <?php if (mysqli_num_rows($result) == 0): ?>
              <tr>
                <td colspan="5" style="text-align: center; padding: 40px; color: #666;">
                  <em>Belum ada data slider. <a href="slider_tambah.php">Tambah slider pertama</a>.</em>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    // Optional: Add confirmation with better styling
    document.querySelectorAll('.delete').forEach(function(deleteBtn) {
      deleteBtn.addEventListener('click', function(e) {
        if (!confirm('Apakah Anda yakin ingin menghapus slider ini?\nTindakan ini tidak dapat dibatalkan.')) {
          e.preventDefault();
        }
      });
    });
  </script>
</body>
</html>