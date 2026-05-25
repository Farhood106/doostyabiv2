<?php
namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $config = $GLOBALS['app_config'] ?? null;
            if (!$config || empty($config['db'])) {
                throw new SetupException('Database configuration is missing.');
            }
            $db = $config['db'];
            // The project requires utf8mb4 end-to-end so Persian text, emoji, and other Unicode content are stored safely.
            $charset = 'utf8mb4';
            $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$charset}";
            self::$pdo = new PDO($dsn, $db['user'], $db['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}",
            ]);
        }
        return self::$pdo;
    }
}
