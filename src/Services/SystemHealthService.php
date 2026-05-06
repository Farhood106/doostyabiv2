<?php
namespace App\Services;

use App\Core\Database;

class SystemHealthService
{
    public function report(): array
    {
        $tables = ['roles','permissions','role_permissions','users','goals','user_goals','provinces','cities','form_steps','question_groups','questions','question_options','user_answers','user_answer_options','user_answer_cities','user_onboarding_progress','audit_logs'];
        $dbOk = false; $dbError = null; $tableStatus = [];
        try {
            $db = Database::connection();
            $db->query('SELECT 1');
            $dbOk = true;
            $stmt = $db->query('SHOW TABLES');
            $existing = array_map('strtolower', array_map('current', $stmt->fetchAll()));
            foreach ($tables as $table) { $tableStatus[$table] = in_array($table, $existing, true); }
        } catch (\Throwable $e) {
            $dbError = $e->getMessage();
            foreach ($tables as $table) { $tableStatus[$table] = false; }
        }
        return [
            'php_version' => PHP_VERSION,
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'database_ok' => $dbOk,
            'database_error' => $dbError,
            'logs_writable' => is_dir(dirname(__DIR__, 2) . '/storage/logs') && is_writable(dirname(__DIR__, 2) . '/storage/logs'),
            'session_status' => session_status() === PHP_SESSION_ACTIVE ? 'active' : 'inactive',
            'app_env' => $GLOBALS['app_config']['app']['env'] ?? 'unknown',
            'tables' => $tableStatus,
        ];
    }
}
