<?php
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

require __DIR__ . '/Core/Helpers.php';

$configFile = dirname(__DIR__) . '/config.php';
if (!file_exists($configFile)) {
    http_response_code(503);
    require dirname(__DIR__) . '/views/setup.php';
    exit;
}

$GLOBALS['app_config'] = require $configFile;
$sessionName = $GLOBALS['app_config']['app']['session_name'] ?? 'doostyabi_session';
session_name($sessionName);
session_start();
