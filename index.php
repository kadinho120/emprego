<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Auth;
Auth::requireLogin();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Currículos de Alta Conversão</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent: #10b981;
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
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        header {
            padding: 2rem 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-weight: 800;
            font-size: 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero {
            padding: 8rem 0 4rem;
            text-align: center;
            position: relative;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 20%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
            filter: blur(40px);
            z-index: -1;
        }

        h1 {
            font-size: 4rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            letter-spacing: -0.02em;
        }

        h1 span {
            display: block;
            background: linear-gradient(135deg, var(--primary), #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto 3rem;
        }

        .cta-btn {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
            font-size: 1.1rem;
        }

        .cta-btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.4);
        }

        .features {
            padding: 6rem 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background-color: var(--card-bg);
            padding: 2.5rem;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            border-color: rgba(99, 102, 241, 0.3);
            transform: translateY(-5px);
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: var(--text-muted);
        }

        /* Glassmorphism Navbar */
        .glass-nav {
            position: fixed;
            top: 1rem;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 1000px;
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 100px;
            padding: 0.75rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(8px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal {
            background: var(--card-bg);
            padding: 3rem;
            border-radius: 32px;
            max-width: 600px;
            width: 90%;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .modal h2 {
            font-size: 2rem;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #fff, var(--text-muted));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .niche-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .niche-card {
            background: rgba(255, 255, 255, 0.03);
            border: 2px solid rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .niche-card:hover {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.1);
            transform: translateY(-5px);
        }

        .niche-icon {
            font-size: 3rem;
        }

        .niche-name {
            font-weight: 700;
            font-size: 1.2rem;
        }

        .niche-card.tech:hover {
            border-color: #2dd4bf;
            background: rgba(45, 212, 191, 0.1);
        }

        .niche-card.health:hover {
            border-color: #10b981;
            background: rgba(16, 185, 129, 0.1);
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 2.5rem;
            }

            .hero {
                padding: 6rem 0 3rem;
            }
        }
    </style>
</head>

<body>

    <nav class="glass-nav">
        <div class="logo">ApproveMax</div>
        <div style="display: flex; gap: 1.5rem; align-items: center;">
            <a href="dashboard.php" style="color: var(--text-main); text-decoration: none; font-size: 0.9rem; font-weight: 600;">Meus Currículos</a>
            <a href="logout.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; font-weight: 600;">Sair</a>
        </div>
    </nav>

    <main class="container">
        <section class="hero">
            <h1>O Currículo Perfeito em <span>Apenas uma Página.</span></h1>
            <p>Seu centro de comando para currículos de alta conversão.</p>
            <div style="display: flex; gap: 1.5rem; justify-content: center; margin-top: 2rem;">
                <button onclick="openNicheModal()" class="cta-btn" style="border: none; cursor: pointer;">Criar Novo Currículo</button>
                <a href="dashboard.php" class="cta-btn" style="border: none; cursor: pointer; background: rgba(255, 255, 255, 0.1); text-decoration: none; display: inline-flex; align-items: center;">Ver Meus Currículos</a>
            </div>
        </section>

        <section class="features">
            <div class="feature-card">
                <h3>Auto-Fit Inteligente</h3>
                <p>Nosso algoritmo reduz automaticamente o tamanho da fonte e espaçamento para garantir que seu conteúdo
                    caiba em uma única página A4.</p>
            </div>
            <div class="feature-card">
                <h3>Modelos Premium</h3>
                <p>Designs modernos, limpos e otimizados para sistemas de triagem (ATS), focados em resultados reais.
                </p>
            </div>
            <div class="feature-card">
                <h3>Foco em Conversão</h3>
                <p>Estrutura pensada para destacar suas melhores experiências e habilidades de forma estratégica.</p>
            </div>
        </section>
    </main>

    <!-- Niche Modal -->
    <div id="nicheModal" class="modal-overlay" onclick="closeNicheModal(event)">
        <div class="modal" onclick="event.stopPropagation()">
            <h2>Escolha sua área de atuação</h2>
            <div class="niche-options">
                <a href="generator.php?niche=tech" class="niche-card tech">
                    <span class="niche-icon">💻</span>
                    <span class="niche-name">TI & Tecnologia</span>
                </a>
                <a href="generator.php?niche=health" class="niche-card health">
                    <span class="niche-icon">🩺</span>
                    <span class="niche-name">Saúde & Enfermagem</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        function openNicheModal() {
            document.getElementById('nicheModal').style.display = 'flex';
        }

        function closeNicheModal(e) {
            document.getElementById('nicheModal').style.display = 'none';
        }
    </script>
</body>

</html>