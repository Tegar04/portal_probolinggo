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
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Slider</title>
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
        <a href="index.php">Semua Layanan</a>
        <a href="index.php?jenis=publik">Layanan Publik</a>
        <a href="index.php?jenis=internal">Aplikasi Internal</a>
        <a href="slider.php" class="active">Slider Header</a>
      </div>
      <a href="logout.php" class="logout-link">Logout</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
      <h1>Tambah Slider</h1>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert-box">
          <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>

      <form action="slider_tambah_proses.php" method="POST" enctype="multipart/form-data">

        <div class="form-group">
          <label for="judul">Judul Slider</label>
          <input type="text" name="judul" id="judul" maxlength="255" required>
        </div>

        <div class="form-group">
          <label for="gambar">Upload Gambar</label>
          <input type="file" name="gambar" id="gambar" accept=".jpg,.jpeg,.png,.gif,.webp" required>
          <small>File gambar (jpg, jpeg, png, gif, webp). Maks. 2MB.</small>
        </div>

        <div class="form-group">
          <label for="urutan">Urutan</label>
          <input type="number" name="urutan" id="urutan" min="1" value="1" required>
        </div>

        <div class="form-group checkbox">
          <label class="highlight-label">
            <input type="checkbox" name="status" value="1" checked> Aktif
          </label>
        </div>

        <!-- ✅ CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <button type="submit" class="btn">Simpan</button>
      </form>
    </main>
  </div>
</body>
</html>
