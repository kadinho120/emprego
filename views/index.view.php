<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApproveMax - Gerador de Currículos de Alta Conversão</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/index.css">
</head>

<body>

    <nav class="glass-nav">
        <div class="logo">ApproveMax</div>
        <div style="display: flex; gap: 1.5rem; align-items: center;">
            <a href="dashboard.php"
                style="color: var(--text-main); text-decoration: none; font-size: 0.9rem; font-weight: 600;">Meus
                Currículos</a>
            <a href="logout.php"
                style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; font-weight: 600;">Sair</a>
        </div>
    </nav>

    <div class="container">
        <section class="hero">
            <h1>O primeiro gerador de currículos <span>focado 100% em conversão.</span></h1>
            <p>Não apenas um PDF, mas uma ferramenta estratégica desenhada para passar em qualquer software de RH e
                impressionar recrutadores.</p>
            <a href="javascript:void(0)" onclick="openNicheModal()" class="cta-btn">Criar Currículo Agora</a>
        </section>

        <section class="features">
            <div class="feature-card">
                <h3>🚀 Foco em ATS</h3>
                <p>Nossos layouts são otimizados para serem lidos perfeitamente por robôs de seleção (Applicant Tracking
                    Systems).</p>
            </div>
            <div class="feature-card">
                <h3>💎 Design Premium</h3>
                <p>Templates modernos e elegantes que transmitem autoridade e profissionalismo instantaneamente.</p>
            </div>
            <div class="feature-card">
                <h3>⚡ Inteligência Sugestiva</h3>
                <p>Receba sugestões de textos profissionais baseadas no seu nicho para não perder tempo escrevendo.</p>
            </div>
        </section>
    </div>

    <!-- Modal de Seleção de Nicho -->
    <div id="nicheModal" class="modal-overlay">
        <div class="modal">
            <h2>Quase lá!</h2>
            <p>Para te dar as melhores sugestões e modelos, qual sua área de atuação?</p>

            <div class="niche-options">
                <a href="generator.php?niche=tech" class="niche-card">
                    <span class="niche-icon">💻</span>
                    <h4>Tecnologia / TI</h4>
                    <span>Devs, UX, Dados, Gestão...</span>
                </a>
                <a href="generator.php?niche=health" class="niche-card">
                    <span class="niche-icon">🏥</span>
                    <h4>Saúde</h4>
                    <span>Enfermeiros, Técnicos, Gestão...</span>
                </a>
            </div>

            <button onclick="closeNicheModal()"
                style="margin-top: 2rem; background: transparent; border: none; color: var(--text-muted); cursor: pointer; text-decoration: underline;">Voltar</button>
        </div>
    </div>

    <script src="public/assets/js/index.js"></script>
</body>

</html>