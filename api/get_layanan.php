<?php
header('Content-Type: application/json');
include '../db/koneksi.php';

// Kunci rahasia untuk membuat hash, jangan share ke client!
define('SECRET_KEY', 'KUNCI_RAHASIA_SANGAT_AMAN');

$jenis = $_GET['jenis'] ?? '';
$highlightOnly = isset($_GET['highlight']) && $_GET['highlight'] == '1';

// Cek jenis request valid
if (!in_array($jenis, ['publik', 'internal'])) {
    echo json_encode([]);
    exit;
}

// Query data sesuai filter
if ($highlightOnly) {
    $sql = "SELECT * FROM layanan WHERE jenis = ? AND highlight = 1 ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM layanan WHERE jenis = ? ORDER BY id DESC";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $jenis);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    // Buat hash verifikasi untuk URL
    $row['hash'] = hash_hmac('sha256', $row['url'], SECRET_KEY);
    $data[] = $row;
}

echo json_encode($data);
