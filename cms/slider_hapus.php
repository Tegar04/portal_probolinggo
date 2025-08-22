<?php
session_start();
include '../db/koneksi.php';

// Pastikan login
if (!isset($_SESSION['login'])) {
  header("Location: login.php");
  exit;
}

// Cek parameter ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: slider.php?error=ID tidak valid");
  exit;
}

$id = (int) $_GET['id'];

// Ambil nama file gambar dulu supaya bisa dihapus dari folder
$result = mysqli_query($conn, "SELECT gambar FROM slider WHERE id=$id");
$row = mysqli_fetch_assoc($result);

if ($row) {
  $gambarPath = "../assets/slider/" . $row['gambar'];
  
  // Hapus data di database
  $delete = mysqli_query($conn, "DELETE FROM slider WHERE id=$id");

  if ($delete) {
    // Jika ada file gambar dan benar-benar ada → hapus juga dari server
    if (file_exists($gambarPath)) {
      unlink($gambarPath);
    }
    header("Location: slider.php?success=1");
    exit;
  } else {
    header("Location: slider.php?error=Gagal menghapus slider");
    exit;
  }
} else {
  header("Location: slider.php?error=Slider tidak ditemukan");
  exit;
}
