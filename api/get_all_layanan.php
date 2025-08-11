<?php
// header('Content-Type: application/json');
// include '../db/koneksi.php';

// $jenis = $_GET['jenis'] ?? '';

// if (!in_array($jenis, ['publik', 'internal'])) {
//     echo json_encode([]);
//     exit;
// }

// $sql = "SELECT * FROM layanan WHERE jenis = ?";
// $stmt = $conn->prepare($sql);
// $stmt->bind_param("s", $jenis);
// $stmt->execute();
// $result = $stmt->get_result();

// $data = [];
// while ($row = $result->fetch_assoc()) {
//     $data[] = $row;
// }

// echo json_encode($data);
