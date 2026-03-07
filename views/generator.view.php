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
    <link rel="stylesheet" href="public/assets/css/generator.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>

<body>

    <div class="container">
        <div class="form-wrapper">
            <div style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem; padding: 0 1rem;">
                <?php if (App\Auth::isLoggedIn()): ?>
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
                    <input type="hidden" name="niche" id="nicheInput"
                        value="<?php echo htmlspecialchars($currentNiche); ?>">
                    <?php if ($resumeData): ?>
                        <input type="hidden" name="resume_id" value="<?php echo $resumeData['id']; ?>">
                    <?php endif; ?>
                    <!-- Passo 1: Seleção de Modelo -->
                    <div class="form-step active" id="step1">
                        <h2 style="margin-bottom: 0.5rem;">Escolha seu Modelo</h2>
                        <p style="color: var(--text-muted); margin-bottom: 2rem; font-size: 0.9rem;">Selecione o layout
                            que mais combina com seu perfil profissional.</p>

                        <div class="niche-selector-tabs" style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
                            <button type="button"
                                class="niche-tab <?php echo $currentNiche === 'tech' ? 'active' : ''; ?>"
                                data-niche="tech" onclick="switchNiche('tech')">💻 TI & Tecnologia</button>
                            <button type="button"
                                class="niche-tab <?php echo $currentNiche === 'health' ? 'active' : ''; ?>"
                                data-niche="health" onclick="switchNiche('health')">🩺 Saúde & Enfermagem</button>
                        </div>

                        <input type="hidden" name="template_id" id="templateInput"
                            value="<?php echo htmlspecialchars($initialTemplate); ?>" required>

                        <!-- Templates Tech -->
                        <div id="niche-tech-container"
                            class="niche-content <?php echo $currentNiche === 'tech' ? 'active' : ''; ?>">
                            <div class="template-grid">
                                <div class="template-card <?php echo $initialTemplate === 'tech' ? 'selected' : ''; ?>"
                                    onclick="selectTemplate('tech', this, 'tech')">
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
                                <div class="template-card <?php echo $initialTemplate === 'tech_modern' ? 'selected' : ''; ?>"
                                    onclick="selectTemplate('tech_modern', this, 'tech')">
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
                                <div class="template-card <?php echo $initialTemplate === 'tech_minimal' ? 'selected' : ''; ?>"
                                    onclick="selectTemplate('tech_minimal', this, 'tech')">
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
                            </div>
                        </div>

                        <!-- Templates Health -->
                        <div id="niche-health-container"
                            class="niche-content <?php echo $currentNiche === 'health' ? 'active' : ''; ?>">
                            <div class="template-grid">
                                <div class="template-card <?php echo $initialTemplate === 'health' ? 'selected' : ''; ?>"
                                    onclick="selectTemplate('health', this, 'health')">
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
                                    onclick="selectTemplate('health_professional', this, 'health')">
                                    <div class="selected-badge">✓</div>
                                    <div class="template-preview">
                                        <div style="background: #1e3a8a; width: 100%; height: 100%;"></div>
                                    </div>
                                    <div class="template-info">
                                        <div class="template-name">Healthcare Leader</div>
                                    </div>
                                </div>
                                <div class="template-card <?php echo $initialTemplate === 'health_clean' ? 'selected' : ''; ?>"
                                    onclick="selectTemplate('health_clean', this, 'health')">
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
                            </div>
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
                        <span class="field-tip">Escreva 3 ou 4 lines sobre sua carreira e seu maior diferencial. Isso é
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
                            <button type="button" class="btn-upload" id="uploadBtn" <?php echo ($resumeData && $resumeData['photo_path']) ? 'style="border-color: var(--primary);"' : ''; ?>>
                                📸
                                <span>
                                    <?php echo ($resumeData && $resumeData['photo_path']) ? 'Trocar Foto' : 'Escolher Foto'; ?>
                                </span>
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

                        <?php if (isset($resumeData['slug'])): ?>
                            <div style="margin-top: 1.5rem; text-align: center;">
                                <a href="cv.php?slug=<?php echo $resumeData['slug']; ?>" target="_blank"
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
    </div> <!-- .container -->

    <script>
        const niche = '<?php echo $_GET['niche'] ?? ''; ?>';
        let expCount = <?php echo count($experiencesData ?? []); ?>;
        let eduCount = <?php echo count($educationData ?? []); ?>;
    </script>
    <script src="public/assets/js/generator.js"></script>
</body>

</html>