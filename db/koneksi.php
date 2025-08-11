<?php
$host = "localhost";
$user = "root";
$pass = ""; // default password kosong di Laragon
$db   = "portal_probolinggo";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}
?>
