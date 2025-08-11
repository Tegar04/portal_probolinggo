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

$id = intval($_POST['id'] ?? 0);
$nama = trim($_POST['nama'] ?? '');
$url = trim($_POST['url'] ?? '');
$jenis = $_POST['jenis'] ?? '';
$bidang = trim($_POST['bidang'] ?? '');
$highlight = isset($_POST['highlight']) ? 1 : 0;

if (!in_array($jenis, ['publik', 'internal'])) {
  die("Jenis layanan tidak valid.");
}

// Ambil data lama
$result = mysqli_query($conn, "SELECT logo FROM layanan WHERE id = $id");
$data = mysqli_fetch_assoc($result);
$logoLama = $data['logo'] ?? '';
$logoName = $logoLama;

$uploadDir = "../assets/layanan/";

if (isset($_FILES['logo']) && $_FILES['logo']['name']) {
  $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
  $maxSize = 1 * 1024 * 1024;

  $fileTmp = $_FILES['logo']['tmp_name'];
  $fileName = $_FILES['logo']['name'];
  $fileSize = $_FILES['logo']['size'];
  $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

  if (!in_array($fileExt, $allowed)) {
    $_SESSION['error'] = "Format file tidak diizinkan. Hanya JPG, PNG, GIF, WEBP.";
    header("Location: edit.php?id=$id&jenis=$jenis");
    exit;
  }

  if ($fileSize > $maxSize) {
    $_SESSION['error'] = "Ukuran file terlalu besar. Maksimal 1MB.";
    header("Location: edit.php?id=$id&jenis=$jenis");
    exit;
  }

  if (!getimagesize($fileTmp)) {
    $_SESSION['error'] = "File bukan gambar valid.";
    header("Location: edit.php?id=$id&jenis=$jenis");
    exit;
  }

  if (file_exists($uploadDir . $logoLama)) {
    unlink($uploadDir . $logoLama);
  }

  $logoName = uniqid('logo_') . '.' . $fileExt;
  $targetPath = $uploadDir . $logoName;

  if (!move_uploaded_file($fileTmp, $targetPath)) {
    $_SESSION['error'] = "Gagal mengunggah logo.";
    header("Location: edit.php?id=$id&jenis=$jenis");
    exit;
  }
}

// Simpan perubahan
$stmt = $conn->prepare("UPDATE layanan SET nama=?, url=?, logo=?, bidang=?, highlight=? WHERE id=?");
$stmt->bind_param("ssssii", $nama, $url, $logoName, $bidang, $highlight, $id);

if ($stmt->execute()) {
  header("Location: index.php?jenis=$jenis");
  exit;
} else {
  $_SESSION['error'] = "Gagal menyimpan perubahan.";
  header("Location: edit.php?id=$id&jenis=$jenis");
  exit;
}
