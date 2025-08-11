<?php
// // File: api/get_layanan_unified.php
// // Unified API endpoint with optional security features

// // Configuration
// $ENABLE_SECURITY = true; // Set to false to disable security features
// $USE_PDO = true; // Set to false to use MySQLi (legacy mode)

// // Start output buffering for clean JSON response
// ob_start();

// // Basic headers
// header('Content-Type: application/json; charset=utf-8');

// // Security headers (optional)
// if ($ENABLE_SECURITY) {
//     header('X-Content-Type-Options: nosniff');
//     header('X-Frame-Options: DENY');
//     header('X-XSS-Protection: 1; mode=block');
//     header('Referrer-Policy: strict-origin-when-cross-origin');
// }

// // Only allow GET requests
// if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
//     http_response_code(405);
//     header('Allow: GET');
//     exit(json_encode(['error' => 'Method not allowed']));
// }

// // Input validation and sanitization
// function sanitizeInput($input) {
//     if (is_null($input)) return '';
//     return trim(htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8'));
// }

// function validateUrl($url) {
//     if (empty($url)) return false;
    
//     // Basic URL validation
//     if (!filter_var($url, FILTER_VALIDATE_URL)) {
//         return false;
//     }
    
//     // Additional security checks if enabled
//     global $ENABLE_SECURITY;
//     if ($ENABLE_SECURITY) {
//         // Check for allowed domains
//         $allowedDomains = [
//             'probolinggokota.go.id',
//             'layanan.probolinggokota.go.id',
//             'sipkd.probolinggokota.go.id',
//             'simpeg.probolinggokota.go.id',
//             'e-office.probolinggokota.go.id',
//             'surat.probolinggokota.go.id',
//             'localhost'
//         ];
        
//         $parsedUrl = parse_url($url);
//         $hostname = $parsedUrl['host'] ?? '';
        
//         $isDomainAllowed = false;
//         foreach ($allowedDomains as $domain) {
//             if ($hostname === $domain || str_ends_with($hostname, '.' . $domain)) {
//                 $isDomainAllowed = true;
//                 break;
//             }
//         }
        
//         if (!$isDomainAllowed) {
//             return false;
//         }
        
//         // Check for suspicious patterns
//         $suspiciousPatterns = [
//             '/javascript:/i',
//             '/data:/i',
//             '/vbscript:/i',
//             '/file:/i'
//         ];
        
//         foreach ($suspiciousPatterns as $pattern) {
//             if (preg_match($pattern, $url)) {
//                 return false;
//             }
//         }
//     }
    
//     return true;
// }

// // Simple rate limiting (if security enabled)
// function checkRateLimit($clientId) {
//     global $ENABLE_SECURITY;
//     if (!$ENABLE_SECURITY) return true;
    
//     $rateFile = sys_get_temp_dir() . '/rate_limit_' . md5($clientId) . '.txt';
//     $maxRequests = 60; // per minute
//     $timeWindow = 60; // seconds
    
//     $now = time();
//     $requests = [];
    
//     if (file_exists($rateFile)) {
//         $data = file_get_contents($rateFile);
//         $requests = $data ? json_decode($data, true) : [];
//     }
    
//     // Filter out old requests
//     $requests = array_filter($requests, function($time) use ($now, $timeWindow) {
//         return ($now - $time) < $timeWindow;
//     });
    
//     if (count($requests) >= $maxRequests) {
//         return false;
//     }
    
//     $requests[] = $now;
//     file_put_contents($rateFile, json_encode($requests));
    
//     return true;
// }

// // Get and validate parameters
// $jenis = sanitizeInput($_GET['jenis'] ?? '');
// $highlight = sanitizeInput($_GET['highlight'] ?? '');
// $all = sanitizeInput($_GET['all'] ?? ''); // For backward compatibility with get_all_layanan.php

// // Validate jenis parameter
// $allowedJenis = ['internal', 'publik'];
// if (!in_array($jenis, $allowedJenis)) {
//     http_response_code(400);
//     exit(json_encode(['error' => 'Invalid jenis parameter']));
// }

// // Rate limiting check
// $clientId = $_SERVER['REMOTE_ADDR'] . '_' . ($_SERVER['HTTP_USER_AGENT'] ?? '');
// if (!checkRateLimit($clientId)) {
//     http_response_code(429);
//     exit(json_encode(['error' => 'Rate limit exceeded']));
// }

// try {
//     // Database connection
//     if ($USE_PDO) {
//         // PDO connection (enhanced)
//         $dbConfig = [
//             'host' => 'localhost',
//             'dbname' => 'portal_layanan',
//             'username' => 'portal_user',
//             'password' => 'secure_password_here', // Use environment variable in production
//             'charset' => 'utf8mb4'
//         ];
        
//         $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
//         $options = [
//             PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//             PDO::ATTR_EMULATE_PREPARES => false,
//         ];
        
//         $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
        
//         // Build query
//         $baseQuery = "SELECT id, nama, logo, url, bidang, deskripsi, status";
//         $whereConditions = ["jenis = :jenis"];
//         $params = [':jenis' => $jenis];
        
//         // Add status filter for security (only active services)
//         if ($ENABLE_SECURITY) {
//             $whereConditions[] = "status = 'aktif'";
//         }
        
//         // Add highlight filter if specified
//         if ($highlight === '1') {
//             $whereConditions[] = "highlight = 1";
//         }
        
//         $query = $baseQuery . " FROM layanan WHERE " . implode(' AND ', $whereConditions) . " ORDER BY urutan ASC, nama ASC";
        
//         $stmt = $pdo->prepare($query);
//         $stmt->execute($params);
//         $result = $stmt->fetchAll();
        
//     } else {
//         // MySQLi connection (legacy compatibility)
//         include '../db/koneksi.php';
        
//         // Build query
//         $baseQuery = "SELECT id, nama, logo, url, bidang, deskripsi, status FROM layanan WHERE jenis = ?";
//         $params = [$jenis];
//         $types = "s";
        
//         // Add status filter for security
//         if ($ENABLE_SECURITY) {
//             $baseQuery .= " AND status = 'aktif'";
//         }
        
//         // Add highlight filter if specified
//         if ($highlight === '1') {
//             $baseQuery .= " AND highlight = 1";
//             $params[] = 1;
//             $types .= "i";
//         }
        
//         $baseQuery .= " ORDER BY id DESC";
        
//         $stmt = $conn->prepare($baseQuery);
//         if ($highlight === '1') {
//             $stmt->bind_param("si", $jenis, 1);
//         } else {
//             $stmt->bind_param("s", $jenis);
//         }
        
//         $stmt->execute();
//         $queryResult = $stmt->get_result();
        
//         $result = [];
//         while ($row = $queryResult->fetch_assoc()) {
//             $result[] = $row;
//         }
//     }
    
//     // Process and validate results
//     $validLayanan = [];
//     foreach ($result as $item) {
//         // Validate URL if security is enabled
//         if ($ENABLE_SECURITY && !validateUrl($item['url'])) {
//             // Log invalid URL but don't expose it
//             error_log("Invalid URL filtered from database: ID " . $item['id']);
//             continue;
//         }
        
//         // Sanitize output
//         $validItem = [
//             'id' => (int)$item['id'],
//             'nama' => sanitizeInput($item['nama']),
//             'logo' => sanitizeInput($item['logo'] ?? ''),
//             'url' => $item['url'],
//             'bidang' => sanitizeInput($item['bidang'] ?? ''),
//         ];
        
//         // Add optional fields
//         if (isset($item['deskripsi'])) {
//             $validItem['deskripsi'] = sanitizeInput($item['deskripsi']);
//         }
//         if (isset($item['status'])) {
//             $validItem['status'] = sanitizeInput($item['status']);
//         }
        
//         $validLayanan[] = $validItem;
//     }
    
//     // Cache headers (if security enabled)
//     if ($ENABLE_SECURITY) {
//         $etag = md5(json_encode($validLayanan));
//         header('Cache-Control: public, max-age=300'); // 5 minutes
//         header('ETag: "' . $etag . '"');
        
//         // Check if client has cached version
//         if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && 
//             $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
//             http_response_code(304);
//             exit;
//         }
//     }
    
//     // Clean output buffer and return JSON
//     ob_clean();
//     echo json_encode($validLayanan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
// } catch (PDOException $e) {
//     ob_clean();
//     http_response_code(500);
    
//     if ($ENABLE_SECURITY) {
//         // Log error but don't expose details
//         error_log("Database error in get_layanan: " . $e->getMessage());
//         echo json_encode(['error' => 'Database connection failed']);
//     } else {
//         // Legacy mode - show more details
//         echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
//     }
    
// } catch (Exception $e) {
//     ob_clean();
//     http_response_code(500);
    
//     if ($ENABLE_SECURITY) {
//         error_log("General error in get_layanan: " . $e->getMessage());
//         echo json_encode(['error' => 'Internal server error']);
//     } else {
//         echo json_encode(['error' => $e->getMessage()]);
//     }
// }

// // Optional request logging
// function logApiRequest() {
//     global $ENABLE_SECURITY;
//     if (!$ENABLE_SECURITY) return;
    
//     $logData = [
//         'timestamp' => date('Y-m-d H:i:s'),
//         'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
//         'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
//         'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
//         'jenis' => $_GET['jenis'] ?? 'unknown'
//     ];
    
//     $logDir = '../logs';
//     if (!is_dir($logDir)) {
//         mkdir($logDir, 0755, true);
//     }
    
//     error_log(json_encode($logData) . "\n", 3, $logDir . '/api_access.log');
// }

// logApiRequest();
?>