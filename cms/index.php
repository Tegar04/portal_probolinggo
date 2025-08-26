<?php
session_start();

// Auto logout jika lebih dari 15 menit tidak aktif
$timeout_duration = 900; // dalam detik (15 menit)

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

// Perbarui waktu aktivitas terakhir
$_SESSION['last_activity'] = time();

include '../db/koneksi.php';
include 'pagination.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>CMS Portal Layanan</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/cms/css/cms.css">
</head>

<body>
  <div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div>
        <div class="logo-wrapper">
          <a href="index.php">
            <img src="../assets/logo/probolinggo-logo.png" alt="Logo" class="logo-img" style="cursor:pointer;">
          </a>
        </div>
        <a href="index.php" class="<?= !isset($_GET['jenis']) ? 'active' : '' ?>">Semua Layanan</a>
        <a href="?jenis=publik" class="<?= ($_GET['jenis'] ?? '') === 'publik' ? 'active' : '' ?>">Layanan Publik</a>
        <a href="?jenis=internal" class="<?= ($_GET['jenis'] ?? '') === 'internal' ? 'active' : '' ?>">Layanan Pemerintahan</a>
        <a href="slider.php" class="<?= basename($_SERVER['PHP_SELF']) === 'slider.php' ? 'active' : '' ?>">Slider Header</a>
      </div>
      <a href="logout.php" class="logout-link">Logout</a>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
      <div class="top-bar">
        <h1>Dashboard Overview</h1>
        <div class="search-bar">
          <form method="GET">
            <?php if (isset($_GET['jenis'])): ?>
              <input type="hidden" name="jenis" value="<?= htmlspecialchars($_GET['jenis']) ?>">
            <?php endif; ?>
            <input type="text" name="search" placeholder="Cari Layanan" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit" style="display:none;">Search</button>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <?php
            $jenis = $_GET['jenis'] ?? '';
            if ($jenis === 'publik') {
              $title = 'Layanan Publik';
            } elseif ($jenis === 'internal') {
              $title = 'Aplikasi Internal';
            } else {
              $title = 'Semua Layanan';
            }
          ?>
          <h2><?= $title ?></h2>
          <?php if ($jenis !== ''): ?>
            <a href="tambah.php<?= $jenis ? '?jenis=' . $jenis : '' ?>" class="btn">Add New</a>
          <?php endif; ?>
        </div>
        <div style="overflow-x:auto;">
          <table>
            <thead>
              <tr>
                <th>No</th>
                <th>Layanan</th>
                <th>Jenis</th>
                <th>Bidang</th>
                <th>Deskripsi</th>
                <th>Logo</th>
                <th>URL</th>
                <th>Highlight</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $search = $_GET['search'] ?? '';
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = 10;
                $offset = ($page - 1) * $limit;

                // Hitung total data
                $countQuery = "SELECT COUNT(*) as total FROM layanan WHERE 1=1";
                if (in_array($jenis, ['publik', 'internal'])) {
                  $countQuery .= " AND jenis='$jenis'";
                }
                if (!empty($search)) {
                  $searchSafe = mysqli_real_escape_string($conn, $search);
                  $countQuery .= " AND (nama LIKE '%$searchSafe%' OR bidang LIKE '%$searchSafe%' OR deskripsi LIKE '%$searchSafe%')";
                }
                $result = mysqli_query($conn, $countQuery);
                $total_data = mysqli_fetch_assoc($result)['total'];

                // Ambil data layanan
                $dataQuery = "SELECT * FROM layanan WHERE 1=1";
                if (in_array($jenis, ['publik', 'internal'])) {
                  $dataQuery .= " AND jenis='$jenis'";
                }
                if (!empty($search)) {
                  $dataQuery .= " AND (nama LIKE '%$searchSafe%' OR bidang LIKE '%$searchSafe%' OR deskripsi LIKE '%$searchSafe%')";
                }
                $dataQuery .= " ORDER BY nama ASC LIMIT $offset, $limit";

                $query = mysqli_query($conn, $dataQuery);
                $no = $offset + 1;

                while($row = mysqli_fetch_assoc($query)) {
              ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= ucfirst($row['jenis']) ?></td>
                <td><?= htmlspecialchars($row['bidang']) ?></td>
                <td title="<?= htmlspecialchars($row['deskripsi'] ?? '') ?>">
                  <?= htmlspecialchars(substr($row['deskripsi'] ?? '', 0, 50)) ?><?= strlen($row['deskripsi'] ?? '') > 50 ? '...' : '' ?>
                </td>
                <td><img src="../assets/layanan/<?= htmlspecialchars($row['logo']) ?>" class="logo-preview"></td>
                <td><input type="text" value="<?= htmlspecialchars($row['url']) ?>" readonly></td>
                <td><?= $row['highlight'] ? '✅' : '❌' ?></td>
                <td class="actions">
                  <a href="edit.php?id=<?= $row['id'] ?>&jenis=<?= $row['jenis'] ?>" class="edit">Edit</a>
                  <a href="hapus.php?id=<?= $row['id'] ?>&jenis=<?= $row['jenis'] ?>" class="delete" onclick="return confirm('Hapus layanan ini?')">Delete</a>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <?php
          $base = "index.php";
          if ($jenis) $base .= "?jenis=$jenis";
          else $base .= "?";
          if (!empty($search)) $base .= (strpos($base, '?') !== false ? '&' : '?') . "search=" . urlencode($search);

          showPagination($total_data, $limit, $page, $base);
        ?>
      </div>

      <footer>&copy; <?= date('Y') ?> Kota Probolinggo</footer>
    </div>
  </div>
</body>
</html>