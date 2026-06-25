<?php

if (!function_exists('ensure_user_role_schema')) {
    function ensure_user_role_schema(): void
    {
        global $mysqli;

        if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
            return;
        }

        if ($chk = @$mysqli->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role'")) {
            @$chk->execute();
            @$chk->bind_result($cntRole);
            @$chk->fetch();
            @$chk->close();
            if ((int)$cntRole === 0) {
                @$mysqli->query("ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'admin' AFTER password");
            }
        }
    }
}

ensure_user_role_schema();

if (!function_exists('current_user_role')) {
    function current_user_role(): string
    {
        global $mysqli;

        if (!function_exists('is_logged_in') || !is_logged_in()) {
            return 'admin';
        }

        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        if ($userId <= 0 || !isset($mysqli) || !($mysqli instanceof mysqli)) {
            return 'admin';
        }

        $role = 'admin';
        if ($stmt = $mysqli->prepare('SELECT role FROM users WHERE id = ? LIMIT 1')) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stmt->bind_result($roleDb);
            if ($stmt->fetch()) {
                $role = (string)$roleDb;
            }
            $stmt->close();
        }

        return $role === 'panitia' ? 'panitia' : 'admin';
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        return current_user_role() === 'admin';
    }
}

if (!function_exists('require_admin')) {
    function require_admin(): void
    {
        if (function_exists('require_login')) {
            require_login();
        }

        if (!is_admin()) {
            if (function_exists('flash')) {
                flash('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }
            header('Location: ' . base_url('admin/dashboard'));
            exit;
        }
    }
}

if (!function_exists('user_initials')) {
    function user_initials(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return 'U';
        }

        $parts = preg_split('/\s+/', $name);
        $initials = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            if (preg_match('/./u', $part, $match)) {
                $initials .= strtoupper($match[0]);
            }
            if (strlen($initials) >= 2) {
                break;
            }
        }

        return $initials !== '' ? $initials : 'U';
    }
}
