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
        'match_cards.narrative' => "SHOW COLUMNS FROM match_cards LIKE 'narrative'",
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

    $tablesToCheck = install_expected_tables();
    $tableStmt = $pdo->prepare('SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1');
    foreach ($tablesToCheck as $tableName) {
        $tableStmt->execute([$tableName]);
        $collation = (string)($tableStmt->fetchColumn() ?: '');
        $isUtf8mb4 = str_starts_with(strtolower($collation), 'utf8mb4_');
        $checks[] = install_check('Table charset: ' . $tableName, $isUtf8mb4 ? 'pass' : 'warn', $isUtf8mb4 ? $collation : ($collation ? "Expected utf8mb4, found {$collation}. Run database/migrations/convert_to_utf8mb4.sql." : 'Table collation unavailable.'));
    }

    $textColumnChecks = [
        'match_cards.strengths_text' => ['match_cards', 'strengths_text'],
        'match_cards.cautions_text' => ['match_cards', 'cautions_text'],
        'match_cards.narrative' => ['match_cards', 'narrative'],
        'messages.body' => ['messages', 'body'],
        'reports.report_reason' => ['reports', 'report_reason'],
        'user_answers.answer_text' => ['user_answers', 'answer_text'],
    ];
    $columnStmt = $pdo->prepare('SELECT DATA_TYPE, CHARACTER_SET_NAME, COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1');
    foreach ($textColumnChecks as $label => [$tableName, $columnName]) {
        $columnStmt->execute([$tableName, $columnName]);
        $column = $columnStmt->fetch();
        if (!$column) {
            $checks[] = install_check('Text column encoding: ' . $label, 'fail', 'Column missing.');
            continue;
        }
        $typeOk = in_array(strtolower((string)$column['DATA_TYPE']), ['varchar', 'text', 'mediumtext', 'longtext'], true);
        $charsetOk = strtolower((string)($column['CHARACTER_SET_NAME'] ?? '')) === 'utf8mb4';
        $checks[] = install_check('Text column encoding: ' . $label, ($typeOk && $charsetOk) ? 'pass' : 'warn', ($typeOk && $charsetOk) ? (($column['DATA_TYPE'] ?? '') . ' / ' . ($column['COLLATION_NAME'] ?? '')) : 'Expected VARCHAR/TEXT with utf8mb4. Run database/migrations/convert_to_utf8mb4.sql.');
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
