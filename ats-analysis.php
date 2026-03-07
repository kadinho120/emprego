<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Config\Database;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['error' => 'Apenas POST permitido']));
}

$resumeId = $_POST['resume_id'] ?? null;
$jobDescription = $_POST['job_description'] ?? '';

if (!$resumeId || empty($jobDescription)) {
    die(json_encode(['error' => 'Dados incompletos']));
}

try {
    $db = Database::getInstance();

    // Fetch resume content
    $stmt = $db->prepare("SELECT summary FROM resumes WHERE id = ?");
    $stmt->execute([$resumeId]);
    $resume = $stmt->fetch();

    $stmtExp = $db->prepare("SELECT description FROM experiences WHERE resume_id = ?");
    $stmtExp->execute([$resumeId]);
    $experiences = $stmtExp->fetchAll(PDO::FETCH_COLUMN);

    $stmtSkills = $db->prepare("SELECT skill_name FROM skills WHERE resume_id = ?");
    $stmtSkills->execute([$resumeId]);
    $skills = $stmtSkills->fetchAll(PDO::FETCH_COLUMN);

    $resumeText = $resume['summary'] . ' ' . implode(' ', $experiences) . ' ' . implode(' ', $skills);
    $resumeText = strtolower($resumeText);

    // Extract keywords from job description (simple version: top words > 3 chars)
    $jobWords = preg_split('/\W+/', strtolower($jobDescription), -1, PREG_SPLIT_NO_EMPTY);
    $stopWords = ['para', 'com', 'uma', 'este', 'esta', 'como', 'pode', 'ser', 'mais', 'pelo', 'sendo', 'sobre', 'entre', 'quando', 'onde', 'quem', 'qual', 'quais'];

    $relevantKeywords = [];
    foreach ($jobWords as $word) {
        if (strlen($word) > 3 && !in_array($word, $stopWords)) {
            $relevantKeywords[$word] = ($relevantKeywords[$word] ?? 0) + 1;
        }
    }

    arsort($relevantKeywords);
    $topKeywords = array_slice(array_keys($relevantKeywords), 0, 15);

    $matches = [];
    $missing = [];

    foreach ($topKeywords as $keyword) {
        if (strpos($resumeText, $keyword) !== false) {
            $matches[] = $keyword;
        } else {
            $missing[] = $keyword;
        }
    }

    $score = count($topKeywords) > 0 ? round((count($matches) / count($topKeywords)) * 100) : 0;

    echo json_encode([
        'score' => $score,
        'matches' => $matches,
        'missing' => $missing,
        'top_keywords' => $topKeywords
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
