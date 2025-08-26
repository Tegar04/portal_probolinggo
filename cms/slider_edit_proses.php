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

// Jika ada gambar baru yang di-crop
if (!empty($_POST['cropped_image'])) {
  $imgData = $_POST['cropped_image'];
  $imgData = str_replace('data:image/png;base64,', '', $imgData);
  $imgData = str_replace(' ', '+', $imgData);
  $decoded = base64_decode($imgData);

  $fileName = time() . ".png";
  $targetDir = "../assets/slider/";
  $filePath = $targetDir . $fileName;

  if (file_put_contents($filePath, $decoded)) {
    // Hapus gambar lama
    if (!empty($slider['gambar']) && file_exists("../assets/slider/" . $slider['gambar'])) {
      unlink("../assets/slider/" . $slider['gambar']);
    }
    $gambar = $fileName;
  } else {
    $_SESSION['error'] = "Gagal menyimpan gambar baru.";
    header("Location: slider_edit.php?id=$id");
    exit;
  }
}

// Update ke DB
$query = "UPDATE slider SET gambar='$gambar', urutan=$urutan, status=$status WHERE id=$id";
if (mysqli_query($conn, $query)) {
  header("Location: slider.php?updated=1");
  exit;
} else {
  $_SESSION['error'] = "Gagal update slider: " . mysqli_error($conn);
  header("Location: slider_edit.php?id=$id");
  exit;
}
