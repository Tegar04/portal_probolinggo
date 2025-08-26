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

// ✅ CSRF Token
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$jenis = $_GET['jenis'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Layanan</title>
  <link rel="stylesheet" href="/cms/css/tambah.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <div class="main-container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div>
        <div class="logo-wrapper">
          <a href="index.php">
            <img src="../assets/logo/probolinggo-logo.png" alt="Logo" class="logo-img" style="cursor:pointer;">
          </a>
        </div>
        <a href="index.php" class="<?= $jenis === '' ? 'active' : '' ?>">Semua Layanan</a>
        <a href="index.php?jenis=publik" class="<?= $jenis === 'publik' ? 'active' : '' ?>">Layanan Publik</a>
        <a href="index.php?jenis=internal" class="<?= $jenis === 'internal' ? 'active' : '' ?>">Layanan Pemerintahan</a>
        <a href="slider.php" class="<?= basename($_SERVER['PHP_SELF']) === 'slider.php' ? 'active' : '' ?>">Slider Header</a>
      </div>
      <a href="logout.php" class="logout-link">Logout</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
      <h1>Tambah Layanan</h1>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert-box">
          <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>

      <form action="tambah_proses.php" method="POST" enctype="multipart/form-data">

        <div class="form-group">
          <label for="nama">Nama Layanan</label>
          <input type="text" name="nama" id="nama" maxlength="100" required>
        </div>

        <div class="form-group">
          <label for="bidang">Bidang</label>
          <input type="text" name="bidang" id="bidang" maxlength="100" placeholder="Contoh: Kependudukan, Perizinan, dll" required>
        </div>

        <div class="form-group">
          <label for="deskripsi">Deskripsi Layanan</label>
          <textarea name="deskripsi" id="deskripsi" rows="4" maxlength="500" placeholder="Jelaskan secara singkat tentang layanan ini..." required></textarea>
          <small>Maksimal 500 karakter.</small>
        </div>

        <div class="form-group">
          <label for="url">URL Tujuan</label>
          <input type="url" name="url" id="url" maxlength="255" required>
        </div>

        <div class="form-group">
          <label for="logo">Upload Logo</label>
          <input type="file" name="logo" id="logo" accept=".jpg,.jpeg,.png,.gif,.webp" required>
          <small>File gambar (jpg, jpeg, png, gif, webp). Maks. 1MB.</small>
        </div>

        <div class="form-group checkbox">
          <label class="highlight-label">
            <input type="checkbox" name="highlight" value="1"> Tampilkan di homepage
          </label>
        </div>

        <input type="hidden" name="jenis" value="<?= htmlspecialchars($jenis) ?>">
        <!-- ✅ CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <button type="submit" class="btn">Simpan</button>
      </form>
    </main>
  </div>
</body>
</html>