<?php
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Config/Database.php';

use App\Auth;
use App\Config\Database;

Auth::requireLogin();

header('Content-Type: application/json');

$resumeId = $_POST['resume_id'] ?? null;

if (!$resumeId) {
    echo json_encode(['success' => false, 'error' => 'ID do currículo não fornecido.']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Get Resume Data (Admin bypasses user_id check)
    if (Auth::isAdmin()) {
        $stmt = $db->prepare("SELECT * FROM resumes WHERE id = ?");
        $stmt->execute([$resumeId]);
    } else {
        $stmt = $db->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
        $stmt->execute([$resumeId, $_SESSION['user_id']]);
    }
    $resume = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$resume) {
        echo json_encode(['success' => false, 'error' => 'Currículo não encontrado.']);
        exit;
    }

    // Get Experiences
    $stmt = $db->prepare("SELECT * FROM experiences WHERE resume_id = ? ORDER BY id ASC");
    $stmt->execute([$resumeId]);
    $experiences = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    // Get Education
    $stmt = $db->prepare("SELECT * FROM education WHERE resume_id = ? ORDER BY id ASC");
    $stmt->execute([$resumeId]);
    $education = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    // Get Skills
    $stmt = $db->prepare("SELECT * FROM skills WHERE resume_id = ? ORDER BY id ASC");
    $stmt->execute([$resumeId]);
    $skills = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    // Prepare full text for AI
    $resumeContent = "NOME: " . $resume['full_name'] . "\n";
    $resumeContent .= "RESUMO: " . $resume['summary'] . "\n\n";
    
    $resumeContent .= "EXPERIÊNCIAS:\n";
    foreach ($experiences as $exp) {
        $resumeContent .= "- " . $exp['position'] . " na " . $exp['company'] . " (" . $exp['start_date'] . " - " . ($exp['end_date'] ?: 'Atual') . "): " . $exp['description'] . "\n";
    }
    
    $resumeContent .= "\nFORMAÇÃO:\n";
    foreach ($education as $edu) {
        $resumeContent .= "- " . $edu['degree'] . " em " . $edu['field_of_study'] . " na " . $edu['institution'] . " (Conclusão: " . $edu['graduation_date'] . ")\n";
    }
    
    $resumeContent .= "\nHABILIDADES: " . implode(', ', array_column($skills, 'name')) . "\n";

    echo json_encode([
        'success' => true,
        'resume_text' => $resumeContent,
        'niche' => $resume['niche']
    ]);

} catch (\Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
}
