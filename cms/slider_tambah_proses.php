<?php
session_start();
include '../db/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: slider_tambah.php");
  exit;
}

// ✅ Cek CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
  $_SESSION['error'] = "Token tidak valid!";
  header("Location: slider_tambah.php");
  exit;
}

$judul  = mysqli_real_escape_string($conn, $_POST['judul']);
$urutan = (int) $_POST['urutan'];
$status = isset($_POST['status']) ? 1 : 0;

// Upload gambar
if (!empty($_FILES['gambar']['name'])) {
  $targetDir  = "../assets/slider/";
  $fileName   = time() . "_" . basename($_FILES["gambar"]["name"]);
  $targetFile = $targetDir . $fileName;
  $fileType   = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

  $allowedTypes = ["jpg", "jpeg", "png", "gif", "webp"];
  if (!in_array($fileType, $allowedTypes)) {
    $_SESSION['error'] = "Format file tidak valid.";
    header("Location: slider_tambah.php");
    exit;
  }
  if ($_FILES["gambar"]["size"] > 2*1024*1024) {
    $_SESSION['error'] = "Ukuran file terlalu besar. Maks. 2MB.";
    header("Location: slider_tambah.php");
    exit;
  }
  if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $targetFile)) {
    $_SESSION['error'] = "Gagal mengupload file.";
    header("Location: slider_tambah.php");
    exit;
  }

  $gambar = $fileName;

  $query = "INSERT INTO slider (judul, gambar, urutan, status) 
            VALUES ('$judul', '$gambar', $urutan, $status)";
  if (mysqli_query($conn, $query)) {
    header("Location: slider.php?success=1");
    exit;
  } else {
    $_SESSION['error'] = "Gagal menambahkan slider: " . mysqli_error($conn);
    header("Location: slider_tambah.php");
    exit;
  }
} else {
  $_SESSION['error'] = "Gambar wajib diupload.";
  header("Location: slider_tambah.php");
  exit;
}
