<?php

namespace App\Renderer;

class ResumeRenderer
{
    public static function render($resume, $experiences, $education, $skills, $fontSize = 11, $lineHeight = 1.35, $isPdf = false)
    {
        $template = $resume['template_id'] ?? 'modern';
        $primaryColor = $resume['primary_color'] ?? null;
        $fontFamily = $resume['font_family'] ?? 'jakarta';

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
            $study = !empty($edu['field_of_study']) ? " - " . $edu['field_of_study'] : "";
            $eduHtml .= "
            <div class='section-item'>
                <div class='item-header'>
                    <span class='company'>{$edu['institution']}</span>
                    <span class='date'>{$edu['graduation_date']}</span>
                </div>
                <div class='item-sub'>{$edu['degree']}{$study}</div>
            </div>";
        }

        // Define CSS based on template
        $css = self::getTemplateCss($template, $primaryColor, $fontSize, $lineHeight);

        $html = "
        <!DOCTYPE html>
        <html lang='pt-br'>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
            <title>{$resume['full_name']} - Currículo</title>
            " . self::getFontImport($fontFamily) . "
            <style>
                @page { margin: 1cm 1.5cm; }
                * { box-sizing: border-box; -webkit-print-color-adjust: exact; }
                body { 
                    font-family: " . self::getFontFace($fontFamily) . ", 'DejaVu Sans', sans-serif; 
                    margin: 0; 
                    padding: 0;
                    background-color: #f1f5f9;
                }
                .resume-page {
                    width: 210mm;
                    min-height: 297mm;
                    margin: 2rem auto;
                    padding: 1cm 1.5cm;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                }
                body.is-iframe .resume-page,
                body.is-pdf .resume-page {
                    margin: 0;
                    box-shadow: none;
                    width: 100%;
                    padding: 0;
                }
                @media print {
                    body { background: none; }
                    .resume-page { 
                        margin: 0; 
                        box-shadow: none; 
                        width: auto;
                        min-height: auto;
                        padding: 0;
                    }
                }
                {$css}
                .section-item { margin-bottom: 15px; clear: both; page-break-inside: avoid; }
                .item-header { margin-bottom: 3px; overflow: hidden; }
                .item-desc { margin-top: 3px; text-align: justify; }
                .section-title { page-break-after: avoid; }

                /* Floating Action Button (FAB) */
                .fab-container {
                    position: fixed;
                    bottom: 30px;
                    right: 30px;
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                    z-index: 1000;
                }
                .fab-btn {
                    background-color: " . ($primaryColor ?? '#4f46e5') . ";
                    color: white;
                    padding: 12px 24px;
                    border-radius: 50px;
                    text-decoration: none;
                    font-weight: 600;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    transition: transform 0.2s, background-color 0.2s;
                }
                .fab-btn:hover {
                    transform: translateY(-2px);
                    filter: brightness(1.1);
                }
                .fab-btn svg { width: 20px; height: 20px; }

                @media print {
                    .fab-container { display: none; }
                }
                body.is-iframe .fab-container,
                body.is-pdf .fab-container {
                    display: none;
                }
            </style>
        </head>
        <body class='" . ($isPdf ? 'is-pdf' : (isset($_GET['iframe']) ? 'is-iframe' : '')) . "' style='margin: 0; padding: 0;'>
            " . (!$isPdf && !isset($_GET['iframe']) && !empty($resume['id']) ? "
            <div class='fab-container'>
                <a href='generate-pdf.php?id={$resume['id']}' class='fab-btn'>
                    <svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='currentColor'>
                        <path stroke-linecap='round' stroke-linejoin='round' d='M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3' />
                    </svg>
                    Baixar PDF
                </a>
            </div>
            " : "") . "
            <div class='resume-page'>
                <div class='header'>
                " . (!empty($resume['photo_path']) ? (
                strpos($resume['photo_path'], 'data:') === 0 ? "
                    <div class='photo-container'>
                        <img src='{$resume['photo_path']}' class='photo'>
                    </div>
                    " : (file_exists(__DIR__ . "/../../" . $resume['photo_path']) ? "
                    <div class='photo-container'>
                        <img src='data:image/" . pathinfo($resume['photo_path'], PATHINFO_EXTENSION) . ";base64," . base64_encode(file_get_contents(__DIR__ . "/../../" . $resume['photo_path'])) . "' class='photo'>
                    </div>
                    " : "")
            ) : "") . "
                <div class='header-text'>
                    <div class='name'>{$resume['full_name']}</div>
                    <div class='contact'>
                        {$resume['city']} - {$resume['state']} | {$resume['email']} | {$resume['phone']}
                    </div>
                </div>
                <div style='clear: both;'></div>
            </div>

            <div class='content-wrapper'>
                " . (!empty(trim($resume['summary'])) ? "
                <div class='section-group'>
                    <div class='section-title'>Resumo</div>
                    <div class='item-desc'>" . nl2br(htmlspecialchars($resume['summary'])) . "</div>
                </div>
                " : "") . "

                " . (!empty($experiences) ? "
                <div class='section-group'>
                    <div class='section-title'>Experiência</div>
                    {$expHtml}
                </div>
                " : "") . "

                " . (!empty($education) ? "
                <div class='section-group'>
                    <div class='section-title'>Educação</div>
                    {$eduHtml}
                </div>
                " : "") . "

                " . (!empty($skills) ? "
                <div class='section-group'>
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

    private static function getTemplateCss($template, $primaryColor, $fontSize, $lineHeight)
    {
        switch ($template) {
            case 'health':
                $p = $primaryColor ?? '#0d9488';
                return "
                    .resume-page { background: white; }
                    body { font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #334155; margin: 0; padding: 0; }
                    .header { background: #f0fdfa; border-bottom: 6px solid {$p}; padding: 30px 40px; margin-bottom: 30px; border-radius: 0 0 20px 20px; text-align: center; }
                    .photo-container { width: 105px; height: 140px; margin: 0 auto 15px auto; border-radius: 15px; border: 3px solid {$p}; overflow: hidden; background: #e2e8f0; }
                    .photo { width: 100%; height: 100%; object-fit: cover; }
                    .name { font-size: 28pt; font-weight: 800; color: {$p}; text-transform: capitalize; margin: 0; }
                    .contact { font-size: 10.5pt; color: #64748b; margin-top: 10px; font-weight: 600; }
                    .header-text { width: 100%; }
                    .content-wrapper { padding: 0 40px; }
                    .section-title { font-size: 11pt; font-weight: 800; color: #ffffff; background: {$p}; padding: 5px 15px; margin-top: 15px; margin-bottom: 10px; border-radius: 6px; display: block; text-transform: uppercase; letter-spacing: 1.5px; page-break-after: avoid; }
                    .section-item { margin-bottom: 15px; page-break-inside: avoid; }
                    .company { font-weight: bold; color: #1e293b; font-size: 1.1em; display: inline-block; }
                    .date { color: #64748b; float: right; font-size: 0.85em; background: #f1f5f9; padding: 3px 10px; border-radius: 12px; font-weight: 600; }
                    .item-sub { color: {$p}; font-weight: 700; margin-bottom: 8px; display: block; font-size: 1.05em; }
                    .item-desc { border-left: 3px solid #ccfbf1; padding-left: 15px; margin-left: 5px; text-align: justify; color: #475569; }
                    .skills-box { border: 2px dashed #99f6e4; padding: 15px; color: {$p}; font-weight: 700; border-radius: 10px; background: #f0fdfa; line-height: 1.6; }
                ";
            case 'health_professional':
                $p = $primaryColor ?? '#1e3a8a';
                return "
                    .resume-page { background: white; }
                    body { font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #1e293b; margin: 0; padding: 0; }
                    .header { background: {$p}; color: white; padding: 40px; margin-bottom: 30px; text-align: left; overflow: hidden; }
                    .header-text { float: left; width: 75%; }
                    .photo-container { float: right; width: 110px; height: 145px; border: 4px solid white; border-radius: 8px; overflow: hidden; }
                    .photo { width: 100%; height: 100%; object-fit: cover; }
                    .name { font-size: 32pt; font-weight: 700; margin: 0; letter-spacing: -1px; }
                    .contact { font-size: 11pt; opacity: 0.9; margin-top: 10px; }
                    .content-wrapper { padding: 0 40px; }
                    .section-title { font-size: 14pt; font-weight: 700; color: {$p}; border-bottom: 3px solid {$p}; margin-top: 25px; margin-bottom: 15px; text-transform: uppercase; padding-bottom: 5px; page-break-after: avoid; }
                    .section-item { margin-bottom: 20px; page-break-inside: avoid; }
                    .company { font-weight: bold; color: #000; font-size: 1.15em; }
                    .date { color: {$p}; font-weight: 700; float: right; }
                    .item-sub { color: #64748b; font-weight: 600; font-style: italic; margin-bottom: 5px; }
                    .item-desc { color: #334155; border-left: 4px solid #e2e8f0; padding-left: 20px; }
                    .skills-box { background: #f1f5f9; padding: 20px; border-radius: 4px; border-left: 10px solid {$p}; font-weight: bold; color: {$p}; }
                ";
            case 'health_clean':
                $p = $primaryColor ?? '#10b981';
                return "
                    .resume-page { background: white; }
                    body { font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #475569; margin: 0; padding: 0; }
                    .header { padding: 40px; text-align: center; border-bottom: 1px solid #e2e8f0; margin-bottom: 30px; }
                    .header-text { width: 100%; }
                    .photo-container { width: 100px; height: 133px; margin: 0 auto 20px auto; border-radius: 50%; border: 3px solid {$p}; overflow: hidden; }
                    .photo { width: 100%; height: 100%; object-fit: cover; }
                    .name { font-size: 26pt; font-weight: 300; color: #0f172a; margin: 0; }
                    .contact { font-size: 10pt; color: #94a3b8; margin-top: 10px; }
                    .content-wrapper { padding: 0 50px; }
                    .section-title { font-size: 12pt; font-weight: 600; color: {$p}; margin-top: 30px; margin-bottom: 15px; text-align: center; page-break-after: avoid; }
                    .section-item { margin-bottom: 25px; page-break-inside: avoid; }
                    .company { font-weight: 700; color: #1e293b; }
                    .date { color: #94a3b8; float: right; font-size: 0.9em; }
                    .item-sub { color: #64748b; font-weight: 500; margin-bottom: 8px; }
                    .item-desc { font-size: 0.95em; border-top: 1px solid #f1f5f9; padding-top: 10px; }
                    .skills-box { text-align: center; color: {$p}; border: 1px solid {$p}; padding: 15px; border-radius: 100px; font-weight: 500; }
                ";
            case 'tech':
                $p = $primaryColor ?? '#2dd4bf';
                return "
                    .resume-page { background: #0f172a; }
                    body { font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #e2e8f0; background-color: #0f172a; margin: 0; padding: 0; }
                    .header { background: #1e293b; color: {$p}; padding: 30px 40px; text-align: left; border-bottom: 6px solid {$p}; margin-bottom: 30px; overflow: hidden; }
                    .header-text { float: left; width: 75%; }
                    .photo-container { float: right; width: 105px; height: 140px; border-radius: 12px; border: 2px solid {$p}; overflow: hidden; background: #0f172a; }
                    .photo { width: 100%; height: 100%; object-fit: cover; }
                    .name { font-size: 30pt; font-weight: 900; color: {$p}; text-transform: uppercase; margin: 0; }
                    .contact { font-size: 10pt; color: #94a3b8; font-weight: 600; font-family: 'Courier New', monospace; margin-top: 10px; }
                    .content-wrapper { padding: 0 40px; }
                    .section-title { font-size: 13pt; font-weight: 800; color: {$p}; margin-top: 20px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 2px; border-bottom: 2px solid rgba(45, 212, 191, 0.3); padding-bottom: 5px; page-break-after: avoid; display: block; }
                    .section-item { margin-bottom: 20px; border-bottom: 1px solid rgba(148, 163, 184, 0.1); padding-bottom: 10px; page-break-inside: avoid; }
                    .company { font-weight: 800; color: #f8fafc; font-size: 1.2em; display: inline-block; }
                    .date { color: {$p}; font-weight: bold; font-family: 'Courier New', monospace; font-size: 0.9em; float: right; padding-top: 5px; }
                    .item-sub { color: #94a3b8; font-weight: 700; margin-bottom: 10px; display: block; font-size: 1.05em; }
                    .item-desc { color: #cbd5e1; text-align: justify; padding-right: 10px; }
                    .skills-box { background: rgba(45, 212, 191, 0.1); border: 1px solid {$p}; padding: 15px; border-radius: 12px; color: {$p}; font-family: 'Courier New', monospace; font-weight: bold; line-height: 1.6; }
                ";
            case 'tech_modern':
                $p = $primaryColor ?? '#4f46e5';
                return "
                    .resume-page { background: #f8fafc; }
                    body { font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #334155; background-color: #f8fafc; margin: 0; padding: 0; }
                    .header { background: {$p}; color: white; padding: 40px; margin-bottom: 30px; overflow: hidden; }
                    .header-text { float: left; width: 75%; }
                    .photo-container { float: right; width: 110px; height: 145px; border-radius: 20px; border: 4px solid rgba(255,255,255,0.3); overflow: hidden; }
                    .photo { width: 100%; height: 100%; object-fit: cover; }
                    .name { font-size: 32pt; font-weight: 800; margin: 0; }
                    .contact { font-size: 11pt; color: #e0e7ff; margin-top: 10px; }
                    .content-wrapper { padding: 0 40px; }
                    .section-title { font-size: 12pt; font-weight: 800; color: {$p}; border-left: 5px solid {$p}; padding-left: 15px; margin-top: 25px; margin-bottom: 15px; text-transform: uppercase; page-break-after: avoid; }
                    .section-item { margin-bottom: 20px; background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; page-break-inside: avoid; }
                    .company { font-weight: 800; color: #1e293b; font-size: 1.1em; }
                    .date { color: {$p}; font-weight: 700; float: right; }
                    .item-sub { color: #64748b; font-weight: 600; margin-top: 5px; }
                    .item-desc { margin-top: 10px; color: #475569; }
                    .skills-box { background: white; border: 2px solid {$p}; padding: 20px; border-radius: 15px; color: {$p}; font-weight: 800; }
                ";
            case 'tech_minimal':
                $p = $primaryColor ?? '#111827';
                return "
                    .resume-page { background: white; }
                    body { font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #1f2937; margin: 0; padding: 0; }
                    .header { padding: 50px 40px 30px 40px; border-bottom: 2px solid {$p}; margin-bottom: 40px; overflow: hidden; }
                    .photo-container { float: left; width: 80px; height: 106px; margin-right: 30px; border: 1px solid {$p}; }
                    .photo { width: 100%; height: 100%; object-fit: cover; }
                    .header-text { float: left; width: 70%; }
                    .name { font-size: 34pt; font-weight: 400; margin: 0; color: {$p}; letter-spacing: -2px; }
                    .contact { font-size: 9pt; color: #6b7280; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px; }
                    .content-wrapper { padding: 0 40px; }
                    .section-title { font-size: 10pt; font-weight: 900; color: {$p}; margin-top: 35px; margin-bottom: 20px; border-bottom: 1px solid {$p}; padding-bottom: 5px; text-transform: uppercase; page-break-after: avoid; }
                    .section-item { margin-bottom: 25px; page-break-inside: avoid; }
                    .company { font-weight: 700; color: {$p}; font-size: 1.1em; }
                    .date { float: right; font-weight: 400; font-family: serif; font-style: italic; }
                    .item-sub { font-weight: 700; color: #4b5563; margin-bottom: 5px; font-size: 0.9em; }
                    .item-desc { color: #374151; border-top: 0.5px solid #f3f4f6; padding-top: 10px; font-size: 0.95em; }
                    .skills-box { border: 1px solid {$p}; padding: 15px; color: {$p}; font-weight: 400; font-size: 0.9em; text-align: justify; }
                ";
            default: // modern fallback
                return "
                    .resume-page { background: white; }
                    body { font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #334155; margin: 0; padding: 0; }
                    .header { background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 30px 40px; margin-bottom: 30px; text-align: center; }
                    .header-text { width: 100%; }
                    .name { font-size: 26pt; font-weight: 800; color: #1e293b; margin: 0; }
                    .contact { font-size: 10pt; color: #64748b; margin-top: 10px; }
                    .content-wrapper { padding: 0 40px; }
                    .section-title { font-size: 14pt; font-weight: 700; color: #1e293b; border-bottom: 2px solid #e2e8f0; margin-top: 20px; margin-bottom: 15px; padding-bottom: 5px; page-break-after: avoid; }
                    .section-item { margin-bottom: 20px; page-break-inside: avoid; }
                ";
        }
    }

    private static function getFontImport($fontFamily)
    {
        switch ($fontFamily) {
            case 'inter':
                return "<link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap' rel='stylesheet'>";
            case 'roboto':
                return "<link href='https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap' rel='stylesheet'>";
            case 'outfit':
                return "<link href='https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap' rel='stylesheet'>";
            case 'jakarta':
            default:
                return "<link href='https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap' rel='stylesheet'>";
        }
    }

    private static function getFontFace($fontFamily)
    {
        switch ($fontFamily) {
            case 'inter':
                return "'Inter'";
            case 'roboto':
                return "'Roboto'";
            case 'outfit':
                return "'Outfit'";
            default:
                return "'Plus Jakarta Sans'";
        }
    }
}
