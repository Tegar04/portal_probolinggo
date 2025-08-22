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
<body>
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
        <a href="index.php?jenis=internal">Aplikasi Internal</a>
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
              <th>Judul</th>
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
            ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['judul']) ?></td>
                <td><img src="../assets/slider/<?= htmlspecialchars($row['gambar']) ?>" class="logo-preview" alt="Slider"></td>
                <td><?= (int)$row['urutan'] ?></td>
                <td><?= $row['status'] ? '✅' : '❌' ?></td>
                <td class="actions">
                  <a href="slider_edit.php?id=<?= $row['id'] ?>" class="edit">Edit</a>
                  <a href="slider_hapus.php?id=<?= $row['id'] ?>" class="delete" onclick="return confirm('Hapus slider ini?')">Delete</a>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
