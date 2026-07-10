<?php

// ponytail: This mirrors the four .htaccess rewrites; use Apache for production.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$target = realpath(__DIR__.$path);

if ($path !== '/' && $target !== false && str_starts_with($target, __DIR__) && (is_file($target) || is_dir($target))) {
    return false;
}

if (preg_match('#^/doc/([a-zA-Z0-9_-]+)\.html$#', $path, $matches)) {
    $_GET['doc'] = $matches[1];
    require __DIR__.'/index.php';
} elseif (preg_match('#^/([a-zA-Z0-9_-]+)\.html$#', $path, $matches)) {
    $_GET['mod'] = $matches[1];
    require __DIR__.'/index.php';
} elseif (preg_match('#^/pay/(.*)$#', $path, $matches)) {
    $_GET['s'] = $matches[1];
    require __DIR__.'/pay.php';
} elseif (preg_match('#^/api/(.*)$#', $path, $matches)) {
    $_GET['s'] = $matches[1];
    require __DIR__.'/api.php';
} elseif ($path === '/') {
    require __DIR__.'/index.php';
} else {
    http_response_code(404);
    require __DIR__.'/404.html';
}
