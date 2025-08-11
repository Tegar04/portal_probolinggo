<?php
include '../db/koneksi.php';

$username = 'admin@example.com';
$password_plain = 'admin123';
$password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

// Update password lama dengan hash
$update = "UPDATE admin SET password='$password_hash' WHERE username='$username'";
if (mysqli_query($conn, $update)) {
  echo "Password berhasil di-hash dan diperbarui!";
} else {
  echo "Gagal: " . mysqli_error($conn);
}
?>
