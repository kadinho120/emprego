<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Renderer\ResumeRenderer;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Apenas POST permitido.");
}

// Convert POST data into the format expected by the renderer
$resume = [
    'full_name' => $_POST['full_name'] ?? '',
    'email' => $_POST['email'] ?? '',
    'phone' => $_POST['phone'] ?? '',
    'city' => $_POST['city'] ?? '',
    'state' => $_POST['state'] ?? '',
    'summary' => $_POST['summary'] ?? '',
    'template_id' => $_POST['template_id'] ?? 'tech',
    'primary_color' => $_POST['primary_color'] ?? null,
    'font_family' => $_POST['font_family'] ?? 'jakarta',
    'photo_path' => $_POST['photo_base64'] ?: ($_POST['photo_path'] ?? null)
];

$experiences = [];
if (!empty($_POST['experience'])) {
    foreach ($_POST['experience'] as $exp) {
        $experiences[] = [
            'company' => $exp['company'] ?? '',
            'position' => $exp['position'] ?? '',
            'start_date' => $exp['start_date'] ?? '',
            'end_date' => $exp['end_date'] ?? '',
            'description' => $exp['description'] ?? ''
        ];
    }
}

$education = [];
if (!empty($_POST['education'])) {
    foreach ($_POST['education'] as $edu) {
        $education[] = [
            'institution' => $edu['institution'] ?? '',
            'degree' => $edu['degree'] ?? '',
            'field_of_study' => $edu['field_of_study'] ?? '',
            'graduation_date' => $edu['graduation_date'] ?? ''
        ];
    }
}

$skills = [];
if (!empty($_POST['skills'])) {
    $skillsRaw = explode(',', $_POST['skills']);
    foreach ($skillsRaw as $s) {
        $skills[] = ['skill_name' => trim($s)];
    }
}

echo ResumeRenderer::render($resume, $experiences, $education, $skills);
