<?php
require __DIR__ . '/_common.php';

$loaded = install_load_config();
$config = $loaded['config'];
if (!install_is_authorized($config)) {
    install_denied();
}

$checks = [];
$checks[] = install_check('config.php exists', $loaded['exists'] ? 'pass' : 'fail', $loaded['exists'] ? 'Found' : 'Missing; copy config.example.php to config.php.');
if ($loaded['error']) {
    $checks[] = install_check('config.php format', 'fail', $loaded['error']);
} elseif ($loaded['exists']) {
    $checks[] = install_check('config.php format', is_array($config) ? 'pass' : 'fail', is_array($config) ? 'Configuration array loaded.' : 'Invalid configuration.');
}
$checks[] = install_check('APP_ENV', 'pass', install_app_env($config));
$checks[] = install_check('Install guard', 'pass', 'Script is authorized; remove INSTALL_TOKEN or delete public/install after setup.');

foreach (['pdo', 'pdo_mysql', 'session', 'json'] as $extension) {
    $checks[] = install_check('PHP extension: ' . $extension, extension_loaded($extension) ? 'pass' : 'fail', extension_loaded($extension) ? 'Available' : 'Missing');
}

$logs = INSTALL_ROOT . '/storage/logs';
$checks[] = install_check('storage/logs writable', is_dir($logs) && is_writable($logs) ? 'pass' : 'fail', is_dir($logs) ? 'Directory exists' : 'Directory missing');

[$pdo, $dbError] = install_pdo($config);
$checks[] = install_check('Database connection', $pdo ? 'pass' : 'fail', $pdo ? 'Connected' : $dbError);

if ($pdo) {
    $existing = install_existing_tables($pdo);
    $missing = array_values(array_diff(install_expected_tables(), $existing));
    $checks[] = install_check('Required schema tables', $missing ? 'fail' : 'pass', $missing ? ('Missing: ' . implode(', ', $missing)) : 'All expected tables found.');
}

install_render('Doostyabi install check', $checks);
