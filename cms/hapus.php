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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$jenis = $_GET['jenis'] ?? 'publik';

if ($id > 0) {
  // Ambil nama logo dulu sebelum hapus
  $result = mysqli_query($conn, "SELECT logo FROM layanan WHERE id = $id");
  $row = mysqli_fetch_assoc($result);
  $logoName = $row['logo'];
  $logoPath = "../assets/layanan/" . $logoName;

  // Hapus file logo dari direktori jika ada
  if (file_exists($logoPath)) {
    unlink($logoPath);
  }

  // Hapus dari database
  $query = "DELETE FROM layanan WHERE id = $id";
  mysqli_query($conn, $query);
}

header("Location: index.php?jenis=" . urlencode($jenis));
exit;
