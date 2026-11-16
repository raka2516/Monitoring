<?php
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (($uri === '/' || $uri === '/index.html') && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (str_contains($accept, 'text/html') || $uri === '/index.html' || !str_contains($accept, 'application/json')) {
        header('Content-Type: text/html; charset=utf-8');
        readfile(__DIR__ . '/index.html');
        exit;
    }
}
require_once __DIR__ . '/health_functions.php';

if ($uri === '/health') {
    require __DIR__ . '/api/health.php';
    exit;
}
if ($uri === '/api/health/all' || $uri === '/all') {
    require __DIR__ . '/api/health_all.php';
    exit;
}
http_response_code(404);
echo json_encode(['error' => 'not_found', 'message' => "Endpoint {$uri} tidak ditemukan"]);
