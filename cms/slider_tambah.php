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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css"/>
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
        <a href="index.php?jenis=internal">Layanan Pemerintahan</a>
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

      <form id="sliderForm" action="slider_tambah_proses.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label for="gambar">Upload Gambar</label>
          <input type="file" name="gambar" id="gambarInput" accept="image/*" required>
          <small>File gambar (jpg, jpeg, png, gif, webp). Maks. 2MB.</small>
        </div>

        <div class="form-group">
          <label>Crop Gambar</label><br>
          <img id="preview" style="max-width:100%; display:none;">
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

        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="cropped_image" id="croppedImage">

        <button type="submit" class="btn">Simpan</button>
      </form>
    </main>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
  <script>
    let cropper;
    const input = document.getElementById('gambarInput');
    const preview = document.getElementById('preview');
    const croppedInput = document.getElementById('croppedImage');

    input.addEventListener('change', e => {
      const file = e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = function(event) {
        preview.src = event.target.result;
        preview.style.display = 'block';

        if (cropper) cropper.destroy();
        cropper = new Cropper(preview, {
          aspectRatio: 1920/600, // contoh rasio 1920x600
          viewMode: 1
        });
      };
      reader.readAsDataURL(file);
    });

    document.getElementById('sliderForm').addEventListener('submit', function(e) {
      e.preventDefault();
      if (!cropper) {
        alert("Harap pilih dan crop gambar terlebih dahulu!");
        return;
      }
      cropper.getCroppedCanvas({
        width: 1920,
        height: 600
      }).toBlob(blob => {
        const reader = new FileReader();
        reader.onloadend = function() {
          croppedInput.value = reader.result; 
          e.target.submit();
        };
        reader.readAsDataURL(blob);
      });
    });
  </script>
</body>
</html>
