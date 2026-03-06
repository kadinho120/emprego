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
                <div class="step-dot" data-step="5">5</div>
            </div>

            <form id="resumeForm" action="process.php" method="POST" enctype="multipart/form-data">
                <!-- Passo 1: Dados Pessoais -->
                <div class="form-step active" id="step1">
                    <h2 style="margin-bottom: 1.5rem;">Dados Pessoais</h2>
                    <label>Nome Completo</label>
                    <input type="text" name="full_name" required placeholder="João Silva">
                    <span class="field-tip">Use seu nome completo e oficial. Evite apelidos.</span>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label>E-mail</label>
                            <input type="email" name="email" required placeholder="joao@email.com">
                            <span class="field-tip">Use um e-mail profissional e que você acesse sempre.</span>
                        </div>
                        <div>
                            <label>Telefone</label>
                            <input type="text" name="phone" id="phone" placeholder="(11) 99999-9999" maxlength="15"
                                required>
                            <span class="field-tip">Coloque seu número principal com o DDD.</span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                        <div>
                            <label>Cidade</label>
                            <input type="text" name="city" placeholder="São Paulo" required>
                            <span class="field-tip">Onde você mora atualmente?</span>
                        </div>
                        <div>
                            <label>Estado (UF)</label>
                            <input type="text" name="state" placeholder="SP" required>
                            <span class="field-tip">Ex: SP, RJ, ES...</span>
                        </div>
                    </div>

                    <label>Resumo Profissional</label>
                    <textarea name="summary" rows="4" placeholder="Fale um pouco sobre sua carreira..."
                        required></textarea>
                    <span class="field-tip">Escreva 3 ou 4 linhas sobre sua carreira e seu maior diferencial. Isso é a
                        primeira coisa que o contratante lê.</span>

                    <div class="btn-group">
                        <div></div>
                        <button type="button" class="btn-next" onclick="nextStep(2)">Próximo</button>
                    </div>
                </div>

                <!-- Passo 2: Foto -->
                <div class="form-step" id="step2">
                    <h2 style="margin-bottom: 1.5rem;">Sua Foto</h2>
                    <label>Foto (Opcional)</label>
                    <input type="file" name="photo" id="photoInput" accept="image/*">
                    <span class="field-tip">A foto será cortada automaticamente para 3:4. Prefira fotos com fundo neutro e boa iluminação.</span>
                    
                    <div id="photoPreview" style="margin-top: 1rem; display: none;">
                        <img id="previewImg" src="" style="width: 150px; height: 200px; object-fit: cover; border-radius: 12px; border: 2px solid var(--primary);">
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn-prev" onclick="nextStep(1)">Anterior</button>
                        <button type="button" class="btn-next" onclick="nextStep(3)">Próximo</button>
                    </div>
                </div>

                <!-- Passo 3: Experiência -->
                <div class="form-step" id="step3">
                    <h2 style="margin-bottom: 1.5rem;">Experiência Profissional</h2>
                    <div id="experienceContainer">
                        <div class="dynamic-field">
                            <label>Empresa</label>
                            <input type="text" name="experience[0][company]">
                            <span class="field-tip">Nome da empresa ou hospital onde trabalhou.</span>

                            <label>Cargo</label>
                            <input type="text" name="experience[0][position]">
                            <span class="field-tip">Seu título oficial (Ex: Enfermeiro Obstetra, Desenvolvedor
                                Backend).</span>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <label>Início</label>
                                    <input type="text" name="experience[0][start_date]" class="date-mask"
                                        placeholder="MM/AAAA" maxlength="7">
                                </div>
                                <div>
                                    <label>Fim</label>
                                    <input type="text" name="experience[0][end_date]" class="date-mask"
                                        placeholder="Atual" maxlength="7">
                                </div>
                            </div>
                            <span class="field-tip">Use o formato Mês/Ano (Ex: 05/2020). Se ainda trabalhar lá, escreva
                                "Atual" no campo Fim.</span>

                            <label>Descrição</label>
                            <textarea name="experience[0][description]" rows="3"></textarea>
                            <span class="field-tip">O que você fazia no dia a dia? Cite 2 ou 3 tarefas importantes.
                                Dica: Use palavras como "Realizei", "Fui responsável por", "Liderei".</span>
                        </div>
                    </div>
                    <button type="button" class="add-more" onclick="addExperience()">+ Adicionar Experiência</button>

                    <div class="btn-group">
                        <button type="button" class="btn-prev" onclick="nextStep(2)">Anterior</button>
                        <button type="button" class="btn-next" onclick="nextStep(4)">Próximo</button>
                    </div>
                </div>

                <!-- Passo 4: Formação -->
                <div class="form-step" id="step4">
                    <h2 style="margin-bottom: 1.5rem;">Educação</h2>
                    <div id="educationContainer">
                        <div class="dynamic-field">
                            <label>Instituição</label>
                            <input type="text" name="education[0][institution]" required>
                            <span class="field-tip">Escola, Faculdade ou Centro Tecnológico.</span>

                            <label>Curso/Grau</label>
                            <input type="text" name="education[0][degree]" required>
                            <span class="field-tip">Ex: Graduação em Enfermagem, Técnico em Redes, MBA em Gestão, Ensino
                                Médio...</span>

                            <label>Conclusão</label>
                            <input type="text" name="education[0][graduation_date]" class="date-mask"
                                placeholder="MM/AAAA" maxlength="7" required>
                            <span class="field-tip">Data em que se formou ou previsão de formatura.</span>
                        </div>
                    </div>
                    <button type="button" class="add-more" onclick="addEducation()">+ Adicionar Formação</button>

                    <div class="btn-group">
                        <button type="button" class="btn-prev" onclick="nextStep(3)">Anterior</button>
                        <button type="button" class="btn-next" onclick="nextStep(5)">Próximo</button>
                    </div>
                </div>

                <!-- Passo 5: Habilidades e Modelo -->
                <div class="form-step" id="step5">
                    <h2 style="margin-bottom: 1.5rem;">Habilidades e Estilo</h2>
                    <label>Habilidades (separadas por vírgula)</label>
                    <input type="text" name="skills" placeholder="PHP, PostgreSQL, Docker, UX Design" required>
                    <span class="field-tip">Liste ferramentas, tecnologias ou competências que você domina. Ex: Excel
                        Avançado, Punção Venosa, Java, Liderança de Equipe...</span>

                    <label>Modelo do Currículo</label>
                    <select name="template_id" required>
                        <option value="tech">TI & Desenvolvimento (Sénior/Fullstack)</option>
                        <option value="health">Saúde (Enfermeiros/Técnicos)</option>
                    </select>
                    <span class="field-tip">Escolha o modelo que mais combina com sua área de atuação.</span>

                    <div class="btn-group">
                        <button type="button" class="btn-prev" onclick="nextStep(4)">Anterior</button>
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
            const currentStepEl = document.querySelector('.form-step.active');
            const currentStepNum = parseInt(currentStepEl.id.replace('step', ''));

            // Só valida se estiver tentando avançar
            if (step > currentStepNum) {
                const inputs = currentStepEl.querySelectorAll('input, select, textarea');
                let valid = true;
                for (let input of inputs) {
                    if (!input.checkValidity()) {
                        input.reportValidity();
                        valid = false;
                        break;
                    }
                }
                if (!valid) return;
            }

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
                <button type="button" class="btn-remove" onclick="removeField(this)">Remover</button>
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

                <label>Descrição</label>
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
                <button type="button" class="btn-remove" onclick="removeField(this)">Remover</button>
                <label>Instituição</label>
                <input type="text" name="education[${eduCount}][institution]" required>
                <span class="field-tip">Escola, Faculdade ou Centro Tecnológico.</span>

                <label>Curso/Grau</label>
                <input type="text" name="education[${eduCount}][degree]" required>
                <span class="field-tip">Ex: Graduação em Enfermagem, Técnico em Redes, MBA em Gestão, Ensino Médio...</span>

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

        // Photo Preview
        document.getElementById('photoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('photoPreview').style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        applyDateMasks();
    </script>

</body>

</html>