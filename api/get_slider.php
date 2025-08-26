<?php
include '../db/koneksi.php';
header('Content-Type: application/json');

// Ambil slider aktif
$query = mysqli_query($conn, "SELECT * FROM slider WHERE status=1 ORDER BY urutan ASC");
$data = [];

while ($row = mysqli_fetch_assoc($query)) {
  $data[] = [
    'gambar' => 'assets/slider/' . $row['gambar'],
    'urutan' => (int)$row['urutan'],
    'status' => (int)$row['status']
  ];
}

// Jika kosong, kasih fallback
if (empty($data)) {
  $data[] = [
    'gambar' => 'assets/slider/default-slider.jpg',
    'urutan' => 1,
    'status' => 1
  ];
}

echo json_encode($data);
