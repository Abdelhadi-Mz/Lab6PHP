<?php
declare(strict_types=1);

namespace App\Database;

use PDO; use PDOException; use App\Log\Logger;

class DBConnection
{
    /** @var PDO|null */
    private static $pdo = null;
    /** @var Logger */
    private static $logger;

    public static function init(Logger $logger): void
    {
        self::$logger = $logger;
    }

    public static function get(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        $config = require __DIR__ . '/../../config/db.php';
        $dsn  = $config['dsn'];
        $user = $config['user'];
        $pass = $config['pass'];
        $opts = $config['options'] ?? [];
        try {
            self::$pdo = new PDO($dsn, $user, $pass, $opts);
            // S’assurer du bon encodage au cas où
            self::$pdo->exec('SET NAMES utf8mb4');
            return self::$pdo;
        } catch (PDOException $e) {
            if (self::$logger) {
                self::$logger->error('PDO connection failed', ['method' => __METHOD__, 'dsn' => $dsn, 'error' => $e->getMessage()]);
            }
            throw $e; // laisser remonter après log
        }
    }
}