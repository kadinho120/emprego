<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Auth;
use App\Config\Database;

Auth::requireLogin();
Auth::init();

$db = Database::getInstance();
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];

// Fetch User Resumes (Admins see all)
if ($userRole === 'admin') {
    $stmt = $db->query("SELECT * FROM resumes ORDER BY created_at DESC");
} else {
    $stmt = $db->prepare("SELECT * FROM resumes WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
}
$resumes = $stmt->fetchAll();

require_once __DIR__ . '/views/dashboard.view.php';