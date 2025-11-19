<?php
namespace Hospital\Repositories;

use Hospital\DB;

class UsersRepository
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = DB::getPDO();
    }

    public function getByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, username, role FROM users WHERE username = :u LIMIT 1');
        $stmt->execute([':u' => $username]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function updatePassword(string $username, string $newHash): bool
    {
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash = :h WHERE username = :u');
        return $stmt->execute([':h' => $newHash, ':u' => $username]);
    }
}
