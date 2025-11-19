<?php
namespace Hospital\Repositories;

use Hospital\DB;

class AuditRepository
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = DB::getPDO();
    }

    public function log(string $actor, string $action, ?string $target = null, array $meta = []): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO audit (actor, action, target, meta, created_at) VALUES (:actor,:action,:target,:meta,:created)');
        $stmt->execute([
            ':actor' => $actor,
            ':action' => $action,
            ':target' => $target,
            ':meta' => json_encode($meta),
            ':created' => date('c')
        ]);
    }
}
