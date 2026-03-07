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
    $stmtInsert = $db->prepare("INSERT INTO resumes (template_id, full_name, email, user_id, phone, city, state, photo_path, linkedin, website, summary, slug) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id");

    $newSlug = ($original['slug'] ? $original['slug'] . '-copy-' . time() : null);

    $stmtInsert->execute([
        $original['template_id'],
        $original['full_name'] . ' (Cópia)',
        $original['email'],
        $userId,
        $original['phone'],
        $original['city'],
        $original['state'],
        $original['photo_path'],
        $original['linkedin'],
        $original['website'],
        $original['summary'],
        $newSlug
    ]);
    $newId = $stmtInsert->fetchColumn();

    // 3. Duplicate Experiences
    $stmtExp = $db->prepare("SELECT * FROM experiences WHERE resume_id = ?");
    $stmtExp->execute([$resumeId]);
    $experiences = $stmtExp->fetchAll();

    $stmtInsertExp = $db->prepare("INSERT INTO experiences (resume_id, company, position, start_date, end_date, description, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($experiences as $exp) {
        $stmtInsertExp->execute([$newId, $exp['company'], $exp['position'], $exp['start_date'], $exp['end_date'], $exp['description'], $exp['sort_order']]);
    }

    // 4. Duplicate Education
    $stmtEdu = $db->prepare("SELECT * FROM education WHERE resume_id = ?");
    $stmtEdu->execute([$resumeId]);
    $education = $stmtEdu->fetchAll();

    $stmtInsertEdu = $db->prepare("INSERT INTO education (resume_id, institution, degree, field_of_study, graduation_date, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($education as $edu) {
        $stmtInsertEdu->execute([$newId, $edu['institution'], $edu['degree'], $edu['field_of_study'], $edu['graduation_date'], $edu['sort_order']]);
    }

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
    header('Location: dashboard.php?error=Erro ao duplicar: ' . $e->getMessage());
}
exit;
