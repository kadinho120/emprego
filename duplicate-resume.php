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

    if (!is_numeric($userId)) {
        $userId = null;
    }

    // 1. Fetch original resume
    $stmt = $db->prepare("SELECT * FROM resumes WHERE id = ?");
    $stmt->execute([$resumeId]);
    $original = $stmt->fetch();

    if (!$original) {
        header('Location: dashboard.php?error=Currículo não encontrado');
        exit;
    }

    // Security check
    if ($original['user_id'] != $userId && $userRole !== 'admin') {
        header('Location: dashboard.php?error=Acesso negado');
        exit;
    }

    // 2. Duplicate Resume
    $newSlug = ($original['slug'] ? $original['slug'] . '-copy-' . time() : null);
    $stmtInsert = $db->prepare("INSERT INTO resumes (template_id, full_name, email, user_id, phone, city, state, photo_path, linkedin, website, summary, slug, primary_color, font_family)
                                SELECT template_id, CONCAT(full_name, ' (Cópia)'), email, ?, phone, city, state, photo_path, linkedin, website, summary, ?, primary_color, font_family
                                FROM resumes WHERE id = ? RETURNING id");
    $stmtInsert->execute([$userId, $newSlug, $resumeId]);
    $newId = $stmtInsert->fetchColumn();

    // 3. Duplicate Experiences
    $stmtExp = $db->prepare("INSERT INTO experiences (resume_id, company, position, start_date, end_date, description, sort_order)
                             SELECT ?, company, position, start_date, end_date, description, sort_order
                             FROM experiences WHERE resume_id = ?");
    $stmtExp->execute([$newId, $resumeId]);

    // 4. Duplicate Education
    $stmtEdu = $db->prepare("INSERT INTO education (resume_id, institution, degree, field_of_study, graduation_date, sort_order)
                             SELECT ?, institution, degree, field_of_study, graduation_date, sort_order
                             FROM education WHERE resume_id = ?");
    $stmtEdu->execute([$newId, $resumeId]);

    // 5. Duplicate Skills
    $stmtSkills = $db->prepare("SELECT * FROM skills WHERE resume_id = ?");
    $stmtSkills->execute([$resumeId]);
    $skills = $stmtSkills->fetchAll();

    $stmtInsertSkill = $db->prepare("INSERT INTO skills (resume_id, skill_name, category) VALUES (?, ?, ?)");
    foreach ($skills as $skill) {
        $stmtInsertSkill->execute([$newId, $skill['skill_name'], $skill['category']]);
    }

    header('Location: dashboard.php?success=Currículo duplicado com sucesso');
} catch (Exception $e) {
    $errorMsg = str_replace(["\r", "\n"], ' ', $e->getMessage());
    header('Location: dashboard.php?error=Erro ao duplicar: ' . urlencode($errorMsg));
}
exit;
