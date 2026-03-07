<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Auth;
use App\Config\Database;

Auth::requireLogin();
Auth::init();

$db = Database::getInstance();
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];

// Fetch User Resumes (Admins see all)
if ($userRole === 'admin') {
    $stmt = $db->query("SELECT * FROM resumes ORDER BY created_at DESC");
} else {
    $stmt = $db->prepare("SELECT * FROM resumes WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
}
$resumes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Currículos - ApproveMax</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --background: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text-main);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        }

        .btn-outline {
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-main);
            background: rgba(255, 255, 255, 0.05);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .resume-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .resume-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .resume-info h3 {
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }

        .resume-info p {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .resume-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: auto;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--card-bg);
            border-radius: 24px;
            border: 2px dashed rgba(255, 255, 255, 0.1);
        }

        /* ATS Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: var(--card-bg);
            padding: 2.5rem;
            border-radius: 24px;
            width: 90%;
            max-width: 600px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .ats-result {
            margin-top: 1.5rem;
            padding: 1.5rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.03);
            display: none;
        }

        .score-badge {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            text-align: center;
            margin-bottom: 1rem;
        }

        .tag {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            margin: 2px;
        }

        .tag-match {
            background: rgba(34, 197, 94, 0.2);
            color: #4ade80;
        }

        .tag-missing {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }
    </style>
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
                                <h3><?php echo htmlspecialchars($resume['full_name']); ?></h3>
                                <span
                                    style="font-size: 0.75rem; color: var(--text-muted); background: rgba(255,255,255,0.05); padding: 4px 8px; border-radius: 6px;">
                                    👁️ <?php echo (int) $resume['views']; ?>
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
                        <div class="resume-actions" style="flex-wrap: wrap;">
                            <a href="generate-pdf.php?id=<?php echo $resume['id']; ?>" target="_blank" class="btn btn-outline"
                                style="flex: 1 1 30%; text-align: center; background: rgba(99, 102, 241, 0.1); border-color: rgba(99, 102, 241, 0.3);">PDF</a>
                            <a href="export-word.php?id=<?php echo $resume['id']; ?>" class="btn btn-outline"
                                style="flex: 1 1 30%; text-align: center; border-color: rgba(96, 165, 250, 0.3); color: #60a5fa;">Word</a>
                            <a href="#" onclick="openAtsModal(<?php echo $resume['id']; ?>)" class="btn btn-outline"
                                style="flex: 1 1 30%; text-align: center; border-color: rgba(245, 158, 11, 0.3); color: #f59e0b;">💬
                                ATS</a>
                            <a href="#"
                                onclick="copyLink('<?php echo (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/cv.php?slug=' . $resume['slug']; ?>')"
                                class="btn btn-outline"
                                style="flex: 1 1 30%; text-align: center; border-color: rgba(16, 185, 129, 0.2); color: #10b981;">🔗
                                Link</a>
                            <a href="duplicate-resume.php?id=<?php echo $resume['id']; ?>" class="btn btn-outline"
                                style="flex: 1 1 30%; text-align: center;">👯 Duplicar</a>
                            <a href="delete-resume.php?id=<?php echo $resume['id']; ?>"
                                onclick="return confirm('Tem certeza que deseja excluir este currículo? Esta ação não pode ser desfeita.')"
                                class="btn btn-outline"
                                style="flex: 1 1 30%; text-align: center; color: #f87171; border-color: rgba(248, 113, 113, 0.2); background: rgba(248, 113, 113, 0.05);">🗑️
                                Excluir</a>
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

    <script>
        function copyLink(url) {
            navigator.clipboard.writeText(url).then(() => {
                alert('Link copiado para a área de transferência!');
            }).catch(err => {
                console.error('Erro ao copiar link: ', err);
                alert('Erro ao copiar link. Tente copiar manualmente.');
            });
        }

        function openAtsModal(id) {
            document.getElementById('atsResumeId').value = id;
            document.getElementById('atsModal').style.display = 'flex';
            document.getElementById('atsResult').style.display = 'none';
            document.getElementById('jobDescription').value = '';
        }

        function closeAtsModal() {
            document.getElementById('atsModal').style.display = 'none';
        }

        function analyzeAts() {
            const id = document.getElementById('atsResumeId').value;
            const desc = document.getElementById('jobDescription').value;
            const btn = document.getElementById('btnAnalyze');

            if (!desc) return alert('Por favor, cole a descrição da vaga.');

            btn.disabled = true;
            btn.innerText = 'Analisando...';

            const formData = new FormData();
            formData.append('resume_id', id);
            formData.append('job_description', desc);

            fetch('ats-analysis.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.error) throw new Error(data.error);

                    document.getElementById('atsScore').innerText = data.score + '%';

                    const matchesDiv = document.getElementById('atsMatches');
                    matchesDiv.innerHTML = data.matches.map(m => `<span class="tag tag-match">${m}</span>`).join('');

                    const missingDiv = document.getElementById('atsMissing');
                    missingDiv.innerHTML = data.missing.map(m => `<span class="tag tag-missing">${m}</span>`).join('');

                    document.getElementById('atsResult').style.display = 'block';
                })
                .catch(err => alert('Erro na análise: ' + err.message))
                .finally(() => {
                    btn.disabled = false;
                    btn.innerText = 'Analisar Agora';
                });
        }
    </script>
</body>

</html>