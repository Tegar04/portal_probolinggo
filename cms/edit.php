<?php
session_start();
$timeout_duration = 900;

if (!isset($_SESSION['login'])) {
  header("Location: login.php");
  exit;
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
  session_unset();
  session_destroy();
  header("Location: login.php?timeout=1");
  exit;
}

$_SESSION['last_activity'] = time();

include '../db/koneksi.php';

$id = intval($_GET['id'] ?? 0);
$jenis = $_GET['jenis'] ?? '';

$query = mysqli_query($conn, "SELECT * FROM layanan WHERE id = $id");
$data = mysqli_fetch_assoc($query);

if (!$data) {
  die("Data tidak ditemukan.");
}

// Ambil jenis dari DB jika tidak ada di URL
if (!$jenis) {
  $jenis = $data['jenis'];
}

// Validasi jenis
if (!in_array($jenis, ['publik', 'internal'])) {
  die("Jenis layanan tidak valid.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Layanan</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/cms/css/tambah.css">
</head>
<body>
<div class="main-container">
  <aside class="sidebar">
    <div>
      <div class="logo-wrapper">
        <a href="index.php">
            <img src="../assets/logo/probolinggo-logo.png" alt="Logo" class="logo-img" style="cursor:pointer;">
        </a>
      </div>
      <a href="index.php" class="<?= $jenis === '' ? 'active' : '' ?>">Semua Layanan</a>
      <a href="index.php?jenis=publik" class="<?= $jenis === 'publik' ? 'active' : '' ?>">Layanan Publik</a>
      <a href="index.php?jenis=internal" class="<?= $jenis === 'internal' ? 'active' : '' ?>">Aplikasi Internal</a>
    </div>
    <a href="logout.php" class="logout-link">Logout</a>
  </aside>

  <main class="main-content">
    <h1>Edit Layanan</h1>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert-box">
        <?= htmlspecialchars($_SESSION['error']) ?>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="update.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= $data['id'] ?>">
      <input type="hidden" name="jenis" value="<?= htmlspecialchars($jenis) ?>">

      <div class="form-group">
        <label for="nama">Nama Layanan</label>
        <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required>
      </div>

      <div class="form-group">
        <label for="bidang">Bidang</label>
        <input type="text" id="bidang" name="bidang" value="<?= htmlspecialchars($data['bidang']) ?>" required>
      </div>

      <div class="form-group">
        <label for="url">URL Tujuan</label>
        <input type="text" id="url" name="url" value="<?= htmlspecialchars($data['url']) ?>" required>
      </div>

      <div class="form-group">
        <label for="logo">Ganti Logo (opsional)</label>
        <input type="file" id="logo" name="logo">
        <small>File gambar (jpg, jpeg, png, gif, webp). Maks. 1MB.</small>
      </div>

      <div class="form-group logo-preview">
        <label>Logo Saat Ini:</label>
        <img src="../assets/layanan/<?= htmlspecialchars($data['logo']) ?>" alt="Logo saat ini">
      </div>

      <div class="form-group checkbox">
        <label class="highlight-label">
          <input type="checkbox" name="highlight" value="1" <?= $data['highlight'] ? 'checked' : '' ?>>
          Tampilkan di homepage
        </label>
      </div>

      <button type="submit" class="btn">Simpan Perubahan</button>
    </form>
  </main>
</div>
</body>
</html>
