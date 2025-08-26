<?php
session_start();
include '../db/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: slider_tambah.php");
  exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
  $_SESSION['error'] = "Token tidak valid!";
  header("Location: slider_tambah.php");
  exit;
}

$urutan = (int) $_POST['urutan'];
$status = isset($_POST['status']) ? 1 : 0;

if (!empty($_POST['cropped_image'])) {
  $imgData = $_POST['cropped_image'];
  $imgData = str_replace('data:image/png;base64,', '', $imgData);
  $imgData = str_replace(' ', '+', $imgData);
  $decoded = base64_decode($imgData);

  $fileName = time() . ".png";
  $targetDir = "../assets/slider/";
  $filePath = $targetDir . $fileName;

  if (file_put_contents($filePath, $decoded)) {
    $query = "INSERT INTO slider (gambar, urutan, status) VALUES ('$fileName', $urutan, $status)";
    if (mysqli_query($conn, $query)) {
      header("Location: slider.php?success=1");
      exit;
    } else {
      $_SESSION['error'] = "Gagal menambahkan slider: " . mysqli_error($conn);
    }
  } else {
    $_SESSION['error'] = "Gagal menyimpan gambar.";
  }
} else {
  $_SESSION['error'] = "Gambar wajib dicrop sebelum disimpan.";
}

header("Location: slider_tambah.php");
exit;
