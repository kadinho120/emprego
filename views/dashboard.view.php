<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Currículos - ApproveMax</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/dashboard-desktop.css" media="screen and (min-width: 768px)">
    <link rel="stylesheet" href="public/assets/css/dashboard-mobile.css" media="screen and (max-width: 767px)">
</head>

<body>
    <div class="container">
        <header class="header">
            <div class="logo">ApproveMax</div>
            <div style="display: flex; gap: 1rem;">
                <a href="index.php" class="btn btn-outline">🏠 Home</a>
                <a href="generator.php" class="btn btn-primary">Criar Novo Currículo</a>
                <a href="logout.php" class="btn btn-outline" style="color: #f87171;">Sair</a>
            </div>
        </header>

        <h1 style="margin-bottom: 2rem; font-size: 2rem;">Meus Currículos</h1>

        <?php if (isset($_GET['success'])): ?>
            <div
                style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); color: #4ade80; padding: 1rem; border-radius: 12px; margin-bottom: 2rem; font-size: 0.9rem;">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div
                style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171; padding: 1rem; border-radius: 12px; margin-bottom: 2rem; font-size: 0.9rem;">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($resumes)): ?>
            <div class="empty-state">
                <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Você ainda não gerou nenhum currículo.</p>
                <a href="generator.php" class="btn btn-primary">Começar Agora</a>
            </div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($resumes as $resume): ?>
                    <div class="resume-card">
                        <div class="resume-info">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <h3>
                                    <?php echo htmlspecialchars($resume['full_name']); ?>
                                </h3>
                                <span
                                    style="font-size: 0.75rem; color: var(--text-muted); background: rgba(255,255,255,0.05); padding: 4px 8px; border-radius: 6px;">
                                    👁️
                                    <?php echo (int) $resume['views']; ?>
                                </span>
                            </div>
                            <p>
                                <?php echo date('d/m/Y H:i', strtotime($resume['created_at'])); ?>
                            </p>
                            <p
                                style="margin-top: 5px; color: var(--primary); font-weight: 600; text-transform: uppercase; font-size: 0.75rem;">
                                <?php echo htmlspecialchars($resume['template_id']); ?>
                            </p>
                        </div>
                        <div class="resume-actions">
                            <a href="generate-pdf.php?id=<?php echo $resume['id']; ?>" target="_blank" class="btn btn-outline"
                                style="background: rgba(99, 102, 241, 0.1); border-color: rgba(99, 102, 241, 0.3);">PDF</a>
                            <a href="export-word.php?id=<?php echo $resume['id']; ?>" class="btn btn-outline"
                                style="border-color: rgba(96, 165, 250, 0.3); color: #60a5fa;">Word</a>
                            <a href="#" onclick="openAtsModal(<?php echo $resume['id']; ?>)" class="btn btn-outline"
                                style="border-color: rgba(245, 158, 11, 0.3); color: #f59e0b;">ATS</a>
                            <a href="#"
                                onclick="copyLink('<?php echo (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/cv.php?slug=' . $resume['slug']; ?>')"
                                class="btn btn-outline" style="border-color: rgba(16, 185, 129, 0.2); color: #10b981;">Link</a>
                            <a href="generator.php?id=<?php echo $resume['id']; ?>" class="btn btn-outline"
                                style="border-color: var(--primary); color: var(--primary);">Editar</a>
                            <a href="duplicate-resume.php?id=<?php echo $resume['id']; ?>" class="btn btn-outline">Duplicar</a>
                            <a href="delete-resume.php?id=<?php echo $resume['id']; ?>"
                                onclick="return confirm('Tem certeza que deseja excluir este currículo? Esta ação não pode ser desfeita.')"
                                class="btn btn-outline"
                                style="color: #f87171; border-color: rgba(248, 113, 113, 0.2); background: rgba(248, 113, 113, 0.05);">Excluir</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="atsModal" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom: 1rem; color: #f59e0b;">Análise de IA (ATS)</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">Cole abaixo a descrição da
                vaga para ver quão bem seu currículo se adapta aos requisitos.</p>

            <input type="hidden" id="atsResumeId">
            <textarea id="jobDescription"
                style="width: 100%; height: 150px; background: #0f172a; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: white; padding: 1rem; margin-bottom: 1rem;"
                placeholder="Cole aqui a descrição da vaga..."></textarea>

            <div style="display: flex; gap: 1rem;">
                <button onclick="analyzeAts()" class="btn btn-primary" id="btnAnalyze"
                    style="flex: 1; background: #f59e0b;">Analisar Agora</button>
                <button onclick="closeAtsModal()" class="btn btn-outline" style="flex: 1;">Fechar</button>
            </div>

            <div id="atsResult" class="ats-result">
                <div class="score-badge" id="atsScore">0%</div>
                <p style="font-size: 0.9rem; margin-bottom: 0.5rem; font-weight: 700;">Palavras-chave encontradas:</p>
                <div id="atsMatches" style="margin-bottom: 1rem;"></div>

                <p style="font-size: 0.9rem; margin-bottom: 0.5rem; font-weight: 700; color: #f87171;">Palavras-chave
                    ausentes (Dica: adicione-as!):</p>
                <div id="atsMissing"></div>
            </div>
        </div>
    </div>

    <script src="public/assets/js/dashboard.js"></script>
</body>

</html>