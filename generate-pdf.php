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
    $skillsArr = array_map(function ($s) {
        return $s['skill_name']; }, $skills);
    $skillsText = implode(' • ', $skillsArr);

    $expHtml = '';
    foreach ($experiences as $exp) {
        $expHtml .= "
        <div class='section-item'>
            <div class='item-header'>
                <strong>{$exp['company']}</strong>
                <span>{$exp['start_date']} – {$exp['end_date']}</span>
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
                <strong>{$edu['institution']}</strong>
                <span>{$edu['graduation_date']}</span>
            </div>
            <div class='item-sub'>{$edu['degree']}</div>
        </div>";
    }

    $html = "
    <html>
    <head>
        <style>
            @page { margin: 1cm; }
            body { 
                font-family: 'Helvetica', sans-serif; 
                font-size: {$fontSize}pt; 
                line-height: {$lineHeight}; 
                color: #333; 
                margin: 0;
            }
            .header { text-align: center; border-bottom: 2px solid #444; padding-bottom: 10px; margin-bottom: 20px; }
            .name { font-size: 24pt; font-weight: bold; color: #000; text-transform: uppercase; }
            .contact { font-size: 10pt; color: #666; margin-top: 5px; }

            .section-title { 
                font-size: 14pt; 
                font-weight: bold; 
                color: #000; 
                border-bottom: 1px solid #ccc; 
                margin-top: 20px; 
                margin-bottom: 10px; 
                text-transform: uppercase;
            }
            .section-item { margin-bottom: 15px; }
            .item-header { display: flex; justify-content: space-between; font-weight: bold; }
            .item-sub { font-style: italic; color: #444; margin-bottom: 5px; }
            .item-desc { font-size: 0.95em; text-align: justify; }
            
            .skills-box { font-style: italic; }
        </style>
    </head>
    <body>
        <div class='header'>
            <div class='name'>{$resume['full_name']}</div>
            <div class='contact'>{$resume['email']} | {$resume['phone']}</div>
        </div>

        <div class='section-title'>Resumo Profissional</div>
        <div class='item-desc'>{$resume['summary']}</div>

        <div class='section-title'>Experiência Profissional</div>
        {$expHtml}

        <div class='section-title'>Formação Acadêmica</div>
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
