<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Config\Database;

$resumeId = $_GET['id'] ?? null;
if (!$resumeId)
    die("ID não fornecido.");

try {
    $db = Database::getInstance();

    // Fetch Resume Data
    $stmt = $db->prepare("SELECT * FROM resumes WHERE id = ?");
    $stmt->execute([$resumeId]);
    $resume = $stmt->fetch();

    if (!$resume)
        die("Currículo não encontrado.");

    // Fetch Experience
    $stmt = $db->prepare("SELECT * FROM experiences WHERE resume_id = ? ORDER BY id ASC");
    $stmt->execute([$resumeId]);
    $experiences = $stmt->fetchAll();

    // Fetch Education
    $stmt = $db->prepare("SELECT * FROM education WHERE resume_id = ? ORDER BY id ASC");
    $stmt->execute([$resumeId]);
    $education = $stmt->fetchAll();

    // Fetch Skills
    $stmt = $db->prepare("SELECT * FROM skills WHERE resume_id = ?");
    $stmt->execute([$resumeId]);
    $skills = $stmt->fetchAll();

} catch (Exception $e) {
    die("Erro no banco: " . $e->getMessage());
}

// Function to generate HTML for the PDF
function getResumeHtml($resume, $experiences, $education, $skills, $fontSize = 12, $lineHeight = 1.4)
{
    $template = $resume['template_id'] ?? 'modern';
    $skillsArr = array_map(function ($s) {
        return $s['skill_name'];
    }, $skills);
    $skillsText = implode(' • ', $skillsArr);

    $expHtml = '';
    foreach ($experiences as $exp) {
        $expHtml .= "
        <div class='section-item'>
            <div class='item-header'>
                <span class='company'>{$exp['company']}</span>
                <span class='date'>{$exp['start_date']} – {$exp['end_date']}</span>
            </div>
            <div class='item-sub'>{$exp['position']}</div>
            <div class='item-desc'>" . nl2br($exp['description']) . "</div>
        </div>";
    }

    $eduHtml = '';
    foreach ($education as $edu) {
        $eduHtml .= "
        <div class='section-item'>
            <div class='item-header'>
                <span class='company'>{$edu['institution']}</span>
                <span class='date'>{$edu['graduation_date']}</span>
            </div>
            <div class='item-sub'>{$edu['degree']}</div>
        </div>";
    }

    // Define CSS based on template
    $css = "";
    if ($template === 'health') {
        $css = "
            body { font-family: 'DejaVu Sans', sans-serif; font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #334155; margin: 0; padding: 0; }
            .header { background: #f0fdfa; border-bottom: 6px solid #0d9488; padding: 30px 40px; margin-bottom: 30px; border-radius: 0 0 20px 20px; text-align: center; }
            .photo-container { width: 105px; height: 140px; margin: 0 auto 15px auto; border-radius: 15px; border: 3px solid #0d9488; overflow: hidden; background: #e2e8f0; }
            .photo { width: 100%; height: 100%; object-fit: cover; }
            .name { font-size: 28pt; font-weight: 800; color: #0f766e; text-transform: capitalize; margin: 0; }
            .contact { font-size: 10.5pt; color: #64748b; margin-top: 10px; font-weight: 600; }
            .content-wrapper { padding: 0 40px; }
            .section-title { font-size: 11pt; font-weight: 800; color: #ffffff; background: #0d9488; padding: 5px 15px; margin-top: 25px; margin-bottom: 15px; border-radius: 6px; display: inline-block; text-transform: uppercase; letter-spacing: 1.5px; }
            .section-item { margin-bottom: 20px; }
            .company { font-weight: bold; color: #1e293b; font-size: 1.1em; display: inline-block; }
            .date { color: #64748b; float: right; font-size: 0.85em; background: #f1f5f9; padding: 3px 10px; border-radius: 12px; font-weight: 600; }
            .item-sub { color: #0d9488; font-weight: 700; margin-bottom: 8px; display: block; font-size: 1.05em; }
            .item-desc { border-left: 3px solid #ccfbf1; padding-left: 15px; margin-left: 5px; text-align: justify; color: #475569; }
            .skills-box { border: 2px dashed #99f6e4; padding: 15px; color: #0f766e; font-weight: 700; border-radius: 10px; background: #f0fdfa; line-height: 1.6; }
        ";
    } else { // tech
        $css = "
            body { font-family: 'DejaVu Sans', sans-serif; font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #e2e8f0; background-color: #0f172a; margin: 0; padding: 0; }
            .header { background: #1e293b; color: #2dd4bf; padding: 30px 40px; text-align: left; border-bottom: 6px solid #2dd4bf; margin-bottom: 30px; overflow: hidden; }
            .header-text { float: left; width: 75%; }
            .photo-container { float: right; width: 105px; height: 140px; border-radius: 12px; border: 2px solid #2dd4bf; overflow: hidden; background: #0f172a; }
            .photo { width: 100%; height: 100%; object-fit: cover; }
            .name { font-size: 30pt; font-weight: 900; color: #2dd4bf; text-transform: uppercase; margin: 0; }
            .contact { font-size: 10pt; color: #94a3b8; font-weight: 600; font-family: 'Courier New', monospace; margin-top: 10px; }
            .content-wrapper { padding: 0 40px; }
            .section-title { font-size: 13pt; font-weight: 800; color: #2dd4bf; margin-top: 30px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 2px; border-bottom: 2px solid rgba(45, 212, 191, 0.3); padding-bottom: 5px; }
            .section-item { margin-bottom: 25px; border-bottom: 1px solid rgba(148, 163, 184, 0.1); padding-bottom: 15px; }
            .company { font-weight: 800; color: #f8fafc; font-size: 1.2em; display: inline-block; }
            .date { color: #2dd4bf; font-weight: bold; font-family: 'Courier New', monospace; font-size: 0.9em; float: right; padding-top: 5px; }
            .item-sub { color: #94a3b8; font-weight: 700; margin-bottom: 10px; display: block; font-size: 1.05em; }
            .item-desc { color: #cbd5e1; text-align: justify; padding-right: 10px; }
            .skills-box { background: rgba(45, 212, 191, 0.1); border: 1px solid #2dd4bf; padding: 15px; border-radius: 12px; color: #2dd4bf; font-family: 'Courier New', monospace; font-weight: bold; line-height: 1.6; }
        ";
    }

    $html = "
    <!DOCTYPE html>
    <html lang='pt-br'>
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
        <style>
            @page { margin: 1cm 1.5cm; }
            * { box-sizing: border-box; -webkit-print-color-adjust: exact; }
            body { font-family: 'DejaVu Sans', sans-serif; }
            {$css}
            .section-item { margin-bottom: 15px; clear: both; page-break-inside: avoid; }
            .item-header { margin-bottom: 3px; overflow: hidden; }
            .item-desc { margin-top: 3px; text-align: justify; }
            .section-title { page-break-after: avoid; }
        </style>
    </head>
    <body style='margin: 0; padding: 0;'>
        <div class='header'>
            " . (!empty($resume['photo_path']) && file_exists(__DIR__ . "/" . $resume['photo_path']) ? "
            <div class='photo-container'>
                <img src='data:image/" . pathinfo($resume['photo_path'], PATHINFO_EXTENSION) . ";base64," . base64_encode(file_get_contents(__DIR__ . "/" . $resume['photo_path'])) . "' class='photo'>
            </div>
            " : "") . "
            <div class='header-text' style='" . (empty($resume['photo_path']) || !file_exists(__DIR__ . "/" . $resume['photo_path']) || $template === 'health' ? "width: 100%;" : "") . "'>
                <div class='name'>{$resume['full_name']}</div>
                <div class='contact'>
                    {$resume['city']} - {$resume['state']} | {$resume['email']} | {$resume['phone']}
                </div>
            </div>
            <div style='clear: both;'></div>
        </div>

        <div class='content-wrapper'>
            " . (!empty(trim($resume['summary'])) ? "
            <div style='page-break-inside: avoid;'>
                <div class='section-title'>Resumo</div>
                <div class='item-desc'>" . nl2br(htmlspecialchars($resume['summary'])) . "</div>
            </div>
            " : "") . "

            " . (!empty($experiences) ? "
            <div class='section-title'>Experiência</div>
            {$expHtml}
            " : "") . "

            " . (!empty($education) ? "
            <div class='section-title'>Educação</div>
            {$eduHtml}
            " : "") . "

            " . (!empty($skills) ? "
            <div style='page-break-inside: avoid;'>
                <div class='section-title'>Habilidades</div>
                <div class='skills-box'>{$skillsText}</div>
            </div>
            " : "") . "
        </div>
    </body>
    </html>
    ";
    return $html;
}

// PDF Generation with Auto-Fit Algorithm
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

$fontSize = 11;
$lineHeight = 1.3;
$maxIterations = 10;
$iteration = 0;

// Final PDF Generation (Single Pass, Multi-page support)
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);

// Fixed sizes for better readability and multi-page stability
$fontSize = 11;
$lineHeight = 1.35;

$html = getResumeHtml($resume, $experiences, $education, $skills, $fontSize, $lineHeight);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Final output
$dompdf->stream("Curriculo_" . str_replace(' ', '_', $resume['full_name']) . ".pdf", ["Attachment" => false]);
