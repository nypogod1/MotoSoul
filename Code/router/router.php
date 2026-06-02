<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (preg_match('#^/api(?:/index\.php)?(/.*)?$#', $uri, $m)) {
    $_SERVER['REQUEST_URI'] = '/api' . ($m[1] ?? '');
    require __DIR__ . '/api/index.php';
    return true;
}

$file = __DIR__ . $uri;
if ($uri !== '/' && is_file($file)) {
    return false;
}

readfile(__DIR__ . '/index.html');
return true;
