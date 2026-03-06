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
            body { font-family: 'Helvetica', sans-serif; font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #2d3748; margin: 0; }
            .header { background: #f0fff4; border-bottom: 4px solid #319795; padding: 20px; margin-bottom: 25px; border-radius: 0 0 15px 15px; }
            .name { font-size: 24pt; font-weight: bold; color: #2c7a7b; text-align: center; }
            .contact { font-size: 10pt; color: #4a5568; margin-top: 5px; text-align: center; font-weight: 500; }
            .content-wrapper { padding: 0 20px; }
            .section-title { font-size: 11pt; font-weight: 800; color: #ffffff; background: #319795; padding: 4px 12px; margin-top: 15px; margin-bottom: 10px; border-radius: 4px; display: inline-block; text-transform: uppercase; letter-spacing: 1px; }
            .company { font-weight: bold; color: #2d3748; display: block; }
            .date { color: #718096; float: right; font-size: 0.85em; background: #edf2f7; padding: 2px 8px; border-radius: 10px; }
            .item-sub { color: #38b2ac; font-weight: 700; margin-bottom: 5px; display: block; }
            .item-desc { border-left: 2px solid #e2e8f0; padding-left: 15px; margin-left: 5px; text-align: justify; }
            .skills-box { border: 1px dashed #319795; padding: 10px; color: #2c7a7b; font-weight: 600; border-radius: 5px; background: #f0fff4; }
        ";
    } else { // tech
        $css = "
            body { font-family: 'Helvetica', sans-serif; font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #e2e8f0; background-color: #0f172a; margin: 0; padding: 0; }
            .header { background: #1e293b; color: #2dd4bf; padding: 40px; text-align: left; border-bottom: 6px solid #2dd4bf; margin-bottom: 30px; }
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
    <html>
    <head>
        <style>
            @page { margin: 1.5cm; }
            * { box-sizing: border-box; }
            {$css}
            .section-item { margin-bottom: 20px; clear: both; }
            .item-header { margin-bottom: 5px; }
            .item-desc { margin-top: 5px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <div class='name'>{$resume['full_name']}</div>
            <div class='contact'>
                {$resume['city']} - {$resume['state']} | {$resume['email']} | {$resume['phone']}
            </div>
        </div>

        <div class='content-wrapper'>
            " . (!empty(trim($resume['summary'])) ? "
            <div class='section-title'>Resumo</div>
            <div class='item-desc'>{$resume['summary']}</div>
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
            <div class='section-title'>Habilidades</div>
            <div class='skills-box'>{$skillsText}</div>
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

while ($iteration < $maxIterations) {
    $html = getResumeHtml($resume, $experiences, $education, $skills, $fontSize, $lineHeight);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $pageCount = $dompdf->getCanvas()->get_page_count();

    if ($pageCount <= 1) {
        break; // Succesfully fits in 1 page
    }

    // Content too large, reduce settings
    $fontSize -= 0.5;
    $lineHeight -= 0.05;
    $iteration++;
}

// Final output
$dompdf->stream("Curriculo_" . str_replace(' ', '_', $resume['full_name']) . ".pdf", ["Attachment" => false]);
