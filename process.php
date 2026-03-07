<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;
use App\Auth;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance();
        $db->beginTransaction();

        // 1. Handle Photo Upload
        $photoPath = $_POST['photo_path'] ?? null;
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

        // 2. Insert/Update Resume Main Info
        Auth::init();
        $userId = $_SESSION['user_id'] ?? null;
        $userRole = $_SESSION['user_role'] ?? 'user';
        if (!is_numeric($userId))
            $userId = null;

        $resumeId = $_POST['resume_id'] ?? null;

        if ($resumeId) {
            // Verify ownership
            if ($userRole !== 'admin') {
                $stmtVerify = $db->prepare("SELECT id FROM resumes WHERE id = ? AND user_id = ?");
                $stmtVerify->execute([$resumeId, $userId]);
                if (!$stmtVerify->fetch()) {
                    throw new Exception("Acesso negado.");
                }
            }

            // Update Resume
            $stmt = $db->prepare("UPDATE resumes SET full_name = ?, email = ?, phone = ?, city = ?, state = ?, photo_path = ?, summary = ?, template_id = ?, primary_color = ?, font_family = ?, niche = ? WHERE id = ?");
            $stmt->execute([
                $_POST['full_name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['city'],
                $_POST['state'],
                $photoPath,
                $_POST['summary'],
                $_POST['template_id'],
                $_POST['primary_color'] ?? '#6366f1',
                $_POST['font_family'] ?? 'jakarta',
                $_POST['niche'] ?? 'tech',
                $resumeId
            ]);

            // Clear sub-records (we re-insert them below)
            $stmtDelExp = $db->prepare("DELETE FROM experiences WHERE resume_id = ?");
            $stmtDelExp->execute([$resumeId]);

            $stmtDelEdu = $db->prepare("DELETE FROM education WHERE resume_id = ?");
            $stmtDelEdu->execute([$resumeId]);

            $stmtDelSkills = $db->prepare("DELETE FROM skills WHERE resume_id = ?");
            $stmtDelSkills->execute([$resumeId]);
        } else {
            // New Resume
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['full_name']))) . '-' . uniqid();

            $stmt = $db->prepare("INSERT INTO resumes (full_name, email, phone, city, state, photo_path, summary, template_id, user_id, slug, primary_color, font_family, niche) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
                $slug,
                $_POST['primary_color'] ?? '#6366f1',
                $_POST['font_family'] ?? 'jakarta',
                $_POST['niche'] ?? 'tech'
            ]);
            $resumeId = $db->lastInsertId();
        }

        // 2. Insert Experience
        if (!empty($_POST['experience'])) {
            $stmtExp = $db->prepare("INSERT INTO experiences (resume_id, company, position, start_date, end_date, description, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $expOrder = 0;
            foreach ($_POST['experience'] as $exp) {
                if (!empty($exp['company'])) {
                    $stmtExp->execute([
                        $resumeId,
                        $exp['company'],
                        $exp['position'],
                        $exp['start_date'],
                        $exp['end_date'],
                        $exp['description'],
                        $expOrder++
                    ]);
                }
            }
        }

        // 3. Insert Education
        if (!empty($_POST['education'])) {
            $stmtEdu = $db->prepare("INSERT INTO education (resume_id, institution, degree, field_of_study, graduation_date, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
            $eduOrder = 0;
            foreach ($_POST['education'] as $edu) {
                if (!empty($edu['institution'])) {
                    $stmtEdu->execute([
                        $resumeId,
                        $edu['institution'],
                        $edu['degree'],
                        $edu['field_of_study'] ?? '',
                        $edu['graduation_date'],
                        $eduOrder++
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
