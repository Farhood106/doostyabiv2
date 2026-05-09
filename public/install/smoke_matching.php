<?php
require __DIR__ . '/_common.php';

$loaded = install_load_config();
$config = $loaded['config'];
if (!install_is_authorized($config)) { install_denied(); }
if (!$loaded['exists'] || $loaded['error']) { install_render('Doostyabi matching smoke test', [install_check('config.php', 'fail', $loaded['error'] ?: 'Missing config.php.')], 500); exit; }

require INSTALL_ROOT . '/src/bootstrap.php';

use App\Core\Database;
use App\Services\MatchService;

$checks = [];
[$pdo, $dbError] = install_pdo($config);
if (!$pdo) { install_render('Doostyabi matching smoke test', [install_check('Database connection', 'fail', $dbError)], 500); exit; }
try {
    $pdo->beginTransaction();
    $roleId = (int)$pdo->query("SELECT id FROM roles WHERE name='user' LIMIT 1")->fetchColumn();
    $goalId = (int)$pdo->query("SELECT id FROM goals WHERE is_active=1 AND deleted_at IS NULL ORDER BY id LIMIT 1")->fetchColumn();
    if (!$roleId || !$goalId) { throw new RuntimeException('Missing user role or active goal seed.'); }
    $hash = password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT);
    $users = [];
    foreach ([1,2] as $i) {
        $email = "smoke_match_{$i}@example.invalid";
        $stmt = $pdo->prepare('INSERT INTO users (role_id,email,password_hash,first_name,is_active) VALUES (?,?,?,?,1) ON DUPLICATE KEY UPDATE is_active=1');
        $stmt->execute([$roleId, $email, $hash, 'Smoke '.$i]);
        $find = $pdo->prepare('SELECT id FROM users WHERE email=?'); $find->execute([$email]); $users[] = (int)$find->fetchColumn();
    }
    $goalStmt = $pdo->prepare('INSERT IGNORE INTO user_goals (user_id, goal_id) VALUES (?,?)');
    foreach ($users as $uid) { $goalStmt->execute([$uid, $goalId]); }

    $question = $pdo->query("SELECT id, answer_type FROM questions WHERE is_matchable=1 AND is_active=1 AND deleted_at IS NULL ORDER BY id LIMIT 1")->fetch();
    if ($question) {
        foreach ($users as $uid) {
            $answerStmt = $pdo->prepare('INSERT INTO user_answers (user_id, question_id, answer_text, answer_number, answer_boolean, answer_date) VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE answer_text=VALUES(answer_text), answer_number=VALUES(answer_number), answer_boolean=VALUES(answer_boolean), answer_date=VALUES(answer_date)');
            $type = $question['answer_type'];
            $answerStmt->execute([$uid, $question['id'], in_array($type, ['text','textarea','range'], true) ? '10-20' : null, in_array($type, ['number','scale'], true) ? 5 : null, $type === 'boolean' ? 1 : null, $type === 'date' ? '2000-01-01' : null]);
            $answerId = (int)$pdo->lastInsertId();
            if ($answerId === 0) { $find = $pdo->prepare('SELECT id FROM user_answers WHERE user_id=? AND question_id=?'); $find->execute([$uid, $question['id']]); $answerId = (int)$find->fetchColumn(); }
            if (in_array($type, ['single_choice','multi_choice','select','multi_select'], true)) {
                $optStmt = $pdo->prepare('SELECT id FROM question_options WHERE question_id=? AND is_active=1 AND deleted_at IS NULL ORDER BY id LIMIT 1'); $optStmt->execute([$question['id']]); $optionId = (int)$optStmt->fetchColumn();
                if ($optionId) { $pdo->prepare('INSERT IGNORE INTO user_answer_options (user_answer_id, question_option_id) VALUES (?,?)')->execute([$answerId, $optionId]); }
            }
            if (in_array($type, ['city_single','city_multi'], true)) {
                $cityId = (int)$pdo->query('SELECT id FROM cities WHERE is_active=1 AND deleted_at IS NULL ORDER BY id LIMIT 1')->fetchColumn();
                if ($cityId) { $pdo->prepare('INSERT IGNORE INTO user_answer_cities (user_answer_id, city_id) VALUES (?,?)')->execute([$answerId, $cityId]); }
            }
        }
    }
    $pdo->commit();
    $count = (new MatchService())->runForUser($users[0], true, 5);
    $checks[] = install_check('Smoke users and goal', 'pass', 'Created or reused two non-login smoke users.');
    $checks[] = install_check('Minimal matchable answer', $question ? 'pass' : 'warn', $question ? 'Created or reused one shared matchable answer.' : 'No active matchable question found; goal-only matching tested.');
    $checks[] = install_check('Matching service run', $count > 0 ? 'pass' : 'warn', $count . ' recommendation(s) generated or updated.');
    $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM match_cards WHERE viewer_user_id=?');
    $stmt->execute([$users[0]]);
    $checks[] = install_check('Anonymous card created', (int)$stmt->fetchColumn() > 0 ? 'pass' : 'warn', 'Checked card count without exposing card payload.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    $checks[] = install_check('Matching smoke test', 'fail', 'Smoke test failed; check schema/seeds on the target host.');
}
install_render('Doostyabi matching smoke test', $checks);
