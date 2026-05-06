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
$config = file_exists($configFile) ? require $configFile : require dirname(__DIR__) . '/config.example.php';
session_name($config['app']['session_name'] ?? 'doostyabi_session');
session_start();
