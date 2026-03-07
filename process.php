<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;
use App\Auth;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance();
        $db->beginTransaction();

        // 1. Handle Photo Upload
        $photoPath = null;
        if (!empty($_FILES['photo']['name'])) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $tmpFile = $_FILES['photo']['tmp_name'];
            $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $fileName = uniqid() . '.' . $fileExtension;
            $targetFile = $uploadDir . $fileName;

            // --- Server-side Crop 3:4 ---
            $img = null;
            // Check if GD functions exist for image manipulation
            if (function_exists('imagecreatefromjpeg')) {
                if ($fileExtension === 'jpg' || $fileExtension === 'jpeg')
                    $img = imagecreatefromjpeg($tmpFile);
                elseif ($fileExtension === 'png')
                    $img = imagecreatefrompng($tmpFile);
                elseif ($fileExtension === 'webp')
                    $img = imagecreatefromwebp($tmpFile);
            }

            if ($img) {
                $width = imagesx($img);
                $height = imagesy($img);
                $targetRatio = 3 / 4;
                $currentRatio = $width / $height;

                if ($currentRatio > $targetRatio) {
                    // Cortar largura
                    $newWidth = $height * $targetRatio;
                    $x = ($width - $newWidth) / 2;
                    $y = 0;
                    $width = $newWidth;
                } else {
                    // Cortar altura
                    $newHeight = $width / $targetRatio;
                    $x = 0;
                    $y = ($height - $newHeight) / 2;
                    $height = $newHeight;
                }

                $cropped = imagecreatetruecolor(600, 800); // Resolução fixa 3:4
                imagecopyresampled($cropped, $img, 0, 0, $x, $y, 600, 800, $width, $height);

                if ($fileExtension === 'jpg' || $fileExtension === 'jpeg')
                    imagejpeg($cropped, $targetFile, 90);
                elseif ($fileExtension === 'png')
                    imagepng($cropped, $targetFile);
                elseif ($fileExtension === 'webp')
                    imagewebp($cropped, $targetFile);

                imagedestroy($img);
                imagedestroy($cropped);
                $photoPath = 'uploads/' . $fileName;
            } else {
                // Fallback: Se o GD não estiver disponível, apenas move o arquivo original
                if (move_uploaded_file($tmpFile, $targetFile)) {
                    $photoPath = 'uploads/' . $fileName;
                }
            }
        }

        // 2. Insert Resume Main Info
        Auth::init();
        $userId = $_SESSION['user_id'] ?? null;
        if (!is_numeric($userId))
            $userId = null;

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['full_name']))) . '-' . uniqid();

        $stmt = $db->prepare("INSERT INTO resumes (full_name, email, phone, city, state, photo_path, summary, template_id, user_id, slug) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['full_name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['city'],
            $_POST['state'],
            $photoPath,
            $_POST['summary'],
            $_POST['template_id'],
            $userId,
            $slug
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
