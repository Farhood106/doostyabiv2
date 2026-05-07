<?php
const INSTALL_ROOT = __DIR__ . '/../..';

function install_expected_tables(): array
{
    return [
        'roles', 'permissions', 'role_permissions', 'users', 'goals', 'user_goals',
        'provinces', 'cities', 'form_steps', 'question_groups', 'questions',
        'question_options', 'user_answers', 'user_answer_options', 'user_answer_cities',
        'user_onboarding_progress', 'audit_logs',
    ];
}

function install_load_config(): array
{
    $path = INSTALL_ROOT . '/config.php';
    if (!is_file($path)) {
        return ['exists' => false, 'config' => null, 'error' => null];
    }
    $config = require $path;
    if (!is_array($config)) {
        return ['exists' => true, 'config' => null, 'error' => 'config.php must return an array.'];
    }
    return ['exists' => true, 'config' => $config, 'error' => null];
}

function install_app_env(?array $config): string
{
    return (string)(getenv('APP_ENV') ?: ($config['app']['env'] ?? 'production'));
}

function install_configured_token(?array $config): string
{
    return (string)(getenv('INSTALL_TOKEN') ?: ($config['app']['install_token'] ?? ''));
}

function install_request_token(): string
{
    if (PHP_SAPI === 'cli') {
        global $argv;
        foreach ($argv ?? [] as $arg) {
            if (str_starts_with($arg, '--token=')) {
                return substr($arg, 8);
            }
        }
        return (string)(getenv('INSTALL_TOKEN') ?: '');
    }
    return (string)($_GET['token'] ?? '');
}

function install_is_authorized(?array $config): bool
{
    $env = strtolower(install_app_env($config));
    if (in_array($env, ['local', 'development'], true)) {
        return true;
    }
    $configuredToken = install_configured_token($config);
    if ($configuredToken === '') {
        return false;
    }
    if (PHP_SAPI === 'cli') {
        return true;
    }
    return hash_equals($configuredToken, install_request_token());
}

function install_pdo(?array $config): array
{
    if (!$config || empty($config['db'])) {
        return [null, 'Database configuration is missing.'];
    }
    if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
        return [null, 'The PDO MySQL extension is not available.'];
    }
    $db = $config['db'];
    foreach (['host', 'name', 'user', 'charset'] as $key) {
        if (empty($db[$key])) {
            return [null, 'Database configuration is incomplete.'];
        }
    }
    try {
        $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";
        $pdo = new PDO($dsn, $db['user'], $db['pass'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return [$pdo, null];
    } catch (Throwable $e) {
        return [null, 'Database connection failed.'];
    }
}

function install_existing_tables(PDO $pdo): array
{
    $rows = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_NUM);
    return array_map(fn(array $row): string => strtolower((string)$row[0]), $rows);
}

function install_check(string $label, string $status, string $detail = ''): array
{
    return ['label' => $label, 'status' => $status, 'detail' => $detail];
}

function install_render(string $title, array $checks, int $httpCode = 200): void
{
    if (PHP_SAPI !== 'cli') {
        http_response_code($httpCode);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title><link rel="stylesheet" href="/assets/style.css"></head><body><main class="container"><section class="card"><h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1><p class="muted">Remove or protect the install directory after setup. No secrets are displayed here.</p><table><thead><tr><th>Check</th><th>Status</th><th>Details</th></tr></thead><tbody>';
        foreach ($checks as $check) {
            echo '<tr><td>' . htmlspecialchars($check['label'], ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars(strtoupper($check['status']), ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars($check['detail'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
        }
        echo '</tbody></table></section></main></body></html>';
        return;
    }

    echo $title . PHP_EOL;
    echo str_repeat('=', strlen($title)) . PHP_EOL;
    foreach ($checks as $check) {
        echo '[' . strtoupper($check['status']) . '] ' . $check['label'];
        if ($check['detail'] !== '') {
            echo ' - ' . $check['detail'];
        }
        echo PHP_EOL;
    }
}

function install_denied(): void
{
    install_render('Install scripts disabled', [
        install_check('Safety guard', 'fail', 'Set APP_ENV to local/development or configure a temporary INSTALL_TOKEN to run this script.'),
    ], 403);
    exit;
}
