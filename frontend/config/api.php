<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Frontend API Configuration
 * Handles all HTTP requests from the PHP frontend to the CodeIgniter REST backend.
 * - Includes connection/execution timeouts to prevent the "120s exceeded" fatal error
 * - Supports GET, POST, PUT (via method spoofing), DELETE
 * - Returns a safe ['success'=>bool, 'data'=>..., 'message'=>...] array always
 */

// ─── Base URL Detection ───────────────────────────────────────────────────────
// Detect the backend API URL dynamically based on server host and protocol
$_api_host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_api_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_api_base     = $_api_protocol . '://' . $_api_host . '/web2_TIC_kelompok2_miniritail2/backend/public/api';

if (!defined('BASE_API_URL')) {
    define('BASE_API_URL', $_api_base);
}

// ─── Helper: Get JWT Token ────────────────────────────────────────────────────
function getAuthToken(): string {
    return $_SESSION['token'] ?? '';
}

// ─── Core API Request Function ────────────────────────────────────────────────
/**
 * Send an HTTP request to the backend REST API.
 *
 * @param  string  $method      HTTP verb: GET, POST, PUT, DELETE
 * @param  string  $endpoint    Path starting with '/', e.g. '/products'
 * @param  mixed   $data        Associative array of body data or query params
 * @param  bool    $isMultipart If true, sends as multipart/form-data (for file uploads)
 * @return array   Always returns ['success'=>bool, 'data'=>mixed, 'message'=>string]
 */
function apiRequest(string $method, string $endpoint, mixed $data = [], bool $isMultipart = false): array {
    // Build the full URL
    $url = BASE_API_URL . $endpoint;

    // For GET requests, append data as query string
    if (strtoupper($method) === 'GET' && !empty($data)) {
        $url .= '?' . http_build_query($data);
    }

    $ch = curl_init($url);

    // ─── cURL Options ─────────────────────────────────────────────────────────
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_CONNECTTIMEOUT  => 5,     // Max seconds to connect
        CURLOPT_TIMEOUT         => 10,    // Max total execution seconds
        CURLOPT_FOLLOWLOCATION  => true,
        CURLOPT_SSL_VERIFYPEER  => false,
        CURLOPT_SSL_VERIFYHOST  => false,
        CURLOPT_HTTPHEADER      => [
            'Accept: application/json',
            'X-Requested-With: XMLHttpRequest',
            // JWT Token if session exists
            ...(!empty($_SESSION['token']) ? ['Authorization: Bearer ' . $_SESSION['token']] : []),
        ],
    ]);

    // ─── Method Handling ──────────────────────────────────────────────────────
    $upperMethod = strtoupper($method);

    switch ($upperMethod) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            if ($isMultipart) {
                // Multipart for file uploads — send data as-is (CURLFile objects supported)
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Accept: application/json',
                    'Content-Type: application/x-www-form-urlencoded',
                    ...(!empty($_SESSION['token']) ? ['Authorization: Bearer ' . $_SESSION['token']] : []),
                ]);
            }
            break;

        case 'PUT':
            // PHP cannot natively parse multipart PUT bodies.
            // Use method spoofing: POST with _method=PUT field.
            curl_setopt($ch, CURLOPT_POST, true);
            if ($isMultipart) {
                // File upload with PUT — use method spoofing via POST + _method
                $data['_method'] = 'PUT';
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                // JSON body for simple PUT (no file)
                $jsonBody = json_encode($data);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($jsonBody),
                    ...(!empty($_SESSION['token']) ? ['Authorization: Bearer ' . $_SESSION['token']] : []),
                ]);
            }
            break;

        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;

        case 'GET':
        default:
            // Already handled above
            break;
    }

    // ─── Execute Request ──────────────────────────────────────────────────────
    $response   = curl_exec($ch);
    $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErrno  = curl_errno($ch);
    $curlError  = curl_error($ch);
    curl_close($ch);

    // ─── Handle Connection Errors ─────────────────────────────────────────────
    if ($curlErrno !== 0 || $response === false) {
        return [
            'success' => false,
            'data'    => null,
            'message' => 'Koneksi ke server gagal: ' . ($curlError ?: 'Unknown cURL error'),
        ];
    }

    // ─── Parse JSON Response ──────────────────────────────────────────────────
    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Response is not valid JSON
        return [
            'success' => false,
            'data'    => null,
            'message' => 'Server mengembalikan response tidak valid (HTTP ' . $httpCode . ')',
        ];
    }

    // ─── Normalize Response ───────────────────────────────────────────────────
    $isSuccess = ($httpCode >= 200 && $httpCode < 300) && ($decoded['success'] ?? false) === true;

    return [
        'success' => $isSuccess,
        'data'    => $decoded['data'] ?? null,
        'message' => $decoded['message'] ?? ($isSuccess ? 'OK' : 'Terjadi kesalahan pada server'),
    ];
}
