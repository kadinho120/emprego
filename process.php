<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance();
        $db->beginTransaction();

        // 1. Handle Photo Upload
        $photoPath = null;
        if (!empty($_FILES['photo']['name'])) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExtension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '.' . $fileExtension;
            $targetFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
                $photoPath = 'uploads/' . $fileName;
            }
        }

        // 2. Insert Resume Main Info
        $stmt = $db->prepare("INSERT INTO resumes (full_name, email, phone, city, state, photo_path, summary, template_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['full_name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['city'],
            $_POST['state'],
            $photoPath,
            $_POST['summary'],
            $_POST['template_id']
        ]);
        $resumeId = $db->lastInsertId();

        // 2. Insert Experience
        if (!empty($_POST['experience'])) {
            $stmtExp = $db->prepare("INSERT INTO experiences (resume_id, company, position, start_date, end_date, description) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($_POST['experience'] as $exp) {
                if (!empty($exp['company'])) {
                    $stmtExp->execute([
                        $resumeId,
                        $exp['company'],
                        $exp['position'],
                        $exp['start_date'],
                        $exp['end_date'],
                        $exp['description']
                    ]);
                }
            }
        }

        // 3. Insert Education
        if (!empty($_POST['education'])) {
            $stmtEdu = $db->prepare("INSERT INTO education (resume_id, institution, degree, graduation_date) VALUES (?, ?, ?, ?)");
            foreach ($_POST['education'] as $edu) {
                if (!empty($edu['institution'])) {
                    $stmtEdu->execute([
                        $resumeId,
                        $edu['institution'],
                        $edu['degree'],
                        $edu['graduation_date']
                    ]);
                }
            }
        }

        // 4. Insert Skills
        if (!empty($_POST['skills'])) {
            $skills = explode(',', $_POST['skills']);
            $stmtSkill = $db->prepare("INSERT INTO skills (resume_id, skill_name) VALUES (?, ?)");
            foreach ($skills as $skill) {
                $trimmed = trim($skill);
                if (!empty($trimmed)) {
                    $stmtSkill->execute([$resumeId, $trimmed]);
                }
            }
        }

        $db->commit();

        // Redirect to PDF generation
        header("Location: generate-pdf.php?id=" . $resumeId);
        exit;

    } catch (Exception $e) {
        if (isset($db))
            $db->rollBack();
        die("Erro ao salvar: " . $e->getMessage());
    }
} else {
    header("Location: generator.php");
}
