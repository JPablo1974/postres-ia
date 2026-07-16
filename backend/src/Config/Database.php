<?php
declare(strict_types=1);

namespace App\Config;

use PDO;

/** Conexion PDO a MySQL (singleton). */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host = (string) Config::get('DB_HOST', '127.0.0.1');
        $name = (string) Config::get('DB_NAME', 'postres_ia');
        $user = (string) Config::get('DB_USER', 'root');
        $pass = (string) Config::get('DB_PASS', '');

        $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

        self::$pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return self::$pdo;
    }
}
