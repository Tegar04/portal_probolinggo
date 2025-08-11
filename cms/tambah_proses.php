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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Ambil dan sanitasi input
  $nama = trim($_POST['nama'] ?? '');
  $jenis = $_POST['jenis'] ?? '';
  $bidang = $_POST['bidang'] ?? '';
  $url = trim($_POST['url'] ?? '');
  $highlight = isset($_POST['highlight']) ? 1 : 0;

  // Validasi nilai 'jenis' harus publik atau internal
  if (!in_array($jenis, ['publik', 'internal'])) {
    $_SESSION['error'] = "Jenis layanan tidak valid.";
    header("Location: tambah.php?jenis=" . urlencode($jenis));
    exit;
  }

  // Validasi input wajib diisi
  if ($nama === '' || $url === '' || $bidang === '') {
    $_SESSION['error'] = "Semua field wajib diisi.";
    header("Location: tambah.php?jenis=" . urlencode($jenis));
    exit;
  }

  // Validasi dan proses upload logo
  $logo = $_FILES['logo']['name'] ?? '';
  $tmp = $_FILES['logo']['tmp_name'] ?? null;
  $size = $_FILES['logo']['size'] ?? 0;
  $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
  $max_size = 1 * 1024 * 1024; // 1MB
  $logo_path = '';

  if ($tmp && $logo) {
    $ext = strtolower(pathinfo($logo, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext)) {
      $_SESSION['error'] = "File logo harus berupa gambar (jpg, jpeg, png, gif, webp).";
      header("Location: tambah.php?jenis=" . urlencode($jenis));
      exit;
    }

    if ($size > $max_size) {
      $_SESSION['error'] = "Ukuran file terlalu besar. Maksimal 1MB.";
      header("Location: tambah.php?jenis=" . urlencode($jenis));
      exit;
    }

    if (!getimagesize($tmp)) {
      $_SESSION['error'] = "File bukan gambar yang valid.";
      header("Location: tambah.php?jenis=" . urlencode($jenis));
      exit;
    }

    $logo_name = uniqid('logo_') . '.' . $ext;
    $logo_path = "../assets/layanan/" . $logo_name;

    if (!move_uploaded_file($tmp, $logo_path)) {
      $_SESSION['error'] = "Gagal mengunggah file logo.";
      header("Location: tambah.php?jenis=" . urlencode($jenis));
      exit;
    }
  } else {
    $_SESSION['error'] = "Logo wajib diunggah.";
    header("Location: tambah.php?jenis=" . urlencode($jenis));
    exit;
  }

  // Simpan ke database
  $query = "INSERT INTO layanan (nama, jenis, url, logo, highlight, bidang) VALUES (?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($query);
  $logo_name = basename($logo_path);
  $stmt->bind_param("ssssss", $nama, $jenis, $url, $logo_name, $highlight, $bidang);

  if ($stmt->execute()) {
    header("Location: index.php?jenis=" . urlencode($jenis));
    exit;
  } else {
    $_SESSION['error'] = "Gagal menyimpan data ke database.";
    header("Location: tambah.php?jenis=" . urlencode($jenis));
    exit;
  }
} else {
  $_SESSION['error'] = "Akses langsung tidak diizinkan.";
  header("Location: tambah.php?jenis=publik");
  exit;
}
