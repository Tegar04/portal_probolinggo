<?php

// Atur parameter cookie sebelum session_start()
session_set_cookie_params([
    'lifetime' => 0, // Session akan hilang setelah browser ditutup
    'secure' => isset($_SERVER['HTTPS']), // Hanya kirim cookie lewat HTTPS
    'httponly' => true, // Tidak bisa diakses via JavaScript
    'samesite' => 'Strict' // Cegah CSRF lintas situs
]);

session_start();

// Security headers dasar
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include '../db/koneksi.php';

// Fungsi IP
function get_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

$ip_address = get_ip();
$current_time = time();
$lock_remaining = 0;

$stmt = $conn->prepare("SELECT * FROM login_attempts WHERE ip_address = ? LIMIT 1");
$stmt->bind_param("s", $ip_address);
$stmt->execute();
$result_attempt = $stmt->get_result();
$attempt_data = $result_attempt->fetch_assoc();

if (!$attempt_data) {
    $stmt = $conn->prepare("INSERT INTO login_attempts (ip_address, attempts, lock_stage, lock_until, last_attempt) VALUES (?, 0, 0, 0, ?)");
    $stmt->bind_param("si", $ip_address, $current_time);
    $stmt->execute();
    $attempt_data = ['attempts' => 0, 'lock_stage' => 0, 'lock_until' => 0];
}

if ($current_time < $attempt_data['lock_until']) {
    $lock_remaining = $attempt_data['lock_until'] - $current_time;
    $error = "Terlalu banyak percobaan gagal. Akun terkunci selama " . ceil($lock_remaining / 60) . " menit.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $lock_remaining <= 0) {
    // CSRF check
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Permintaan tidak valid.";
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if ($data && password_verify($password, $data['password'])) {
            session_regenerate_id(true);
            $_SESSION['login'] = true;
            $_SESSION['last_activity'] = time();
            $_SESSION['username'] = $data['username'];
            $_SESSION['user_id'] = $data['id'];

            $stmt = $conn->prepare("UPDATE login_attempts SET attempts=0, lock_stage=0, lock_until=0 WHERE ip_address=?");
            $stmt->bind_param("s", $ip_address);
            $stmt->execute();

            header("Location: index.php");
            exit;
        } else {
            $new_attempts = $attempt_data['attempts'] + 1;
            $lock_stage = $attempt_data['lock_stage'];
            $lock_until = 0;

            if ($new_attempts >= 3) {
                $new_attempts = 0;
                $lock_stage++;

                if ($lock_stage == 1) {
                    $lock_until = $current_time + 60;
                } elseif ($lock_stage == 2) {
                    $lock_until = $current_time + (5 * 60);
                } else {
                    $lock_stage = 3;
                    $lock_until = $current_time + (10 * 60);
                }

                $lock_remaining = $lock_until - $current_time;
                $error = "Terlalu banyak percobaan gagal. Akun terkunci selama " . ceil($lock_remaining / 60) . " menit.";
            } else {
                $error = "Email atau password salah! Percobaan ke {$new_attempts} dari 3.";
            }

            $stmt = $conn->prepare("UPDATE login_attempts SET attempts=?, lock_stage=?, lock_until=?, last_attempt=? WHERE ip_address=?");
            $stmt->bind_param("iiiis", $new_attempts, $lock_stage, $lock_until, $current_time, $ip_address);
            $stmt->execute();
        }
    }
}

// Helper escape output
function e($str) {
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Portal Layanan</title>
  <link rel="stylesheet" href="/cms/css/login.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <div class="login-page">
    <div class="login-header">
      <img src="../assets/logo/probolinggo-logo.png" alt="Logo Kota" class="logo">
    </div>

    <div class="login-card" id="loginCard">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

        <?php if (isset($error)): ?>
          <p id="errorMsg" class="error-msg"><?= e($error) ?></p>
          <?php if ($lock_remaining > 0): ?>
            <div id="countdown"></div>
          <?php endif; ?>
        <?php elseif (isset($_GET['timeout'])): ?>
          <p class="error-msg">Sesi Anda telah berakhir. Silakan login kembali.</p>
        <?php endif; ?>

        <label>
          <i class="icon">&#128100;</i>
          <input type="text" id="usernameInput" name="username" placeholder="Email address" required>
        </label>

        <label>
          <i class="icon">&#128274;</i>
          <input type="password" name="password" placeholder="Password" required>
        </label>

        <button id="loginBtn" type="submit" <?= ($lock_remaining > 0) ? 'disabled' : '' ?>>Sign in</button>
      </form>
    </div>
  </div>

  <?php if ($lock_remaining > 0): ?>
  <script>
    let remaining = <?= (int)$lock_remaining ?>;
    const btn = document.getElementById('loginBtn');
    const countdownEl = document.getElementById('countdown');
    const errorEl = document.getElementById('errorMsg');
    const usernameInput = document.getElementById('usernameInput');
    const loginCard = document.getElementById('loginCard');

    function updateCountdown() {
      if (remaining <= 0) {
        btn.disabled = false;
        if (countdownEl) countdownEl.style.display = 'none';
        if (errorEl) errorEl.style.display = 'none';
        if (loginCard) loginCard.classList.add('error-cleared');
        usernameInput.focus();
        return;
      }
      let minutes = Math.floor(remaining / 60);
      let seconds = remaining % 60;
      countdownEl.innerText = `Tunggu ${minutes}:${seconds.toString().padStart(2, '0')} sebelum mencoba lagi`;
      remaining--;
      setTimeout(updateCountdown, 1000);
    }
    updateCountdown();
  </script>
  <?php endif; ?>
</body>
</html>
