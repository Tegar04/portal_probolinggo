<?php
include '../db/koneksi.php';
header('Content-Type: application/json');

$query = mysqli_query($conn, "SELECT * FROM slider WHERE status=1 ORDER BY urutan ASC");
$data = [];
while ($row = mysqli_fetch_assoc($query)) {
  $data[] = [
    'judul' => $row['judul'],
    'gambar' => 'assets/slider/' . $row['gambar']
  ];
}
echo json_encode($data);
