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
                    .header { background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%); border-left: 10px solid {$p}; padding: 40px; margin-bottom: 30px; border-radius: 0 20px 20px 0; }
                    .photo-container { float: right; width: 110px; height: 145px; border-radius: 15px; border: 4px solid white; box-shadow: 0 10px 25px rgba(13, 148, 136, 0.2); overflow: hidden; }
                    .photo { width: 100%; height: 100%; object-fit: cover; }
                    .name { font-size: 30pt; font-weight: 800; color: {$p}; margin: 0; letter-spacing: -1px; }
                    .contact { font-size: 10pt; color: #64748b; margin-top: 10px; display: flex; gap: 10px; }
                    .header-text { float: left; width: 70%; }
                    .content-wrapper { padding: 0 40px; }
                    .section-title { font-size: 12pt; font-weight: 800; color: {$p}; margin-top: 25px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center; gap: 10px; page-break-after: avoid; }
                    .section-title::after { content: ''; flex: 1; height: 2px; background: linear-gradient(to right, {$p}, transparent); opacity: 0.3; }
                    .section-item { margin-bottom: 20px; page-break-inside: avoid; }
                    .company { font-weight: 700; color: #1e293b; font-size: 1.1em; }
                    .date { color: {$p}; font-weight: 700; font-size: 0.85em; background: #ccfbf1; padding: 4px 12px; border-radius: 50px; float: right; }
                    .item-sub { color: #5b21b6; font-weight: 700; margin-top: 4px; display: block; font-style: italic; }
                    .item-desc { margin-top: 10px; padding-left: 20px; border-left: 2px solid #e2e8f0; color: #475569; }
                    .skills-box { display: flex; flex-wrap: wrap; gap: 10px; color: {$p}; font-weight: 700; }
                ";
            case 'health_professional':
                $p = $primaryColor ?? '#1e3a8a';
                return "
                    .resume-page { background: white; }
                    body { font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #1e293b; margin: 0; padding: 0; }
                    .header { background: {$p}; color: white; padding: 50px 40px; margin-bottom: 40px; position: relative; overflow: hidden; }
                    .header::after { content: ''; position: absolute; top: -50%; right: -10%; width: 300px; height: 300px; background: rgba(255,255,255,0.05); border-radius: 50%; }
                    .header-text { float: left; width: 75%; position: relative; z-index: 10; }
                    .photo-container { float: right; width: 120px; height: 160px; border: 5px solid rgba(255,255,255,0.2); border-radius: 12px; overflow: hidden; position: relative; z-index: 10; }
                    .photo { width: 100%; height: 100%; object-fit: cover; }
                    .name { font-size: 34pt; font-weight: 800; margin: 0; text-transform: uppercase; letter-spacing: -1px; }
                    .contact { font-size: 11pt; opacity: 0.8; margin-top: 15px; font-weight: 500; }
                    .content-wrapper { padding: 0 50px; }
                    .section-title { font-size: 15pt; font-weight: 800; color: {$p}; border-left: 8px solid {$p}; padding-left: 15px; margin-top: 30px; margin-bottom: 20px; text-transform: uppercase; background: #f8fafc; padding-top: 8px; padding-bottom: 8px; page-break-after: avoid; }
                    .section-item { margin-bottom: 25px; page-break-inside: avoid; }
                    .company { font-weight: 800; color: #000; font-size: 1.2em; }
                    .date { color: #64748b; font-weight: 600; float: right; text-transform: uppercase; font-size: 0.9em; }
                    .item-sub { color: {$p}; font-weight: 700; margin-bottom: 8px; font-size: 1.1em; display: block; }
                    .item-desc { color: #334155; padding-left: 20px; border-left: 1px solid #e2e8f0; line-height: 1.6; }
                    .skills-box { background: #f1f5f9; padding: 25px; border-radius: 12px; border-right: 15px solid {$p}; font-weight: 700; color: {$p}; }
                ";
            case 'health_clean':
                $p = $primaryColor ?? '#10b981';
                return "
                    .resume-page { background: white; }
                    body { font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #475569; margin: 0; padding: 0; }
                    .header { padding: 60px 40px; text-align: center; border-bottom: 2px solid #f1f5f9; margin-bottom: 40px; }
                    .header-text { width: 100%; }
                    .photo-container { width: 120px; height: 120px; margin: 0 auto 25px auto; border-radius: 50%; border: 4px solid {$p}; padding: 5px; background: white; box-shadow: 0 15px 35px rgba(16, 185, 129, 0.1); overflow: hidden; }
                    .photo { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
                    .name { font-size: 32pt; font-weight: 300; color: #0f172a; margin: 0; letter-spacing: 2px; text-transform: uppercase; }
                    .contact { font-size: 10.5pt; color: #94a3b8; margin-top: 15px; letter-spacing: 1px; }
                    .content-wrapper { padding: 0 100px; }
                    .section-title { font-size: 13pt; font-weight: 600; color: {$p}; margin-top: 40px; margin-bottom: 25px; text-align: center; text-transform: uppercase; letter-spacing: 3px; page-break-after: avoid; }
                    .section-title::before, .section-title::after { content: '—'; margin: 0 15px; opacity: 0.3; }
                    .section-item { margin-bottom: 35px; page-break-inside: avoid; }
                    .company { font-weight: 700; color: #1e293b; font-size: 1.1em; display: block; text-align: center; }
                    .date { color: #cbd5e1; font-size: 0.9em; display: block; text-align: center; margin-bottom: 5px; }
                    .item-sub { color: #64748b; font-weight: 500; font-style: italic; display: block; text-align: center; margin-bottom: 15px; }
                    .item-desc { font-size: 1em; text-align: justify; line-height: 1.6; border-top: 1px solid #f8fafc; padding-top: 15px; }
                    .skills-box { text-align: center; color: {$p}; font-weight: 600; padding: 20px; border: 1px dashed #cbd5e1; border-radius: 4px; letter-spacing: 1px; }
                ";
            case 'tech':
                $p = $primaryColor ?? '#2dd4bf';
                return "
                    .resume-page { background: #0f172a; }
                    body { font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #e2e8f0; background-color: #0f172a; margin: 0; padding: 0; }
                    .header { background: #1e293b; color: {$p}; padding: 40px; text-align: left; border-bottom: 6px solid {$p}; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
                    .header-text { float: left; width: 75%; }
                    .photo-container { float: right; width: 110px; height: 145px; border-radius: 12px; border: 2px solid {$p}; overflow: hidden; background: #0f172a; box-shadow: 0 0 20px rgba(45, 212, 191, 0.2); }
                    .photo { width: 100%; height: 100%; object-fit: cover; }
                    .name { font-size: 32pt; font-weight: 900; color: {$p}; text-transform: uppercase; margin: 0; letter-spacing: -2px; }
                    .contact { font-size: 10pt; color: #94a3b8; font-weight: 600; font-family: 'Courier New', monospace; margin-top: 10px; }
                    .content-wrapper { padding: 0 40px; }
                    .section-title { font-size: 13pt; font-weight: 800; color: {$p}; margin-top: 25px; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 2px; display: flex; align-items: center; gap: 15px; page-break-after: avoid; }
                    .section-title::after { content: ''; flex: 1; height: 1px; background: rgba(45, 212, 191, 0.3); }
                    .section-item { margin-bottom: 25px; background: rgba(30, 41, 59, 0.5); padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); page-break-inside: avoid; }
                    .company { font-weight: 800; color: #f8fafc; font-size: 1.25em; }
                    .date { color: {$p}; font-weight: bold; font-family: 'Courier New', monospace; font-size: 0.9em; float: right; background: rgba(45, 212, 191, 0.1); padding: 4px 12px; border-radius: 4px; }
                    .item-sub { color: #94a3b8; font-weight: 700; margin-top: 5px; display: block; font-size: 1.1em; }
                    .item-desc { color: #cbd5e1; margin-top: 15px; line-height: 1.6; border-left: 1px solid rgba(45, 212, 191, 0.2); padding-left: 15px; }
                    .skills-box { background: rgba(15, 23, 42, 0.8); border: 2px solid {$p}; padding: 20px; border-radius: 12px; color: {$p}; font-family: 'Courier New', monospace; font-weight: bold; box-shadow: inset 0 0 15px rgba(45, 212, 191, 0.1); }
                ";
            case 'tech_modern':
                $p = $primaryColor ?? '#4f46e5';
                return "
                    .resume-page { background: #f8fafc; }
                    body { font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #334155; background-color: #f8fafc; margin: 0; padding: 0; }
                    .header { background: white; color: #1e293b; padding: 50px 40px; margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; position: relative; }
                    .header::before { content: ''; position: absolute; left: 0; top: 0; width: 100%; height: 5px; background: {$p}; }
                    .header-text { float: left; width: 75%; }
                    .photo-container { float: right; width: 120px; height: 160px; border-radius: 24px; border: 4px solid white; box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden; transform: rotate(-2deg); }
                    .photo { width: 100%; height: 100%; object-fit: cover; }
                    .name { font-size: 36pt; font-weight: 800; color: #1e293b; margin: 0; letter-spacing: -2px; }
                    .contact { font-size: 11pt; color: #64748b; margin-top: 10px; font-weight: 500; }
                    .content-wrapper { padding: 0 40px; }
                    .section-title { font-size: 12pt; font-weight: 800; color: {$p}; margin-top: 30px; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 2px; page-break-after: avoid; }
                    .section-item { margin-bottom: 25px; background: white; padding: 25px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02); page-break-inside: avoid; }
                    .company { font-weight: 800; color: #1e293b; font-size: 1.2em; display: inline-block; }
                    .date { color: {$p}; font-weight: 700; float: right; background: #eef2ff; padding: 5px 15px; border-radius: 10px; font-size: 0.9em; }
                    .item-sub { color: #64748b; font-weight: 700; margin-top: 8px; display: block; }
                    .item-desc { margin-top: 15px; color: #475569; }
                    .skills-box { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 10px 30px rgba(79, 70, 229, 0.1); color: {$p}; font-weight: 800; border: 1px solid #e0e7ff; }
                ";
            case 'tech_minimal':
                $p = $primaryColor ?? '#111827';
                return "
                    .resume-page { background: white; }
                    body { font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #1f2937; margin: 0; padding: 0; }
                    .header { padding: 80px 40px 40px 40px; border-bottom: 4px solid {$p}; margin-bottom: 50px; }
                    .photo-container { float: right; width: 100px; height: 133px; border: 1px solid {$p}; padding: 3px; }
                    .photo { width: 100%; height: 100%; object-fit: cover; }
                    .header-text { float: left; width: 75%; }
                    .name { font-size: 42pt; font-weight: 900; margin: 0; color: {$p}; letter-spacing: -3px; line-height: 0.9; }
                    .contact { font-size: 10pt; color: #6b7280; margin-top: 20px; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; }
                    .content-wrapper { padding: 0 40px; }
                    .section-title { font-size: 11pt; font-weight: 950; color: {$p}; margin-top: 40px; margin-bottom: 25px; border-bottom: 2px solid {$p}; padding-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; page-break-after: avoid; }
                    .section-item { margin-bottom: 30px; page-break-inside: avoid; }
                    .company { font-weight: 900; color: {$p}; font-size: 1.3em; letter-spacing: -0.5px; }
                    .date { float: right; font-weight: 500; color: #6b7280; font-size: 0.95em; }
                    .item-sub { font-weight: 700; color: #4b5563; margin-top: 5px; font-size: 1em; text-transform: uppercase; }
                    .item-desc { color: #374151; margin-top: 15px; font-size: 1.05em; text-align: justify; }
                    .skills-box { border: 2px solid {$p}; padding: 25px; color: {$p}; font-weight: 500; font-size: 1em; letter-spacing: 0.5px; }
                ";
            case 'tech_creative':
                $p = $primaryColor ?? '#f59e0b';
                return "
                    .resume-page { background: #f8fafc; border: 15px solid #0f172a; }
                    body { font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #1e293b; margin: 0; padding: 0; }
                    .header { background: #0f172a; color: white; padding: 40px; border-bottom: 8px solid {$p}; }
                    .header-text { width: 100%; text-align: left; }
                    .name { font-size: 36pt; font-weight: 900; color: {$p}; letter-spacing: -1px; margin: 0; }
                    .contact { font-size: 10pt; color: #94a3b8; margin-top: 10px; font-family: monospace; }
                    .photo-container { float: right; width: 120px; height: 160px; border: 4px solid {$p}; border-radius: 10px; margin-top: -80px; background: white; }
                    .photo { width: 100%; height: 100%; object-fit: cover; }
                    .content-wrapper { padding: 30px 40px; }
                    .section-title { font-size: 16pt; font-weight: 900; color: #0f172a; margin-top: 25px; margin-bottom: 15px; border-left: 10px solid {$p}; padding-left: 15px; text-transform: uppercase; background: #f1f5f9; padding-top: 5px; padding-bottom: 5px; page-break-after: avoid; }
                    .section-item { margin-bottom: 20px; page-break-inside: avoid; }
                    .company { font-weight: 900; color: #0f172a; font-size: 1.2em; }
                    .date { color: {$p}; font-weight: 800; font-family: monospace; }
                    .item-sub { color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
                    .item-desc { color: #334155; margin-top: 5px; border-top: 1px dashed {$p}; padding-top: 10px; }
                    .skills-box { display: flex; flex-wrap: wrap; gap: 8px; font-weight: 800; color: #0f172a; }
                ";
            case 'health_executive':
                $p = $primaryColor ?? '#1e293b';
                return "
                    .resume-page { background: white; border-top: 20px solid {$p}; }
                    body { font-size: {$fontSize}pt; line-height: {$lineHeight}; color: #334155; margin: 0; padding: 0; }
                    .header { padding: 50px 40px; text-align: center; border-bottom: 1px solid #e2e8f0; }
                    .name { font-size: 30pt; font-weight: 400; color: {$p}; letter-spacing: 2px; text-transform: uppercase; margin: 0; }
                    .contact { font-size: 10pt; color: #64748b; margin-top: 15px; letter-spacing: 1px; }
                    .photo-container { width: 90px; height: 120px; margin: 0 auto 20px auto; border: 1px solid #e2e8f0; padding: 5px; }
                    .photo { width: 100%; height: 100%; object-fit: cover; }
                    .content-wrapper { padding: 40px 60px; }
                    .section-title { font-size: 13pt; font-weight: 700; color: {$p}; margin-top: 30px; margin-bottom: 20px; text-align: center; letter-spacing: 3px; text-transform: uppercase; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; page-break-after: avoid; }
                    .section-item { margin-bottom: 30px; page-break-inside: avoid; }
                    .company { font-weight: 700; color: #1e293b; font-size: 1.1em; }
                    .date { color: #94a3b8; font-style: italic; }
                    .item-header { border-bottom: 1px solid #f8fafc; padding-bottom: 5px; margin-bottom: 10px; }
                    .item-sub { color: {$p}; font-weight: 600; font-size: 1em; }
                    .item-desc { font-style: normal; color: #475569; padding-top: 5px; }
                    .skills-box { text-align: center; font-style: italic; color: {$p}; border: 1px double #e2e8f0; padding: 20px; background: #fcfdfe; }
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
