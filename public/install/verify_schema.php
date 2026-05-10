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



    $chatColumnChecks = [
        'chats.status' => "SHOW COLUMNS FROM chats LIKE 'status'",
        'chats.closed_reason' => "SHOW COLUMNS FROM chats LIKE 'closed_reason'",
        'messages.body' => "SHOW COLUMNS FROM messages LIKE 'body'",
        'messages.moderation_status' => "SHOW COLUMNS FROM messages LIKE 'moderation_status'",
        'chat_participants.user_id' => "SHOW COLUMNS FROM chat_participants LIKE 'user_id'",
        'reveal_types.reveal_field_key' => "SHOW COLUMNS FROM reveal_types LIKE 'reveal_field_key'",
        'reveal_requests.status' => "SHOW COLUMNS FROM reveal_requests LIKE 'status'",
        'match_visibility_snapshots.viewer_user_id' => "SHOW COLUMNS FROM match_visibility_snapshots LIKE 'viewer_user_id'",
        'reports.status' => "SHOW COLUMNS FROM reports LIKE 'status'",
        'reports.priority' => "SHOW COLUMNS FROM reports LIKE 'priority'",
        'users.profile_quality_score' => "SHOW COLUMNS FROM users LIKE 'profile_quality_score'",
        'users.trust_score' => "SHOW COLUMNS FROM users LIKE 'trust_score'",
        'users.trust_flags_json' => "SHOW COLUMNS FROM users LIKE 'trust_flags_json'",
        'users.quality_flags_json' => "SHOW COLUMNS FROM users LIKE 'quality_flags_json'",
        'users.moderation_signals_json' => "SHOW COLUMNS FROM users LIKE 'moderation_signals_json'",
        'match_cards.last_shown_at' => "SHOW COLUMNS FROM match_cards LIKE 'last_shown_at'",
        'match_cards.shown_count' => "SHOW COLUMNS FROM match_cards LIKE 'shown_count'",
        'match_cards.hidden_until' => "SHOW COLUMNS FROM match_cards LIKE 'hidden_until'",
        'match_cards.freshness_score' => "SHOW COLUMNS FROM match_cards LIKE 'freshness_score'",
        'user_onboarding_progress.skipped_optional_count' => "SHOW COLUMNS FROM user_onboarding_progress LIKE 'skipped_optional_count'",
        'user_onboarding_progress.engagement_metadata_json' => "SHOW COLUMNS FROM user_onboarding_progress LIKE 'engagement_metadata_json'",
    ];
    foreach ($chatColumnChecks as $label => $sql) {
        $stmt = $pdo->query($sql);
        $found = (bool)$stmt->fetch();
        $checks[] = install_check('Schema column: ' . $label, $found ? 'pass' : 'fail', $found ? 'Column found.' : 'Missing column.');
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
        'admin settings' => 'SELECT COUNT(*) FROM admin_settings',
        'reveal types' => 'SELECT COUNT(*) FROM reveal_types WHERE is_active=1',
    ];
    foreach ($seedChecks as $label => $sql) {
        $count = (int)$pdo->query($sql)->fetchColumn();
        $checks[] = install_check('Core seed records: ' . $label, $count > 0 ? 'pass' : 'fail', $count . ' record(s) found.');
    }
}

install_render('Doostyabi schema verification', $checks);
