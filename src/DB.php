<?php

namespace Hospital;

class DB
{
    private static ?\PDO $pdo = null;
    private static string $path;

    public static function getPath(): string
    {
        if (!isset(self::$path)) {
            self::$path = __DIR__ . '/../data/hospital.db';
        }
        return self::$path;
    }

    public static function getPDO(): \PDO
    {
        if (self::$pdo === null) {
            $dsn = 'sqlite:' . self::getPath();
            self::$pdo = new \PDO($dsn);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        return self::$pdo;
    }
}
