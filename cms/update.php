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

// Sanitasi input
$id = intval($_POST['id'] ?? 0);
$nama = trim($_POST['nama'] ?? '');
$url = trim($_POST['url'] ?? '');
$jenis = $_POST['jenis'] ?? '';
$bidang_in = trim($_POST['bidang'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');
$highlight = isset($_POST['highlight']) ? 1 : 0;

if (!in_array($jenis, ['publik', 'internal'])) {
  $_SESSION['error'] = "Jenis layanan tidak valid.";
  header("Location: edit.php?id=$id&jenis=" . urlencode($jenis));
  exit;
}

// Validasi input wajib
if ($nama === '' || $url === '' || $bidang_in === '' || $deskripsi === '') {
  $_SESSION['error'] = "Semua field wajib diisi.";
  header("Location: edit.php?id=$id&jenis=" . urlencode($jenis));
  exit;
}

// Validasi panjang field
if (strlen($nama) > 100 || strlen($bidang_in) > 100 || strlen($url) > 255 || strlen($deskripsi) > 500) {
  $_SESSION['error'] = "Input terlalu panjang.";
  header("Location: edit.php?id=$id&jenis=" . urlencode($jenis));
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

// Ambil data lama (gunakan prepared statement)
$stmt = $conn->prepare("SELECT logo FROM layanan WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
  $_SESSION['error'] = "Data tidak ditemukan.";
  header("Location: index.php?jenis=" . urlencode($jenis));
  exit;
}

$logoLama = $data['logo'] ?? '';
$logoName = $logoLama;

$uploadDir = "../assets/layanan/";

// Jika upload logo baru
if (!empty($_FILES['logo']['name'])) {
  $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
  $maxSize = 1 * 1024 * 1024;

  $fileTmp = $_FILES['logo']['tmp_name'];
  $fileName = $_FILES['logo']['name'];
  $fileSize = $_FILES['logo']['size'];
  $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

  if (!in_array($fileExt, $allowed)) {
    $_SESSION['error'] = "Format file tidak diizinkan. Hanya JPG, JPEG, PNG, GIF, WEBP.";
    header("Location: edit.php?id=$id&jenis=" . urlencode($jenis));
    exit;
  }

  if ($fileSize > $maxSize) {
    $_SESSION['error'] = "Ukuran file terlalu besar. Maksimal 1MB.";
    header("Location: edit.php?id=$id&jenis=" . urlencode($jenis));
    exit;
  }

  if (!getimagesize($fileTmp)) {
    $_SESSION['error'] = "File bukan gambar valid.";
    header("Location: edit.php?id=$id&jenis=" . urlencode($jenis));
    exit;
  }

  // Hapus logo lama jika ada
  if ($logoLama && file_exists($uploadDir . $logoLama)) {
    unlink($uploadDir . $logoLama);
  }

  $logoName = uniqid('logo_') . '.' . $fileExt;
  $targetPath = $uploadDir . $logoName;

  if (!move_uploaded_file($fileTmp, $targetPath)) {
    $_SESSION['error'] = "Gagal mengunggah logo.";
    header("Location: edit.php?id=$id&jenis=" . urlencode($jenis));
    exit;
  }
}

// Simpan perubahan (gunakan prepared statement)
$stmt = $conn->prepare("UPDATE layanan SET nama=?, url=?, logo=?, bidang=?, deskripsi=?, highlight=? WHERE id=?");
$stmt->bind_param("sssssii", $nama, $url, $logoName, $bidang, $deskripsi, $highlight, $id);

if ($stmt->execute()) {
  header("Location: index.php?jenis=" . urlencode($jenis));
  exit;
} else {
  $_SESSION['error'] = "Gagal menyimpan perubahan.";
  header("Location: edit.php?id=$id&jenis=" . urlencode($jenis));
  exit;
}