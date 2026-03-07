<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Auth;
use App\Config\Database;

Auth::requireLogin();
Auth::init();

$db = Database::getInstance();
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? 'user';

$resumeData = null;
$experiencesData = [];
$educationData = [];
$skillsData = "";

if (isset($_GET['id'])) {
    $resumeId = (int) $_GET['id'];

    if ($userRole === 'admin') {
        $stmt = $db->prepare("SELECT * FROM resumes WHERE id = ?");
        $stmt->execute([$resumeId]);
    } else {
        $stmt = $db->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
        $stmt->execute([$resumeId, $userId]);
    }

    $resumeData = $stmt->fetch();

    if ($resumeData) {
        $stmt = $db->prepare("SELECT * FROM experiences WHERE resume_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$resumeId]);
        $experiencesData = $stmt->fetchAll();
        if (empty($experiencesData))
            $experiencesData = [[]]; // At least one for the form

        $stmt = $db->prepare("SELECT * FROM education WHERE resume_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$resumeId]);
        $educationData = $stmt->fetchAll();
        if (empty($educationData))
            $educationData = [[]]; // At least one for the form

        $stmt = $db->prepare("SELECT * FROM skills WHERE resume_id = ?");
        $stmt->execute([$resumeId]);
        $skillsList = $stmt->fetchAll();
        $skillsData = implode(', ', array_column($skillsList, 'skill_name'));
    }
}

if (!$resumeData) {
    $experiencesData = [[]];
    $educationData = [[]];
}

$initialTemplate = $resumeData['template_id'] ?? ((isset($_GET['niche']) && $_GET['niche'] === 'tech') ? 'tech' : ((isset($_GET['niche']) && $_GET['niche'] === 'health') ? 'health' : 'tech'));
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Currículo - Passo a Passo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg: #0f172a;
            --card-bg: #1e293b;
            --input-bg: #0f172a;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            padding: 2rem 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            align-items: start;
        }

        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
            }

            .preview-container {
                display: none;
            }
        }

        .preview-container {
            position: sticky;
            top: 2rem;
            background: white;
            border-radius: 24px;
            height: calc(100vh - 4rem);
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .preview-iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Responsive overrides for the iframe content */
        #previewIframe {
            background: #f1f5f9;
        }

        .drag-handle {
            cursor: move;
            color: var(--text-muted);
            margin-right: 10px;
            display: inline-flex;
            align-items: center;
        }

        .sortable-ghost {
            opacity: 0.4;
            background: var(--primary) !important;
        }

        .form-card {
            background-color: var(--card-bg);
            padding: 3rem;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3rem;
            position: relative;
        }

        .step-indicator::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(255, 255, 255, 0.1);
            z-index: 1;
        }

        .step-dot {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--card-bg);
            border: 2px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .step-dot.active {
            border-color: var(--primary);
            background: var(--primary);
            color: white;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.5);
        }

        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--input-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-family: inherit;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        input:focus,
        textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }

        /* Template Selection Grid */
        .template-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .template-card {
            background: var(--input-bg);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .template-card:hover {
            transform: translateY(-5px);
            border-color: rgba(99, 102, 241, 0.5);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.3);
        }

        .template-card.selected {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
        }

        .template-preview {
            width: 100%;
            aspect-ratio: 3/4;
            background: #f1f5f9;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .template-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .template-info {
            padding: 1rem;
            text-align: center;
            background: var(--card-bg);
        }

        .template-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-main);
        }

        .selected-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--primary);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }

        .template-card.selected .selected-badge {
            display: flex;
        }

        input[name="template_id"] {
            display: none;
        }

        .btn-group {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 2rem;
        }

        button {
            padding: 0.75rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .btn-prev {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        .btn-next,
        .btn-submit {
            background: var(--primary);
            border: none;
            color: white;
        }

        button:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .dynamic-field {
            background: rgba(255, 255, 255, 0.02);
            padding: 1.5rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            border: 1px dashed rgba(255, 255, 255, 0.1);
        }

        .add-more {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
        }

        .btn-remove {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 0.4rem 0.8rem;
            font-size: 0.75rem;
            border-radius: 8px;
            float: right;
            margin-top: -10px;
        }

        .field-tip {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: -1.2rem;
            margin-bottom: 1.2rem;
            line-height: 1.3;
            display: block;
        }

        /* Custom File Upload */
        .file-upload-wrapper {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .file-upload-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            padding: 2rem;
            background: rgba(15, 23, 42, 0.4);
            border: 2px dashed rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .file-upload-btn:hover {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
            transform: translateY(-2px);
        }

        .file-upload-btn svg {
            width: 32px;
            height: 32px;
            color: var(--primary);
            opacity: 0.8;
        }

        .file-upload-btn span {
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-main);
        }

        .file-upload-btn small {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        #photoInput {
            position: absolute;
            width: 0.1px;
            height: 0.1px;
            opacity: 0;
            overflow: hidden;
            z-index: -1;
        }

        /* Suggestion Styles */
        .suggestion-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .btn-suggestion {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            border: 1px solid rgba(99, 102, 241, 0.2);
            padding: 0.3rem 0.8rem;
            font-size: 0.75rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-suggestion:hover {
            background: var(--primary);
            color: white;
        }

        .modal-suggestion {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.95);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }

        .suggestion-content {
            background: var(--card-bg);
            padding: 2.5rem;
            border-radius: 24px;
            max-width: 700px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .suggestion-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 1.2rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .suggestion-item:hover {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
            transform: translateX(5px);
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="form-wrapper">
            <div style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem; padding: 0 1rem;">
                <?php if (Auth::isLoggedIn()): ?>
                    <div style="display: flex; gap: 1.5rem; align-items: center;">
                        <a href="dashboard.php"
                            style="color: var(--text-main); text-decoration: none; font-size: 0.9rem; font-weight: 600;">Meus
                            Currículos</a>
                        <a href="logout.php"
                            style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; font-weight: 600;">Sair</a>
                    </div>
                <?php else: ?>
                    <a href="logout.php"
                        style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: color 0.3s;"
                        onmouseover="this.style.color='#fff'" onmouseout="this.style.color='var(--text-muted)'">Sair da
                        Conta</a>
                <?php endif; ?>
            </div>
            <div class="form-card">
                <div style="display: flex; justify-content: center; margin-bottom: 2rem;">
                    <div class="logo"
                        style="font-size: 1.8rem; font-weight: 800; background: linear-gradient(135deg, #fff, var(--primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        ApproveMax</div>
                </div>
                <div class="step-indicator">
                    <div class="step-dot active" data-step="1">1</div>
                    <div class="step-dot" data-step="2">2</div>
                    <div class="step-dot" data-step="3">3</div>
                    <div class="step-dot" data-step="4">4</div>
                    <div class="step-dot" data-step="5">5</div>
                    <div class="step-dot" data-step="6">6</div>
                </div>

                <form id="resumeForm" action="process.php" method="POST" enctype="multipart/form-data">
                    <?php if ($resumeData): ?>
                        <input type="hidden" name="resume_id" value="<?php echo $resumeData['id']; ?>">
                    <?php endif; ?>
                    <!-- Passo 1: Seleção de Modelo -->
                    <div class="form-step active" id="step1">
                        <h2 style="margin-bottom: 0.5rem;">Escolha seu Modelo</h2>
                        <p style="color: var(--text-muted); margin-bottom: 2rem; font-size: 0.9rem;">Selecione o layout
                            que
                            mais combina com seu perfil profissional.</p>

                        <input type="hidden" name="template_id" id="templateInput"
                            value="<?php echo htmlspecialchars($initialTemplate); ?>" required>

                        <div class="template-grid">
                            <?php if (isset($_GET['niche']) && $_GET['niche'] === 'tech'): ?>
                                <div class="template-card <?php echo $initialTemplate === 'tech' ? 'selected' : ''; ?>"
                                    onclick="selectTemplate('tech', this)">
                                    <div class="selected-badge">✓</div>
                                    <div class="template-preview">
                                        <div
                                            style="background: #0f172a; width: 100%; height: 100%; display: flex; flex-direction: column; padding: 10px; gap: 5px;">
                                            <div style="background: var(--primary); height: 20px; width: 60%;"></div>
                                            <div style="background: #1e293b; height: 10px; width: 100%;"></div>
                                            <div style="background: #1e293b; height: 10px; width: 100%;"></div>
                                        </div>
                                    </div>
                                    <div class="template-info">
                                        <div class="template-name">TI - Dark Mode</div>
                                    </div>
                                </div>
                                <div class="template-card" onclick="selectTemplate('tech_modern', this)">
                                    <div class="selected-badge">✓</div>
                                    <div class="template-preview">
                                        <div
                                            style="background: #4f46e5; width: 100%; height: 100%; display: flex; flex-direction: column; padding: 10px; gap: 5px;">
                                            <div style="background: white; height: 20px; width: 60%;"></div>
                                            <div style="background: rgba(255,255,255,0.2); height: 10px; width: 100%;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="template-info">
                                        <div class="template-name">Modern Blue</div>
                                    </div>
                                </div>
                                <div class="template-card" onclick="selectTemplate('tech_minimal', this)">
                                    <div class="selected-badge">✓</div>
                                    <div class="template-preview">
                                        <div
                                            style="background: white; width: 100%; height: 100%; display: flex; flex-direction: column; padding: 10px; border: 1px solid #ddd; gap: 5px;">
                                            <div style="background: #111827; height: 15px; width: 70%;"></div>
                                            <div style="background: #eee; height: 8px; width: 100%;"></div>
                                        </div>
                                    </div>
                                    <div class="template-info">
                                        <div class="template-name">Minimalist Professional</div>
                                    </div>
                                </div>
                            <?php elseif (isset($_GET['niche']) && $_GET['niche'] === 'health'): ?>
                                <div class="template-card <?php echo $initialTemplate === 'health' ? 'selected' : ''; ?>"
                                    onclick="selectTemplate('health', this)">
                                    <div class="selected-badge">✓</div>
                                    <div class="template-preview">
                                        <div
                                            style="background: #f0fdfa; width: 100%; height: 100%; display: flex; flex-direction: column; border-bottom: 5px solid #0d9488;">
                                        </div>
                                    </div>
                                    <div class="template-info">
                                        <div class="template-name">Health - Teal Basic</div>
                                    </div>
                                </div>
                                <div class="template-card <?php echo $initialTemplate === 'health_professional' ? 'selected' : ''; ?>"
                                    onclick="selectTemplate('health_professional', this)">
                                    <div class="selected-badge">✓</div>
                                    <div class="template-preview">
                                        <div style="background: #1e3a8a; width: 100%; height: 100%;"></div>
                                    </div>
                                    <div class="template-info">
                                        <div class="template-name">Healthcare Leader</div>
                                    </div>
                                </div>
                                <div class="template-card <?php echo $initialTemplate === 'health_clean' ? 'selected' : ''; ?>"
                                    onclick="selectTemplate('health_clean', this)">
                                    <div class="selected-badge">✓</div>
                                    <div class="template-preview">
                                        <div
                                            style="background: white; width: 100%; height: 100%; border-bottom: 1px solid #eee;">
                                        </div>
                                    </div>
                                    <div class="template-info">
                                        <div class="template-name">Clean Medical</div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="template-card selected" onclick="selectTemplate('tech', this)">
                                    <div class="selected-badge">✓</div>
                                    <div class="template-preview">
                                        <div style="background: #0f172a; width: 100%; height: 100%;"></div>
                                    </div>
                                    <div class="template-info">
                                        <div class="template-name">TI - Dark Mode</div>
                                    </div>
                                </div>
                                <div class="template-card" onclick="selectTemplate('health', this)">
                                    <div class="selected-badge">✓</div>
                                    <div class="template-preview">
                                        <div
                                            style="background: #f0fdfa; width: 100%; height: 100%; border-bottom: 5px solid #0d9488;">
                                        </div>
                                    </div>
                                    <div class="template-info">
                                        <div class="template-name">Saúde - Teal Basic</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="btn-group">
                            <div></div>
                            <button type="button" class="btn-next" onclick="nextStep(2)">Começar Preenchimento</button>
                        </div>
                    </div>

                    <!-- Passo 2: Dados Pessoais -->
                    <div class="form-step" id="step2">
                        <h2 style="margin-bottom: 1.5rem;">Dados Pessoais</h2>
                        <label>Nome Completo</label>
                        <input type="text" name="full_name" required placeholder="João Silva"
                            value="<?php echo htmlspecialchars($resumeData['full_name'] ?? ''); ?>">
                        <span class="field-tip">Use seu nome completo e oficial. Evite apelidos.</span>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label>E-mail</label>
                                <input type="email" name="email" required placeholder="joao@email.com"
                                    value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>">
                                <span class="field-tip">Use um e-mail profissional e que você acesse sempre.</span>
                            </div>
                            <div>
                                <label>Telefone</label>
                                <input type="text" name="phone" id="phone" placeholder="(11) 99999-9999" maxlength="15"
                                    required value="<?php echo htmlspecialchars($resumeData['phone'] ?? ''); ?>">
                                <span class="field-tip">Coloque seu número principal com o DDD.</span>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                            <div>
                                <label>Cidade</label>
                                <input type="text" name="city" placeholder="São Paulo" required
                                    value="<?php echo htmlspecialchars($resumeData['city'] ?? ''); ?>">
                                <span class="field-tip">Onde você mora atualmente?</span>
                            </div>
                            <div>
                                <label>Estado (UF)</label>
                                <input type="text" name="state" placeholder="SP" required
                                    value="<?php echo htmlspecialchars($resumeData['state'] ?? ''); ?>">
                                <span class="field-tip">Ex: SP, RJ, ES...</span>
                            </div>
                        </div>

                        <div class="suggestion-header">
                            <label style="margin-bottom: 0;">Resumo Profissional</label>
                            <button type="button" class="btn-suggestion" onclick="openSuggestions('summary', this)">✨
                                Ver
                                Sugestões</button>
                        </div>
                        <textarea name="summary" rows="4" placeholder="Fale um pouco sobre sua carreira..."
                            required><?php echo htmlspecialchars($resumeData['summary'] ?? ''); ?></textarea>
                        <span class="field-tip">Escreva 3 ou 4 linhas sobre sua carreira e seu maior diferencial. Isso é
                            a
                            primeira coisa que o contratante lê.</span>

                        <div class="btn-group">
                            <button type="button" class="btn-prev" onclick="nextStep(1)">Anterior</button>
                            <button type="button" class="btn-next" onclick="nextStep(3)">Próximo</button>
                        </div>
                    </div>

                    <!-- Passo 3: Foto -->
                    <div class="form-step" id="step3">
                        <h2 style="margin-bottom: 1.5rem;">Sua Foto</h2>
                        <label>Sua Melhor Foto (Opcional)</label>
                        <div class="file-upload-wrapper">
                            <input type="hidden" name="photo_base64" id="photoBase64">
                            <input type="file" name="photo" id="photoInput" accept="image/*">
                            <input type="hidden" name="photo_base64" id="photoBase64">
                            <button type="button" class="btn-upload" id="uploadBtn" <?php echo ($resumeData && $resumeData['photo_path']) ? 'style="border-color: var(--primary);"' : ''; ?>>
                                📸
                                <span><?php echo ($resumeData && $resumeData['photo_path']) ? 'Trocar Foto' : 'Escolher Foto'; ?></span>
                            </button>
                        </div>
                        <input type="hidden" name="photo_path"
                            value="<?php echo htmlspecialchars($resumeData['photo_path'] ?? ''); ?>">
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 1rem;">
                            Dica: Uma foto profissional aumenta suas chances em 70%.
                        </p>

                        <div id="photoPreview"
                            style="display: <?php echo ($resumeData && $resumeData['photo_path']) ? 'block' : 'none'; ?>; margin-top: 1.5rem; text-align: center;">
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">
                                Visualização:</p>
                            <img id="previewImg"
                                src="<?php echo ($resumeData && $resumeData['photo_path']) ? $resumeData['photo_path'] : ''; ?>"
                                style="width: 150px; height: 200px; object-fit: cover; border-radius: 12px; border: 2px solid var(--primary);">
                        </div>

                        <div class="btn-group">
                            <button type="button" class="btn-prev" onclick="nextStep(2)">Anterior</button>
                            <button type="button" class="btn-next" onclick="nextStep(4)">Próximo</button>
                        </div>
                    </div>

                    <!-- Passo 4: Experiência -->
                    <div class="form-step" id="step4">
                        <h2 style="margin-bottom: 1.5rem;">Experiência Profissional</h2>
                        <div id="experienceContainer">
                            <?php foreach ($experiencesData as $index => $exp): ?>
                                <div class="dynamic-field">
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                        <div class="drag-handle">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M4 8h16M4 16h16" />
                                            </svg>
                                        </div>
                                        <?php if ($index > 0): ?>
                                            <button type="button" class="btn-remove"
                                                onclick="removeField(this)">Remover</button>
                                        <?php endif; ?>
                                    </div>
                                    <label>Empresa</label>
                                    <input type="text" name="experience[<?php echo $index; ?>][company]"
                                        value="<?php echo htmlspecialchars($exp['company'] ?? ''); ?>">
                                    <span class="field-tip">Nome da empresa ou hospital onde trabalhou.</span>

                                    <label>Cargo</label>
                                    <input type="text" name="experience[<?php echo $index; ?>][position]"
                                        value="<?php echo htmlspecialchars($exp['position'] ?? ''); ?>">
                                    <span class="field-tip">Seu título oficial (Ex: Enfermeiro Obstetra, Desenvolvedor
                                        Backend).</span>

                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                        <div>
                                            <label>Início</label>
                                            <input type="text" name="experience[<?php echo $index; ?>][start_date]"
                                                class="date-mask" placeholder="MM/AAAA" maxlength="7"
                                                value="<?php echo htmlspecialchars($exp['start_date'] ?? ''); ?>">
                                        </div>
                                        <div>
                                            <label>Fim</label>
                                            <input type="text" name="experience[<?php echo $index; ?>][end_date]"
                                                class="date-mask" placeholder="Atual" maxlength="7"
                                                value="<?php echo htmlspecialchars($exp['end_date'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <span class="field-tip">Use o formato Mês/Ano (Ex: 05/2020). Se ainda trabalhar lá,
                                        escreva
                                        "Atual" no campo Fim.</span>

                                    <div class="suggestion-header">
                                        <label style="margin-bottom: 0;">Descrição</label>
                                        <button type="button" class="btn-suggestion"
                                            onclick="openSuggestions('experience', this)">✨ Ver Sugestões</button>
                                    </div>
                                    <textarea name="experience[<?php echo $index; ?>][description]"
                                        rows="3"><?php echo htmlspecialchars($exp['description'] ?? ''); ?></textarea>
                                    <span class="field-tip">O que você fazia no dia a dia? Cite 2 ou 3 tarefas
                                        importantes.</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="add-more" onclick="addExperience()">+ Adicionar
                            Experiência</button>

                        <div class="btn-group">
                            <button type="button" class="btn-prev" onclick="nextStep(3)">Anterior</button>
                            <button type="button" class="btn-next" onclick="nextStep(5)">Próximo</button>
                        </div>
                    </div>

                    <!-- Passo 5: Formação -->
                    <div class="form-step" id="step5">
                        <h2 style="margin-bottom: 1.5rem;">Educação</h2>
                        <div id="educationContainer">
                            <?php foreach ($educationData as $index => $edu): ?>
                                <div class="dynamic-field">
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                        <div class="drag-handle">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M4 8h16M4 16h16" />
                                            </svg>
                                        </div>
                                        <?php if ($index > 0): ?>
                                            <button type="button" class="btn-remove"
                                                onclick="removeField(this)">Remover</button>
                                        <?php endif; ?>
                                    </div>
                                    <label>Instituição</label>
                                    <input type="text" name="education[<?php echo $index; ?>][institution]" required
                                        value="<?php echo htmlspecialchars($edu['institution'] ?? ''); ?>">
                                    <span class="field-tip">Escola, Faculdade ou Centro Tecnológico.</span>

                                    <label>Curso/Grau</label>
                                    <input type="text" name="education[<?php echo $index; ?>][degree]" required
                                        value="<?php echo htmlspecialchars($edu['degree'] ?? ''); ?>">
                                    <span class="field-tip">Ex: Graduação, Técnico, MBA, Ensino Médio...</span>

                                    <label>Área de Estudo</label>
                                    <input type="text" name="education[<?php echo $index; ?>][field_of_study]" required
                                        value="<?php echo htmlspecialchars($edu['field_of_study'] ?? ''); ?>">
                                    <span class="field-tip">Ex: Enfermagem, Ciência da Computação, Administração...</span>

                                    <label>Conclusão</label>
                                    <input type="text" name="education[<?php echo $index; ?>][graduation_date]"
                                        class="date-mask" placeholder="MM/AAAA" maxlength="7" required
                                        value="<?php echo htmlspecialchars($edu['graduation_date'] ?? ''); ?>">
                                    <span class="field-tip">Data em que se formou ou previsão de formatura.</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="add-more" onclick="addEducation()">+ Adicionar Formação</button>

                        <div class="btn-group">
                            <button type="button" class="btn-prev" onclick="nextStep(4)">Anterior</button>
                            <button type="button" class="btn-next" onclick="nextStep(6)">Próximo</button>
                        </div>
                    </div>

                    <!-- Passo 6: Habilidades e Estilo -->
                    <div class="form-step" id="step6">
                        <h2 style="margin-bottom: 1.5rem;">Habilidades e Estilo</h2>
                        <div class="suggestion-header">
                            <label style="margin-bottom: 0;">Habilidades (separadas por vírgula)</label>
                            <button type="button" class="btn-suggestion" onclick="openSuggestions('skills', this)">✨ Ver
                                Sugestões</button>
                        </div>
                        <input type="text" name="skills" placeholder="PHP, PostgreSQL, Docker, UX Design" required
                            value="<?php echo htmlspecialchars($skillsData); ?>">
                        <span class="field-tip">Liste ferramentas, tecnologias ou competências que você domina. Ex:
                            Excel
                            Avançado, Punção Venosa, Java, Liderança de Equipe...</span>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                            <div>
                                <label>Cor Principal</label>
                                <input type="color" name="primary_color"
                                    value="<?php echo htmlspecialchars($resumeData['primary_color'] ?? '#6366f1'); ?>"
                                    style="height: 50px; padding: 5px;">
                            </div>
                            <div>
                                <label>Fonte</label>
                                <select name="font_family">
                                    <option value="jakarta" <?php echo ($resumeData && ($resumeData['font_family'] ?? '') === 'jakarta') ? 'selected' : ''; ?>>Plus Jakarta Sans</option>
                                    <option value="inter" <?php echo ($resumeData && ($resumeData['font_family'] ?? '') === 'inter') ? 'selected' : ''; ?>>Inter</option>
                                    <option value="roboto" <?php echo ($resumeData && ($resumeData['font_family'] ?? '') === 'roboto') ? 'selected' : ''; ?>>Roboto</option>
                                    <option value="outfit" <?php echo ($resumeData && ($resumeData['font_family'] ?? '') === 'outfit') ? 'selected' : ''; ?>>Outfit</option>
                                </select>
                            </div>
                        </div>

                        <div class="btn-group">
                            <button type="button" class="btn-prev" onclick="nextStep(5)">Anterior</button>
                            <button type="submit" class="btn-submit">Gerar Currículo</button>
                        </div>

                        <?php if (isset($resume['slug'])): ?>
                            <div style="margin-top: 1.5rem; text-align: center;">
                                <a href="cv.php?slug=<?php echo $resume['slug']; ?>" target="_blank"
                                    style="color: var(--primary); text-decoration: none; font-weight: 500; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                                    </svg>
                                    Visualizar Currículo Online (Público)
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div> <!-- .form-wrapper -->

        <div class="preview-container">
            <iframe id="previewIframe" class="preview-iframe"></iframe>
        </div>

        <!-- Suggestion Modal -->
        <div id="suggestionModal" class="modal-suggestion" onclick="closeSuggestions()">
            <div class="suggestion-content" onclick="event.stopPropagation()">
                <h3 style="margin-bottom: 1.5rem; color: var(--primary);">Sugestões Profissionais</h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem;">Escolha uma opção
                    abaixo.
                    Você poderá editá-la depois.</p>
                <div id="suggestionsList"></div>
                <button type="button" class="btn-prev" onclick="closeSuggestions()"
                    style="width: 100%; margin-top: 1rem;">Fechar</button>
            </div>
        </div>

        <script>
            const niche = '<?php echo $_GET['niche'] ?? ''; ?>';
            let currentTargetField = null;

            const suggestionsData = {
                tech: {
                    summary: [
                        "Desenvolvedor Full Stack com 5 anos de experiência em PHP, JavaScript e bancos de dados SQL. Especialista em arquitetura escalável e otimização de performance. Focado em entregar soluções de alta qualidade com código limpo.",
                        "Engenheiro de Software apaixonado por resolver problemas complexos através da tecnologia. Experiência sólida com Docker, CI/CD e ecossistemas Cloud. Ávido por aprendizado contínuo e metodologias ágeis.",
                        "Desenvolvedor Backend com foco em segurança e performance de APIs. Sólidos conhecimentos em ecossistemas de microsserviços e integração de sistemas legados. Comprometido com a excelência técnica.",
                        "Especialista em TI focado em transformação digital e automação de processos. Experiência em liderar equipes multidisciplinares e implementar infraestrutura ágil e resiliente.",
                        "Desenvolvedor Frontend experiente na criação de interfaces modernas e responsivas. Especialista em UX/UI com foco na melhor experiência do usuário final utilizando as tecnologias mais recentes do mercado.",
                        "Arquiteto de Soluções com vasta experiência em ambientes corporativos de alta disponibilidade. Focado em alinhar tecnologia aos objetivos de negócio e reduzir custos através de automação.",
                        "Desenvolvedor Mobile sênior com foco em performance e experiência do usuário (iOS/Android). Especialista em React Native e Flutter, com sólida base em desenvolvimento nativo.",
                        "Engenheiro de Dados com experiência em Big Data, ETL e Data Lakes. Especialista em Python e Spark, focado em transformar dados brutos em inteligência acionável para a empresa.",
                        "Líder Técnico com perfil comunicativo e foco em resultados. Especialista em gestão de talentos, revisão de código e garantia de entrega seguindo os prazos do roadmap.",
                        "Desenvolvedor Júnior proativo com grande capacidade de aprendizado. Conhecimentos sólidos em lógica de programação, versionamento com Git e bases de desenvolvimento web."
                    ],
                    experience: [
                        "Desenvolvimento e manutenção de sistemas web complexos utilizando PHP e JavaScript. Otimização de consultas SQL reduzindo em 30% o tempo de resposta das aplicações principais.",
                        "Implementação de arquitetura de microsserviços escalável utilizando Docker e Kubernetes. Responsável pela automação do pipeline de deployment (CI/CD).",
                        "Liderança técnica de equipe de 5 desenvolvedores em projetos críticos. Revisão de código e mentoria de novos talentos para manter altos padrões de qualidade.",
                        "Integração de APIs de terceiros e sistemas de pagamento de alta segurança. Redução de falhas críticas em 25% através de implementação de testes automatizados.",
                        "Análise e levantamento de requisitos técnicos junto aos stakeholders. Documentação completa de infraestrutura e padrões de desenvolvimento da empresa.",
                        "Migração de sistemas legados para arquitetura em nuvem (AWS/Azure). Melhoria na escalabilidade do sistema e redução de latência em 40%.",
                        "Desenvolvimento de novas funcionalidades focadas na retenção de usuários. Implementação de testes A/B que resultaram em 15% de aumento no engajamento.",
                        "Resolução de bugs complexos e gargalos de performance em ambiente de produção. Monitoramento contínuo de recursos e saúde das aplicações.",
                        "Colaboração em equipe multidisciplinar seguindo cerimônias do Scrum. Foco em transparência, adaptabilidade e entregas incrementais de valor.",
                        "Criação de componentes reutilizáveis para agilizar o desenvolvimento frontend. Padronização do design system da empresa em todos os produtos web."
                    ],
                    skills: [
                        "PHP 8, Laravel, JavaScript (ES6+), Vue.js, React, Node.js, TypeScript",
                        "PostgreSQL, MySQL, Redis, MongoDB, Eloquent ORM, Query Optimization",
                        "Docker, Kubernetes, AWS, Google Cloud, CI/CD Pipelines, Git, Linux",
                        "Arquitetura de Microsserviços, Design Patterns, SOLID, Clean Code, APIs RESTful",
                        "Metodologias Ágeis (Scrum/Kanban), Jira, TDD, Unit Testing, Pair Programming",
                        "Segurança da Informação, Autenticação JWT/OAuth, Criptografia, OWASP Top 10",
                        "GraphQL, WebSockets, Python, Go, C#, .NET Core, Micro-frontends",
                        "UX/UI Design, Figma, CSS3 (BEM/SASS), Tailwind CSS, Acessibilidade Web",
                        "Big Data, Spark, Kafka, Airflow, Data Warehousing, Power BI, SQL Avançado",
                        "Mobile App Dev, React Native, Flutter, Swift, Kotlin, App Store Submission"
                    ]
                },
                health: {
                    summary: [
                        "Enfermeiro graduado com sólida experiência em Pronto Atendimento e UTI. Especialista em cuidados paliativos e assistência humanizada. Focado na segurança do paciente e gestão de equipes de enfermagem.",
                        "Técnico em Enfermagem dedicado com 4 anos de experiência em clinica médica e pediátrica. Excelência em procedimentos técnicos e suporte ao paciente com foco em acolhimento e empatia.",
                        "Enfermeiro Obstetra comprometido com a assistência segura ao parto e pós-parto. Vasta experiência em manejo de emergências obstétricas e orientação qualificada à família.",
                        "Profissional de saúde focado em gestão hospitalar e controle de infecção. Experiência em implementação de protocolos de segurança e treinamento de equipes assistenciais.",
                        "Enfermeiro generalista com experiência em ESF (Estratégia Saúde da Família). Especialista em saúde pública e promoção de cuidados preventivos na comunidade.",
                        "Enfermeiro de Emergência com foco em atendimento pré-hospitalar e traumas. Experiência em situações de alta pressão e tomada de decisão ágil e segura.",
                        "Especialista em Enfermagem do Trabalho, focado na saúde ocupacional e prevenção de acidentes. Monitoramento contínuo do bem-estar dos colaboradores.",
                        "Enfermeiro Nefrologista com vasta experiência em hemodiálise e diálise peritoneal. Focado no cuidado integral ao paciente renal crônico e em estado crítico.",
                        "Coordenador de Enfermagem com foco em auditoria e qualidade. Especialista em gestão de custos hospitalares e otimização de processos assistenciais.",
                        "Enfermeiro recém-formado com excelente base teórica e proatividade. Focado em iniciar carreira com dedicação total ao aprendizado prático e bem-estar do paciente."
                    ],
                    experience: [
                        "Assistência direta a pacientes em estado crítico na Unidade de Terapia Intensiva. Monitoramento contínuo e execução de protocolos de emergência com precisão.",
                        "Gerenciamento de escala de funcionários e controle de materiais assistenciais. Responsável pelo treinamento mensal da equipe técnica sobre novos protocolos.",
                        "Realização de triagem de pacientes em Pronto-Socorro de alta complexidade. Classificação de risco e suporte imediato em casos de trauma e paradas cardiorrespiratórias.",
                        "Administração rigorosa de medicamentos e cuidados pós-operatórios complexos. Registro detalhado de evolução clínica e interface com equipe médica multidisciplinar.",
                        "Acompanhamento e suporte em partos de baixo e alto risco. Realização de exames físicos e assistência integral ao recém-nascido e puérpera.",
                        "Planejamento e execução de campanhas de saúde preventiva na comunidade. Coleta de exames citopatológicos e acompanhamento de pré-natal de baixo risco.",
                        "Auditoria de prontuários médicos para garantir a conformidade com as normas do COFEN/COREn. Melhoria na qualidade do registro e faturamento hospitalar.",
                        "Assistência domiciliar (Home Care) a pacientes com necessidades complexas. Instalação e manutenção de dispositivos invasivos como GTT, SNE e traqueostomia.",
                        "Supervisão de estágio curricular para graduandos de enfermagem. Orientação técnica e ética em ambiente hospitalar de grande porte.",
                        "Controle rigoroso de infecção hospitalar através da CCIH. Implementação de novas rotinas de higienização e monitoramento de resistência bacteriana."
                    ],
                    skills: [
                        "Punção Venosa, Administração de Medicamentos, Curativos Complexos, Monitorização Hemodinâmica, Reanimação Cardiorrespiratória",
                        "Assistência Humanizada, Gestão de Equipes, Protocolos de Segurança do Paciente, SAE (Sistematização da Assistência de Enfermagem)",
                        "Triagem e Classificação de Risco, Manejo de Emergências, Cuidados Críticos (UTI), Gasometria Arterial, Ventilação Mecânica",
                        "Saúde Coletiva, Imunização, Visita Domiciliar, Educação em Saúde, Prevenção de Doenças, Controle de Infecção Hospitalar",
                        "Acesso Central, Sondagem Vesical e Nasogástrica, Coleta de Exames, ECG, Suporte Básico e Avançado de Vida (BLS/ACLS)",
                        "Auditoria Hospitalar, Gestão por Indicadores, Segurança do Paciente, Qualidade em Saúde, Acreditação ONA/JCI",
                        "Maternidade e Obstetrícia, Aleitamento Materno, Cuidados Imediatos ao RN, Parto Humanizado, Primeiros Socorros",
                        "Enfermagem do Trabalho, Ergonomia, NR-32, Exames Ocupacionais, Primeiros Socorros Empresariais, PCMSO",
                        "Nefrologia, Hemodiálise, Diálise Peritoneal, Acessos Vasculares, Manejo de Equipamentos de Alta Complexidade",
                        "Informática em Saúde, prontuário eletrônico (TASY/MV), Inglês Técnico, Liderança de Equipes Multidisciplinares"
                    ]
                }
            };

            function openSuggestions(type, btn) {
                if (type === 'skills') {
                    currentTargetField = document.querySelector('input[name="skills"]');
                } else {
                    currentTargetField = btn.closest('.suggestion-header').nextElementSibling || btn.closest('.dynamic-field').querySelector('textarea');
                    if (btn.closest('.dynamic-field')) {
                        currentTargetField = btn.parentElement.nextElementSibling;
                    }
                }

                const list = document.getElementById('suggestionsList');
                list.innerHTML = '';

                const selectedNiche = niche || 'tech'; // Default to tech if niche not set
                const items = suggestionsData[selectedNiche] ? suggestionsData[selectedNiche][type] : suggestionsData['tech'][type];

                items.forEach(text => {
                    const div = document.createElement('div');
                    div.className = 'suggestion-item';
                    div.innerText = text;
                    div.onclick = () => {
                        currentTargetField.value = text;
                        closeSuggestions();
                        triggerUpdate(); // Sync preview immediately
                    };
                    list.appendChild(div);
                });

                document.getElementById('suggestionModal').style.display = 'flex';
            }

            function closeSuggestions() {
                document.getElementById('suggestionModal').style.display = 'none';
            }

            let expCount = <?php echo count($experiencesData); ?>;
            let eduCount = <?php echo count($educationData); ?>;

            function selectTemplate(id, el) {
                // Update hidden input
                document.getElementById('templateInput').value = id;

                // Update UI state
                document.querySelectorAll('.template-card').forEach(card => card.classList.remove('selected'));
                el.classList.add('selected');

                // Sync preview immediately
                triggerUpdate();
            }

            function nextStep(step) {
                const currentStepEl = document.querySelector('.form-step.active');
                const currentStepNum = parseInt(currentStepEl.id.replace('step', ''));

                // Basic validation for required fields in the current step
                const inputs = currentStepEl.querySelectorAll('input[required], textarea[required], select[required]');
                let valid = true;
                inputs.forEach(input => {
                    if (!input.value) {
                        input.style.borderColor = '#ef4444';
                        valid = false;
                    } else {
                        input.style.borderColor = 'rgba(255, 255, 255, 0.1)';
                    }
                });

                if (!valid && step > currentStepNum) {
                    alert('Por favor, preencha todos os campos obrigatórios.');
                    return;
                }

                showStep(step);
                window.scrollTo({ top: 0, behavior: 'smooth' });
                triggerUpdate();
            }

            function showStep(step) {
                document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
                document.getElementById('step' + step).classList.add('active');

                document.querySelectorAll('.step-dot').forEach(d => {
                    if (parseInt(d.dataset.step) <= step) d.classList.add('active');
                    else d.classList.remove('active');
                });
            }

            function addExperience() {
                const container = document.getElementById('experienceContainer');
                const html = `
            <div class="dynamic-field">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div class="drag-handle">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 8h16M4 16h16" />
                        </svg>
                    </div>
                    <button type="button" class="btn-remove" onclick="removeField(this)">Remover</button>
                </div>
                <label>Empresa</label>
                <input type="text" name="experience[${expCount}][company]">
                <span class="field-tip">Nome da empresa ou hospital onde trabalhou.</span>

                <label>Cargo</label>
                <input type="text" name="experience[${expCount}][position]">
                <span class="field-tip">Seu título oficial (Ex: Enfermeiro Obstetra, Desenvolvedor Backend).</span>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label>Início</label>
                        <input type="text" name="experience[${expCount}][start_date]" class="date-mask" placeholder="MM/AAAA" maxlength="7">
                    </div>
                    <div>
                        <label>Fim</label>
                        <input type="text" name="experience[${expCount}][end_date]" class="date-mask" placeholder="Atual" maxlength="7">
                    </div>
                </div>
                <span class="field-tip">Use o formato Mês/Ano (Ex: 05/2020). Se ainda trabalhar lá, escreva "Atual" no campo Fim.</span>

                <div class="suggestion-header">
                    <label style="margin-bottom: 0;">Descrição</label>
                    <button type="button" class="btn-suggestion" onclick="openSuggestions('experience', this)">✨ Ver Sugestões</button>
                </div>
                <textarea name="experience[${expCount}][description]" rows="3"></textarea>
                <span class="field-tip">O que você fazia no dia a dia? Cite 2 ou 3 tarefas importantes.</span>
            </div>
        `;
                container.insertAdjacentHTML('beforeend', html);
                expCount++;
                applyDateMasks();
            }

            function addEducation() {
                const container = document.getElementById('educationContainer');
                const html = `
            <div class="dynamic-field">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div class="drag-handle">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 8h16M4 16h16" />
                        </svg>
                    </div>
                    <button type="button" class="btn-remove" onclick="removeField(this)">Remover</button>
                </div>
                <label>Instituição</label>
                <input type="text" name="education[${eduCount}][institution]" required>
                <span class="field-tip">Escola, Faculdade ou Centro Tecnológico.</span>

                <label>Curso/Grau</label>
                <input type="text" name="education[${eduCount}][degree]" required>
                <span class="field-tip">Ex: Graduação, Técnico, MBA, Ensino Médio...</span>

                <label>Área de Estudo</label>
                <input type="text" name="education[${eduCount}][field_of_study]" required>
                <span class="field-tip">Ex: Enfermagem, Ciência da Computação, Administração...</span>

                <label>Conclusão</label>
                <input type="text" name="education[${eduCount}][graduation_date]" class="date-mask" placeholder="MM/AAAA" maxlength="7" required>
                <span class="field-tip">Data em que se formou ou previsão de formatura.</span>
            </div>
        `;
                container.insertAdjacentHTML('beforeend', html);
                eduCount++;
                applyDateMasks();
            }

            function removeField(btn) {
                btn.closest('.dynamic-field').remove();
            }

            // Phone Mask
            const phoneInput = document.getElementById('phone');
            phoneInput.addEventListener('input', (e) => {
                let x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
                e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
            });

            // Date Mask (MM/AAAA)
            function applyDateMasks() {
                document.querySelectorAll('.date-mask').forEach(input => {
                    input.addEventListener('input', (e) => {
                        let v = e.target.value.replace(/\D/g, '');
                        if (v.length > 2) {
                            v = v.substring(0, 2) + '/' + v.substring(2, 6);
                        }
                        e.target.value = v;
                    });
                });
            }

            // Live Preview Logic
            const form = document.getElementById('resumeForm');
            const previewIframe = document.getElementById('previewIframe');
            let abortController = null;

            function updatePreview() {
                if (abortController) abortController.abort();
                abortController = new AbortController();

                const formData = new FormData(form);
                fetch('live-preview-api.php?iframe=1', {
                    method: 'POST',
                    body: formData,
                    signal: abortController.signal
                })
                    .then(response => response.text())
                    .then(html => {
                        const doc = previewIframe.contentDocument || previewIframe.contentWindow.document;
                        const parser = new DOMParser();
                        const newDoc = parser.parseFromString(html, 'text/html');

                        if (!doc.body || !doc.head.innerHTML.trim()) {
                            // First load
                            doc.open();
                            doc.write(html);
                            doc.close();
                        } else {
                            // Update body
                            doc.body.innerHTML = newDoc.body.innerHTML;
                            doc.body.className = newDoc.body.className;
                            doc.body.style.cssText = newDoc.body.style.cssText;

                            // Update styles
                            const newStyle = newDoc.querySelector('style');
                            const oldStyle = doc.querySelector('style');
                            if (newStyle && oldStyle) {
                                oldStyle.textContent = newStyle.textContent;
                            }

                            // Update title if needed
                            if (newDoc.title) doc.title = newDoc.title;
                        }
                    })
                    .catch(err => {
                        if (err.name === 'AbortError') return;
                        console.error('Preview error:', err);
                    });
            }

            // Debounce preview updates
            let timeout = null;
            const triggerUpdate = () => {
                clearTimeout(timeout);
                timeout = setTimeout(updatePreview, 500);
            };

            form.addEventListener('input', triggerUpdate);
            form.addEventListener('change', triggerUpdate);

            // Initial preview
            setTimeout(updatePreview, 300);

            // SortableJS initialization
            function initSortable(id) {
                new Sortable(document.getElementById(id), {
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'sortable-ghost',
                    onEnd: updatePreview
                });
            }

            initSortable('experienceContainer');
            initSortable('educationContainer');

            // Photo Preview
            document.getElementById('photoInput').addEventListener('change', function (e) {
                const file = e.target.files[0];
                const btn = document.getElementById('uploadBtn');
                const btnText = btn.querySelector('span');

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        const img = new Image();
                        img.onload = function () {
                            const canvas = document.createElement('canvas');
                            const MAX_WIDTH = 400; // Sufficient for resume photo
                            const scale = MAX_WIDTH / img.width;
                            canvas.width = MAX_WIDTH;
                            canvas.height = img.height * scale;

                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                            const base64 = canvas.toDataURL('image/jpeg', 0.7);
                            document.getElementById('previewImg').src = base64;
                            document.getElementById('photoBase64').value = base64;
                            document.getElementById('photoPreview').style.display = 'block';
                            btnText.textContent = 'Foto selecionada: ' + file.name;
                            btn.style.borderColor = 'var(--primary)';
                            triggerUpdate();
                        };
                        img.src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });

            applyDateMasks();
        </script>
    </div> <!-- .container -->

</body>

</html>