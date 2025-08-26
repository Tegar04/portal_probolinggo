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

  // ✅ Cek CSRF
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Token CSRF tidak valid.";
    header("Location: tambah.php?jenis=" . urlencode($_POST['jenis'] ?? 'publik'));
    exit;
  }

  // Ambil dan sanitasi input
  $nama = trim($_POST['nama'] ?? '');
  $jenis = $_POST['jenis'] ?? '';
  $bidang_in = trim($_POST['bidang'] ?? '');
  $deskripsi = trim($_POST['deskripsi'] ?? '');
  $url = trim($_POST['url'] ?? '');
  $highlight = isset($_POST['highlight']) ? 1 : 0;

  // Validasi nilai 'jenis'
  if (!in_array($jenis, ['publik', 'internal'])) {
    $_SESSION['error'] = "Jenis layanan tidak valid.";
    header("Location: tambah.php?jenis=" . urlencode($jenis));
    exit;
  }

  // Validasi input wajib
  if ($nama === '' || $url === '' || $bidang_in === '' || $deskripsi === '') {
    $_SESSION['error'] = "Semua field wajib diisi.";
    header("Location: tambah.php?jenis=" . urlencode($jenis));
    exit;
  }

  // Validasi panjang field
  if (strlen($nama) > 100 || strlen($bidang_in) > 100 || strlen($url) > 255 || strlen($deskripsi) > 500) {
    $_SESSION['error'] = "Input terlalu panjang.";
    header("Location: tambah.php?jenis=" . urlencode($jenis));
    exit;
  }

  // Validasi URL
  if (!filter_var($url, FILTER_VALIDATE_URL)) {
    $_SESSION['error'] = "URL tidak valid.";
    header("Location: tambah.php?jenis=" . urlencode($jenis));
    exit;
  }

  // 🔹 Normalisasi bidang (huruf depan kapital)
  $bidang_in = ucfirst(strtolower($bidang_in));

  // 🔹 Cek apakah bidang sudah ada (case-insensitive)
  $stmt = $conn->prepare("SELECT bidang FROM layanan WHERE LOWER(bidang) = LOWER(?) LIMIT 1");
  $stmt->bind_param("s", $bidang_in);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) {
    $bidang = $row['bidang']; // gunakan yang sudah ada
  } else {
    $bidang = $bidang_in; // pakai hasil normalisasi
  }
  $stmt->close();

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

    $logo_name = uniqid('logo_', true) . '.' . $ext;
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

  // Simpan ke database (✅ highlight = integer, tambah deskripsi)
  $query = "INSERT INTO layanan (nama, jenis, url, logo, highlight, bidang, deskripsi) VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($query);
  $logo_name = basename($logo_path);
  $stmt->bind_param("sssssss", $nama, $jenis, $url, $logo_name, $highlight, $bidang, $deskripsi);

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