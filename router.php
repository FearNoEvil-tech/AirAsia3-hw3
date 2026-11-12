<?php
/*
 * router.php: optional router for the PHP development server.
 *     php -S 0.0.0.0:8000 router.php
 * Without it, the development server would let a visitor download
 * database/rewards.db or open the include files directly. This blocks those
 * paths and lets every other request through unchanged.
 */
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');

$blockedFolder = preg_match('~^/+(database|includes|\.git)(/|$)~i', $path);
$blockedFile = preg_match('~\.(db|sqlite|sql)$~i', $path) || basename($path) === 'router.php';

if ($blockedFolder || $blockedFile) {
    http_response_code(404);
    exit('Not found');
}

return false;   // serve the requested page or file normally
