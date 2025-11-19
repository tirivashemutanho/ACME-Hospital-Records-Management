<?php
namespace Hospital;

class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function hasRole(string $role): bool
    {
        $u = self::user();
        if (!$u) return false;
        return isset($u['role']) && $u['role'] === $role;
    }

    /** Require one or more roles (string or array), redirect/forbid on failure */
    public static function requireRole($roles): void
    {
        $u = self::user();
        if (!$u) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Forbidden';
            exit;
        }
        $allowed = is_array($roles) ? $roles : [$roles];
        if (!in_array($u['role'] ?? '', $allowed, true)) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Forbidden';
            exit;
        }
    }
}
