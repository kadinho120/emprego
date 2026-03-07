<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Auth;
use App\Config\Database;

Auth::requireLogin();
Auth::init();

$db = Database::getInstance();
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? 'user';

$resumeData = null;
$experiencesData = [];
$educationData = [];
$skillsData = "";

if (isset($_GET['id'])) {
    $resumeId = (int) $_GET['id'];

    if ($userRole === 'admin') {
        $stmt = $db->prepare("SELECT * FROM resumes WHERE id = ?");
        $stmt->execute([$resumeId]);
    } else {
        $stmt = $db->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
        $stmt->execute([$resumeId, $userId]);
    }

    $resumeData = $stmt->fetch();

    if ($resumeData) {
        $stmt = $db->prepare("SELECT * FROM experiences WHERE resume_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$resumeId]);
        $experiencesData = $stmt->fetchAll();
        if (empty($experiencesData))
            $experiencesData = [[]]; // At least one for the form

        $stmt = $db->prepare("SELECT * FROM education WHERE resume_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$resumeId]);
        $educationData = $stmt->fetchAll();
        if (empty($educationData))
            $educationData = [[]]; // At least one for the form

        $stmt = $db->prepare("SELECT * FROM skills WHERE resume_id = ?");
        $stmt->execute([$resumeId]);
        $skillsList = $stmt->fetchAll();
        $skillsData = implode(', ', array_column($skillsList, 'skill_name'));
    }
}

if (!$resumeData) {
    $experiencesData = [[]];
    $educationData = [[]];
}

$currentNiche = $resumeData['niche'] ?? ($_GET['niche'] ?? 'tech');
$initialTemplate = $resumeData['template_id'] ?? ($currentNiche === 'health' ? 'health' : 'tech');

require_once __DIR__ . '/views/generator.view.php';