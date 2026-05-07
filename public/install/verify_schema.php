<?php
require __DIR__ . '/_common.php';

$loaded = install_load_config();
$config = $loaded['config'];
if (!install_is_authorized($config)) {
    install_denied();
}

$checks = [];
if (!$loaded['exists'] || $loaded['error']) {
    $checks[] = install_check('config.php', 'fail', $loaded['error'] ?: 'Missing config.php.');
    install_render('Doostyabi schema verification', $checks, 500);
    exit;
}

[$pdo, $dbError] = install_pdo($config);
if (!$pdo) {
    $checks[] = install_check('Database connection', 'fail', $dbError);
    install_render('Doostyabi schema verification', $checks, 500);
    exit;
}
$checks[] = install_check('Database connection', 'pass', 'Connected');

$existing = install_existing_tables($pdo);
$missing = array_values(array_diff(install_expected_tables(), $existing));
$checks[] = install_check('Required schema tables', $missing ? 'fail' : 'pass', $missing ? ('Missing: ' . implode(', ', $missing)) : 'All expected tables found.');

if (!$missing) {
    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE email=? LIMIT 1');
    $stmt->execute(['admin@example.com']);
    $admin = $stmt->fetch();
    $checks[] = install_check('Default admin account', $admin ? 'pass' : 'fail', $admin ? 'admin@example.com exists.' : 'admin@example.com is missing.');
    if ($admin) {
        $matchesDefault = password_verify('admin123', (string)$admin['password_hash']);
        $checks[] = install_check('Default admin password verification', $matchesDefault ? 'pass' : 'warn', $matchesDefault ? 'Seeded password works; change it after first login.' : 'Stored admin password does not match the seed default, which is expected if it was changed.');
    }

    $seedChecks = [
        'roles' => "SELECT COUNT(*) FROM roles WHERE name IN ('admin','user')",
        'permissions' => "SELECT COUNT(*) FROM permissions WHERE name IN ('admin.access','forms.manage','users.view','onboarding.complete')",
        'goals' => 'SELECT COUNT(*) FROM goals WHERE deleted_at IS NULL',
        'provinces' => 'SELECT COUNT(*) FROM provinces WHERE deleted_at IS NULL',
        'cities' => 'SELECT COUNT(*) FROM cities WHERE deleted_at IS NULL',
        'form steps' => 'SELECT COUNT(*) FROM form_steps WHERE deleted_at IS NULL',
        'question groups' => 'SELECT COUNT(*) FROM question_groups WHERE deleted_at IS NULL',
        'questions' => 'SELECT COUNT(*) FROM questions WHERE deleted_at IS NULL',
        'question options' => 'SELECT COUNT(*) FROM question_options WHERE deleted_at IS NULL',
    ];
    foreach ($seedChecks as $label => $sql) {
        $count = (int)$pdo->query($sql)->fetchColumn();
        $checks[] = install_check('Core seed records: ' . $label, $count > 0 ? 'pass' : 'fail', $count . ' record(s) found.');
    }
}

install_render('Doostyabi schema verification', $checks);
