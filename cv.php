<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Config\Database;

use App\Renderer\ResumeRenderer;

$id = $_GET['id'] ?? null;
$slug = $_GET['slug'] ?? null;

if (!$id && !$slug) {
    die("Currículo não encontrado.");
}

try {
    $db = Database::getInstance();

    if ($slug) {
        $stmt = $db->prepare("SELECT * FROM resumes WHERE slug = ?");
        $stmt->execute([$slug]);
    } else {
        $stmt = $db->prepare("SELECT * FROM resumes WHERE id = ?");
        $stmt->execute([$id]);
    }

    $resume = $stmt->fetch();

    if (!$resume) {
        die("Currículo não encontrado.");
    }

    // Increment views
    $db->prepare("UPDATE resumes SET views = views + 1 WHERE id = ?")->execute([$resume['id']]);

    // Fetch related data
    $stmtExp = $db->prepare("SELECT * FROM experiences WHERE resume_id = ? ORDER BY sort_order ASC, id ASC");
    $stmtExp->execute([$resume['id']]);
    $experiences = $stmtExp->fetchAll();

    $stmtEdu = $db->prepare("SELECT * FROM education WHERE resume_id = ? ORDER BY sort_order ASC, id ASC");
    $stmtEdu->execute([$resume['id']]);
    $education = $stmtEdu->fetchAll();

    $stmtSkills = $db->prepare("SELECT * FROM skills WHERE resume_id = ? ORDER BY id ASC");
    $stmtSkills->execute([$resume['id']]);
    $skills = $stmtSkills->fetchAll();

    // Render HTML using central renderer
    echo ResumeRenderer::render($resume, $experiences, $education, $skills);

} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}
