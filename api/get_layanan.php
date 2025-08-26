<?php
header('Content-Type: application/json');
include '../db/koneksi.php';

// Load secret key dari environment (bukan hardcode di file)
$secretKey = getenv('SECRET_KEY') ?: 'default_fallback_key';

// Batasi origin agar API tidak bisa diambil bebas dari luar
header("Access-Control-Allow-Origin: https://portal.probolinggokota.go.id");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

$jenis = $_GET['jenis'] ?? '';
$highlightOnly = isset($_GET['highlight']) && $_GET['highlight'] == '1';

// Validasi input jenis
if (!in_array($jenis, ['publik', 'internal'], true)) {
    echo json_encode([]);
    exit;
}

// Query data sesuai filter - tambah kolom deskripsi
if ($highlightOnly) {
    $sql = "SELECT id, nama, bidang, deskripsi, url, logo, jenis, highlight FROM layanan WHERE jenis = ? AND highlight = 1 ORDER BY id DESC";
} else {
    $sql = "SELECT id, nama, bidang, deskripsi, url, logo, jenis, highlight FROM layanan WHERE jenis = ? ORDER BY id DESC";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $jenis);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    // Sanitasi output agar aman dari XSS
    $row['nama'] = htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8');
    $row['bidang'] = htmlspecialchars($row['bidang'], ENT_QUOTES, 'UTF-8');
    $row['deskripsi'] = htmlspecialchars($row['deskripsi'] ?? '', ENT_QUOTES, 'UTF-8');
    $row['logo'] = htmlspecialchars($row['logo'], ENT_QUOTES, 'UTF-8');
    
    // Validasi dan sanitasi URL
    if (filter_var($row['url'], FILTER_VALIDATE_URL)) {
        $row['url'] = $row['url'];
    } else {
        $row['url'] = '';
    }
    
    // Buat hash verifikasi untuk URL
    $row['hash'] = hash_hmac('sha256', $row['url'], $secretKey);
    
    $data[] = $row;
}

echo json_encode($data);
?>