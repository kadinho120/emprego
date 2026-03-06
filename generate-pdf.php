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
    if ($template === 'modern') {
        $css = "
            body { font-family: 'Helvetica', sans-serif; font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #1e293b; margin: 0; }
            .header { border-left: 10px solid #4f46e5; padding-left: 20px; margin-bottom: 30px; }
            .name { font-size: 26pt; font-weight: 800; color: #1e293b; letter-spacing: -1px; }
            .contact { font-size: 10pt; color: #64748b; margin-top: 5px; }
            .section-title { font-size: 13pt; font-weight: bold; color: #4f46e5; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; margin-top: 25px; margin-bottom: 15px; text-transform: uppercase; }
            .company { font-weight: bold; color: #1e293b; font-size: 1.1em; }
            .date { color: #64748b; float: right; font-weight: normal; font-size: 0.9em; }
            .item-sub { color: #4f46e5; font-weight: 600; margin-bottom: 8px; }
            .skills-box { background: #f8fafc; padding: 10px; border-radius: 5px; color: #475569; font-style: italic; }
        ";
    } elseif ($template === 'corporate') {
        $css = "
            body { font-family: 'Times', serif; font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #000; margin: 0; }
            .header { text-align: center; border-bottom: 1px double #000; padding-bottom: 15px; margin-bottom: 25px; }
            .name { font-size: 28pt; font-weight: normal; text-transform: uppercase; letter-spacing: 2px; }
            .contact { font-size: 11pt; color: #333; margin-top: 8px; font-style: italic; }
            .section-title { font-size: 14pt; font-weight: bold; border-bottom: 1px solid #000; margin-top: 25px; margin-bottom: 12px; text-transform: uppercase; text-align: center; }
            .company { font-weight: bold; text-decoration: underline; }
            .date { float: right; font-weight: bold; }
            .item-sub { font-weight: bold; font-style: italic; margin-bottom: 5px; display: block; }
            .item-desc { text-align: justify; }
            .skills-box { font-weight: bold; text-align: center; border-top: 1px solid #ccc; padding-top: 5px; }
        ";
    } else { // minimal
        $css = "
            body { font-family: 'Arial', sans-serif; font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #333; margin: 0; }
            .header { margin-bottom: 40px; }
            .name { font-size: 32pt; font-weight: 300; color: #000; }
            .contact { font-size: 9pt; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-top: 10px; }
            .section-title { font-size: 10pt; font-weight: 800; color: #999; margin-top: 35px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 2px; }
            .company { font-weight: bold; color: #000; }
            .date { color: #bbb; display: block; font-size: 0.85em; margin-bottom: 5px; }
            .item-sub { color: #666; margin-bottom: 5px; }
            .item-desc { color: #444; }
            .skills-box { color: #777; line-height: 1.8; }
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

        <div class='section-title'>Resumo</div>
        <div class='item-desc'>{$resume['summary']}</div>

        <div class='section-title'>Experiência</div>
        {$expHtml}

        <div class='section-title'>Educação</div>
        {$eduHtml}

        <div class='section-title'>Habilidades</div>
        <div class='skills-box'>{$skillsText}</div>
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
