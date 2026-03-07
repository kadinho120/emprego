<?php

namespace App;

use App\Config\Database;
use PDO;

class Auth
{
    public static function init()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isLoggedIn()
    {
        self::init();
        return isset($_SESSION['user_id']);
    }

    public static function isAdmin()
    {
        self::init();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            $redirect = urlencode($_SERVER['REQUEST_URI']);
            header("Location: login.php?redirect=$redirect");
            exit;
        }
    }

    public static function checkEmail($email)
    {
        // Admin check from env
        $adminEmail = getenv('ADMIN_EMAIL');
        if ($email === $adminEmail) {
            return ['status' => 'exists', 'has_password' => true, 'role' => 'admin'];
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            return [
                'status' => 'exists',
                'has_password' => !empty($user['password_hash']),
                'role' => $user['role']
            ];
        }

        return ['status' => 'not_found'];
    }

    public static function login($email, $password)
    {
        self::init();

        // Admin check from env
        $adminEmail = getenv('ADMIN_EMAIL');
        $adminPass = getenv('ADMIN_PASSWORD');

        if ($email === $adminEmail && $password === $adminPass) {
            $_SESSION['user_id'] = 'admin';
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'admin';
            return true;
        }

        // Regular user check
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $user['password_hash'] && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }

        return false;
    }

    public static function registerPassword($email, $password)
    {
        $db = Database::getInstance();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Only set password if it doesn't have one (first access)
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE email = ? AND (password_hash IS NULL OR password_hash = '')");
        if ($stmt->execute([$hash, $email]) && $stmt->rowCount() > 0) {
            return self::login($email, $password);
        }
        return false;
    }

    public static function logout()
    {
        self::init();
        session_destroy();
    }
}
