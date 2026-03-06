<?php
require_once __DIR__ . '/vendor/autoload.php';
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
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1.5rem;
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
            color: var(--accent);
            border: 1px solid rgba(16, 185, 129, 0.2);
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="form-card">
            <div class="step-indicator">
                <div class="step-dot active" data-step="1">1</div>
                <div class="step-dot" data-step="2">2</div>
                <div class="step-dot" data-step="3">3</div>
                <div class="step-dot" data-step="4">4</div>
            </div>

            <form id="resumeForm" action="process.php" method="POST">
                <!-- Passo 1: Dados Pessoais -->
                <div class="form-step active" id="step1">
                    <h2 style="margin-bottom: 1.5rem;">Dados Pessoais</h2>
                    <label>Nome Completo</label>
                    <input type="text" name="full_name" required placeholder="João Silva">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label>E-mail</label>
                            <input type="email" name="email" required placeholder="joao@email.com">
                        </div>
                        <div>
                        <label>Telefone</label>
                        <input type="text" name="phone" id="phone" placeholder="(11) 99999-9999" maxlength="15">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                    <div>
                        <label>Cidade</label>
                        <input type="text" name="city" placeholder="São Paulo">
                    </div>
                    <div>
                        <label>Estado (UF)</label>
                        <input type="text" name="state" placeholder="SP">
                    </div>
                </div>

                    <label>Resumo Profissional</label>
                    <textarea name="summary" rows="4" placeholder="Fale um pouco sobre sua carreira..."></textarea>

                    <div class="btn-group">
                        <div></div>
                        <button type="button" class="btn-next" onclick="nextStep(2)">Próximo</button>
                    </div>
                </div>

                <!-- Passo 2: Experiência -->
                <div class="form-step" id="step2">
                    <h2 style="margin-bottom: 1.5rem;">Experiência Profissional</h2>
                    <div id="experienceContainer">
                        <div class="dynamic-field">
                            <label>Empresa</label>
                            <input type="text" name="experience[0][company]">
                            <label>Cargo</label>
                            <input type="text" name="experience[0][position]">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <label>Início</label>
                                    <input type="text" name="experience[0][start_date]" class="date-mask" placeholder="MM/AAAA" maxlength="7">
                                </div>
                                <div>
                                    <label>Fim</label>
                                    <input type="text" name="experience[0][end_date]" class="date-mask" placeholder="Atual" maxlength="7">
                                </div>
                            </div>
                            <label>Descrição</label>
                            <textarea name="experience[0][description]" rows="3"></textarea>
                        </div>
                    </div>
                    <button type="button" class="add-more" onclick="addExperience()">+ Adicionar Experiência</button>

                    <div class="btn-group">
                        <button type="button" class="btn-prev" onclick="nextStep(1)">Anterior</button>
                        <button type="button" class="btn-next" onclick="nextStep(3)">Próximo</button>
                    </div>
                </div>

                <!-- Passo 3: Formação -->
                <div class="form-step" id="step3">
                    <h2 style="margin-bottom: 1.5rem;">Educação</h2>
                    <div id="educationContainer">
                        <div class="dynamic-field">
                            <label>Instituição</label>
                            <input type="text" name="education[0][institution]">
                            <label>Curso/Grau</label>
                            <input type="text" name="education[0][degree]">
                            <label>Conclusão</label>
                            <input type="text" name="education[0][graduation_date]" class="date-mask" placeholder="MM/AAAA" maxlength="7">
                        </div>
                    </div>
                    <button type="button" class="add-more" onclick="addEducation()">+ Adicionar Formação</button>

                    <div class="btn-group">
                        <button type="button" class="btn-prev" onclick="nextStep(2)">Anterior</button>
                        <button type="button" class="btn-next" onclick="nextStep(4)">Próximo</button>
                    </div>
                </div>

                <!-- Passo 4: Habilidades e Modelo -->
                <div class="form-step" id="step4">
                    <h2 style="margin-bottom: 1.5rem;">Habilidades e Estilo</h2>
                    <label>Habilidades (separadas por vírgula)</label>
                    <input type="text" name="skills" placeholder="PHP, PostgreSQL, Docker, UX Design">

                    <label>Modelo do Currículo</label>
                    <select name="template_id">
                        <option value="modern">Moderno e Limpo</option>
                        <option value="corporate">Corporativo Tradicional</option>
                        <option value="minimal">Minimalista</option>
                        <option value="health">Saude (Enfermeiros/Tecnicos)</option>
                    </select>

                    <div class="btn-group">
                        <button type="button" class="btn-prev" onclick="nextStep(3)">Anterior</button>
                        <button type="submit" class="btn-submit">Gerar Currículo</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        let expCount = 1;
        let eduCount = 1;

        function nextStep(step) {
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
                <label>Empresa</label>
                <input type="text" name="experience[${expCount}][company]">
                <label>Cargo</label>
                <input type="text" name="experience[${expCount}][position]">
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
                <label>Descrição</label>
                <textarea name="experience[${expCount}][description]" rows="3"></textarea>
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
                <label>Instituição</label>
                <input type="text" name="education[${eduCount}][institution]">
                <label>Curso/Grau</label>
                <input type="text" name="education[${eduCount}][degree]">
                <label>Conclusão</label>
                <input type="text" name="education[${eduCount}][graduation_date]" class="date-mask" placeholder="MM/AAAA" maxlength="7">
            </div>
        `;
            container.insertAdjacentHTML('beforeend', html);
            eduCount++;
            applyDateMasks();
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

        applyDateMasks();
    </script>

</body>

</html>