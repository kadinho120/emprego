<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Config\Database;
use App\Auth;
use App\Renderer\ResumeRenderer;

Auth::requireLogin();

$resumeId = $_GET['id'] ?? null;
if (!$resumeId)
    die("ID não fornecido.");

try {
    $db = Database::getInstance();

    // Fetch Resume Data with ownership check
    $currentUserId = $_SESSION['user_id'] ?? 0;
    if (!is_numeric($currentUserId))
        $currentUserId = 0; // Fallback for 'admin' string

    $stmt = $db->prepare("SELECT * FROM resumes WHERE id = ? AND (user_id = ? OR 'admin' = ?)");
    $stmt->execute([$resumeId, $currentUserId, $_SESSION['user_role'] ?? '']);
    $resume = $stmt->fetch();

    if (!$resume)
        die("Currículo não encontrado.");

    // Fetch Experience
    $stmt = $db->prepare("SELECT * FROM experiences WHERE resume_id = ? ORDER BY sort_order ASC, id DESC");
    $stmt->execute([$resumeId]);
    $experiences = $stmt->fetchAll();

    // Fetch Education
    $stmt = $db->prepare("SELECT * FROM education WHERE resume_id = ? ORDER BY sort_order ASC, id DESC");
    $stmt->execute([$resumeId]);
    $education = $stmt->fetchAll();

    // Fetch Skills
    $stmt = $db->prepare("SELECT * FROM skills WHERE resume_id = ? ORDER BY id ASC");
    $stmt->execute([$resumeId]);
    $skills = $stmt->fetchAll();

} catch (Exception $e) {
    die("Erro no banco: " . $e->getMessage());
}

// PDF Generation
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);

// Fixed sizes for better readability and multi-page stability
$fontSize = 11;
$lineHeight = 1.35;

$html = ResumeRenderer::render($resume, $experiences, $education, $skills, $fontSize, $lineHeight, true);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Final output
$dompdf->stream("Curriculo_" . str_replace(' ', '_', $resume['full_name']) . ".pdf", ["Attachment" => false]);
