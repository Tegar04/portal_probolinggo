<?php
session_start();
include '../db/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: slider.php");
  exit;
}

// ✅ CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
  $_SESSION['error'] = "Token tidak valid!";
  header("Location: slider.php");
  exit;
}

$id     = (int) $_POST['id'];
$judul  = mysqli_real_escape_string($conn, $_POST['judul']);
$urutan = (int) $_POST['urutan'];
$status = isset($_POST['status']) ? 1 : 0;

// Ambil data lama
$result = mysqli_query($conn, "SELECT * FROM slider WHERE id=$id");
$slider = mysqli_fetch_assoc($result);
if (!$slider) {
  $_SESSION['error'] = "Slider tidak ditemukan.";
  header("Location: slider.php");
  exit;
}

$gambar = $slider['gambar'];

// Jika upload gambar baru
if (!empty($_FILES['gambar']['name'])) {
  $targetDir  = "../assets/slider/";
  $fileName   = time() . "_" . basename($_FILES["gambar"]["name"]);
  $targetFile = $targetDir . $fileName;
  $fileType   = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

  $allowedTypes = ["jpg", "jpeg", "png", "gif", "webp"];
  if (!in_array($fileType, $allowedTypes)) {
    $_SESSION['error'] = "Format file tidak valid.";
    header("Location: slider_edit.php?id=$id");
    exit;
  }
  if ($_FILES["gambar"]["size"] > 2*1024*1024) {
    $_SESSION['error'] = "Ukuran file terlalu besar. Maks. 2MB.";
    header("Location: slider_edit.php?id=$id");
    exit;
  }
  if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $targetFile)) {
    $_SESSION['error'] = "Gagal mengupload file.";
    header("Location: slider_edit.php?id=$id");
    exit;
  }

  // hapus gambar lama
  if (!empty($slider['gambar']) && file_exists("../assets/slider/" . $slider['gambar'])) {
    unlink("../assets/slider/" . $slider['gambar']);
  }

  $gambar = $fileName;
}

// Update ke DB
$query = "UPDATE slider SET judul='$judul', gambar='$gambar', urutan=$urutan, status=$status WHERE id=$id";
if (mysqli_query($conn, $query)) {
  header("Location: slider.php?updated=1");
  exit;
} else {
  $_SESSION['error'] = "Gagal update slider: " . mysqli_error($conn);
  header("Location: slider_edit.php?id=$id");
  exit;
}
