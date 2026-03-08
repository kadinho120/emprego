<?php
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Config/Database.php';

use App\Auth;
use App\Config\Database;

// Auto-save requires login
Auth::requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método inválido.']);
    exit;
}

$resumeId = $_POST['resume_id'] ?? null;
$userId = $_SESSION['user_id'];
$isAdmin = Auth::isAdmin();

// Convert 'admin' string to null for DB compliance
$dbUserId = $isAdmin ? null : $userId;
$fullName = $_POST['full_name'] ?? 'Sem Nome';
$summary = $_POST['summary'] ?? '';
$niche = $_POST['niche'] ?? 'tech';
$templateId = $_POST['template_id'] ?? 'tech';
$phone = $_POST['phone'] ?? '';
$city = $_POST['city'] ?? '';
$state = $_POST['state'] ?? '';
$primaryColor = $_POST['primary_color'] ?? '#6366f1';
$fontFamily = $_POST['font_family'] ?? 'jakarta';
$photoPath = $_POST['photo_path'] ?? '';
$photoBase64 = $_POST['photo_base64'] ?? '';

// Handle photo path if base64 is provided in auto-save
if ($photoBase64) {
    $photoPath = $photoBase64;
}

try {
    $db = Database::getInstance();
    
    if ($resumeId) {
        // Update existing resume
        if ($isAdmin) {
            $stmt = $db->prepare("
                UPDATE resumes 
                SET full_name = ?, summary = ?, niche = ?, template_id = ?, 
                    phone = ?, city = ?, state = ?, primary_color = ?, 
                    font_family = ?, photo_path = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([
                $fullName, $summary, $niche, $templateId, 
                $phone, $city, $state, $primaryColor, 
                $fontFamily, $photoPath, $resumeId
            ]);
        } else {
            $stmt = $db->prepare("
                UPDATE resumes 
                SET full_name = ?, summary = ?, niche = ?, template_id = ?, 
                    phone = ?, city = ?, state = ?, primary_color = ?, 
                    font_family = ?, photo_path = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([
                $fullName, $summary, $niche, $templateId, 
                $phone, $city, $state, $primaryColor, 
                $fontFamily, $photoPath, $resumeId, $userId
            ]);
        }
    } else {
        // Create new resume as draft
        $slug = bin2hex(random_bytes(8));
        $stmt = $db->prepare("
            INSERT INTO resumes (
                user_id, full_name, summary, niche, template_id, 
                phone, city, state, primary_color, font_family, 
                photo_path, slug, views
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0) 
            RETURNING id
        ");
        $stmt->execute([
            $dbUserId, $fullName, $summary, $niche, $templateId, 
            $phone, $city, $state, $primaryColor, $fontFamily, 
            $photoPath, $slug
        ]);
        $resumeId = $stmt->fetchColumn();
    }

    // Process Experiences (Simple replace for draft)
    $stmt = $db->prepare("DELETE FROM experiences WHERE resume_id = ?");
    $stmt->execute([$resumeId]);
    
    if (isset($_POST['experience']) && is_array($_POST['experience'])) {
        foreach ($_POST['experience'] as $exp) {
            if (!empty($exp['company']) || !empty($exp['position'])) {
                $stmt = $db->prepare("INSERT INTO experiences (resume_id, company, position, start_date, end_date, description) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$resumeId, $exp['company'], $exp['position'], $exp['start_date'], $exp['end_date'], $exp['description']]);
            }
        }
    }

    // Process Education
    $stmt = $db->prepare("DELETE FROM education WHERE resume_id = ?");
    $stmt->execute([$resumeId]);
    
    if (isset($_POST['education']) && is_array($_POST['education'])) {
        foreach ($_POST['education'] as $edu) {
            if (!empty($edu['institution'])) {
                $stmt = $db->prepare("INSERT INTO education (resume_id, institution, degree, field_of_study, graduation_date) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$resumeId, $edu['institution'], $edu['degree'], $edu['field_of_study'], $edu['graduation_date']]);
            }
        }
    }

    // Process Skills
    $stmt = $db->prepare("DELETE FROM skills WHERE resume_id = ?");
    $stmt->execute([$resumeId]);
    
    if (isset($_POST['skills']) && !empty($_POST['skills'])) {
        $skillsArr = array_map('trim', explode(',', $_POST['skills']));
        foreach ($skillsArr as $skillName) {
            if ($skillName) {
                $stmt = $db->prepare("INSERT INTO skills (resume_id, name) VALUES (?, ?)");
                $stmt->execute([$resumeId, $skillName]);
            }
        }
    }

    echo json_encode(['success' => true, 'resume_id' => $resumeId]);

} catch (\Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
