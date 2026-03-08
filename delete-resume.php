<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Auth;
use App\Config\Database;

Auth::init();
Auth::requireLogin();

$resumeId = $_GET['id'] ?? null;
if (!$resumeId) {
    header('Location: dashboard.php?error=ID não fornecido');
    exit;
}

try {
    $db = Database::getInstance();
    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['user_role'] ?? 'user';

    // 1. Fetch resume to check ownership and get photo path
    $stmt = $db->prepare("SELECT user_id, photo_path FROM resumes WHERE id = ?");
    $stmt->execute([$resumeId]);
    $resume = $stmt->fetch();

    if (!$resume) {
        header('Location: dashboard.php?error=Currículo não encontrado');
        exit;
    }

    // Security Check: Only owner or admin
    $isAdmin = Auth::isAdmin();
    if ($resume['user_id'] != $userId && !$isAdmin) {
        header('Location: dashboard.php?error=Acesso negado');
        exit;
    }

    // 2. Delete Photo File if exists
    if ($resume['photo_path'] && file_exists(__DIR__ . '/' . $resume['photo_path'])) {
        unlink(__DIR__ . '/' . $resume['photo_path']);
    }

    // 3. Delete from Database (CASCADE will handle experiences, education, etc.)
    $stmtDelete = $db->prepare("DELETE FROM resumes WHERE id = ?");
    $stmtDelete->execute([$resumeId]);

    header('Location: dashboard.php?success=Currículo excluído com sucesso');
} catch (Exception $e) {
    header('Location: dashboard.php?error=Erro ao excluir: ' . $e->getMessage());
}
exit;
