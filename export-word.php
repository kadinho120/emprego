<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use App\Auth;

Auth::requireLogin();

$id = $_GET['id'] ?? null;
if (!$id)
    die("ID não fornecido");

try {
    $db = Database::getInstance();

    // Fetch data
    $stmt = $db->prepare("SELECT * FROM resumes WHERE id = ?");
    $stmt->execute([$id]);
    $resume = $stmt->fetch();
    if (!$resume)
        die("Currículo não encontrado");

    $stmtExp = $db->prepare("SELECT * FROM experiences WHERE resume_id = ? ORDER BY sort_order ASC, id ASC");
    $stmtExp->execute([$id]);
    $experiences = $stmtExp->fetchAll();

    $stmtEdu = $db->prepare("SELECT * FROM education WHERE resume_id = ? ORDER BY sort_order ASC, id ASC");
    $stmtEdu->execute([$id]);
    $education = $stmtEdu->fetchAll();

    $stmtSkills = $db->prepare("SELECT * FROM skills WHERE resume_id = ?");
    $stmtSkills->execute([$id]);
    $skills = $stmtSkills->fetchAll();

    // Create Word Doc
    $phpWord = new PhpWord();
    $section = $phpWord->addSection();

    // Header (Name & Contact)
    $section->addText(strtoupper($resume['full_name']), ['bold' => true, 'size' => 20]);
    $section->addText($resume['city'] . ' - ' . $resume['state'] . ' | ' . $resume['email'] . ' | ' . $resume['phone'], ['size' => 10]);
    $section->addHR();

    // Summary
    if ($resume['summary']) {
        $section->addText('RESUMO', ['bold' => true]);
        $section->addText($resume['summary']);
        $section->addTextBreak();
    }

    // Experiences
    if ($experiences) {
        $section->addText('EXPERIÊNCIA PROFISSIONAL', ['bold' => true]);
        foreach ($experiences as $exp) {
            $textrun = $section->addTextRun();
            $textrun->addText($exp['company'] . ' - ', ['bold' => true]);
            $textrun->addText($exp['position'], ['italic' => true]);
            $section->addText($exp['start_date'] . ' - ' . $exp['end_date'], ['size' => 9]);
            $section->addText($exp['description']);
            $section->addTextBreak();
        }
    }

    // Education
    if ($education) {
        $section->addText('FORMAÇÃO ACADÊMICA', ['bold' => true]);
        foreach ($education as $edu) {
            $section->addText($edu['institution'], ['bold' => true]);
            $section->addText($edu['degree'] . ' (' . $edu['graduation_date'] . ')');
            $section->addTextBreak();
        }
    }

    // Skills
    if ($skills) {
        $section->addText('HABILIDADES', ['bold' => true]);
        $skillNames = array_map(fn($s) => $s['skill_name'], $skills);
        $section->addText(implode(' • ', $skillNames));
    }

    // Export
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment;filename="curriculo_' . $id . '.docx"');
    header('Cache-Control: max-age=0');

    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save('php://output');

} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}
