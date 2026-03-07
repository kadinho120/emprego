<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Currículo - ApproveMax</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer base {
            body {
                @apply bg-[#0f172a] text-slate-200 antialiased overflow-x-hidden;
            }
        }
        @layer components {
            .glass-card {
                @apply bg-slate-800/50 backdrop-blur-xl border border-white/10;
            }
            .form-input {
                @apply w-full bg-slate-900/50 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder:text-slate-600;
            }
            .btn-step {
                @apply px-6 py-3 rounded-xl font-bold transition-all active:scale-[0.98] text-sm;
            }
            .btn-next {
                @apply btn-step bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-500/20;
            }
            .btn-prev {
                @apply btn-step bg-slate-800/50 border border-white/10 hover:bg-slate-700/50 text-slate-200;
            }
            .step-dot {
                @apply w-10 h-10 rounded-full border-2 border-white/10 flex items-center justify-center font-bold text-slate-500 transition-all;
            }
            .step-dot.active {
                @apply border-indigo-500 text-white bg-indigo-500/20 shadow-[0_0_15px_rgba(99,102,241,0.3)];
            }
            .step-dot.completed {
                @apply border-emerald-500 text-emerald-400 bg-emerald-500/10;
            }
        }
        #mobilePreviewTrigger {
            @apply fixed bottom-6 right-6 w-14 h-14 bg-indigo-600 rounded-full flex items-center justify-center text-2xl shadow-2xl z-[60] md:hidden transform transition-all active:scale-90;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>

<body>

    <!-- Floating Preview Trigger (Mobile only) -->
    <button type="button" id="mobilePreviewTrigger">👁️</button>

    <!-- Mobile Preview Modal -->
    <div id="mobilePreviewModal" class="fixed inset-0 z-[100] hidden flex-col bg-slate-950/95 backdrop-blur-md">
        <div class="flex justify-between items-center p-4 border-b border-white/10">
            <h3 class="font-bold text-lg">Prévia em Tempo Real</h3>
            <button type="button" id="closeMobilePreview"
                class="bg-indigo-600 px-4 py-2 rounded-lg text-sm font-bold">Fechar</button>
        </div>
        <div class="flex-grow">
            <iframe id="mobilePreviewIframe" class="w-full h-full border-none"></iframe>
        </div>
    </div>
    <div class="flex flex-col lg:flex-row min-h-screen">
        <!-- Sidebar Form Area -->
        <div
            class="w-full lg:w-[450px] xl:w-[500px] flex-shrink-0 bg-slate-900/30 lg:border-r border-white/5 flex flex-col pt-8">
            <div class="px-6 md:px-10 flex justify-between items-center mb-8">
                <div
                    class="text-xl font-extrabold bg-gradient-to-r from-white to-indigo-400 bg-clip-text text-transparent">
                    ApproveMax</div>
                <div class="flex gap-4 items-center">
                    <a href="dashboard.php"
                        class="text-xs font-bold text-slate-400 hover:text-white transition-colors">Meus Currículos</a>
                    <a href="logout.php"
                        class="text-xs font-bold text-slate-500 hover:text-red-400 transition-colors">Sair</a>
                </div>
            </div>

            <div class="flex-grow px-6 md:px-10 pb-20">
                <div class="flex justify-center gap-2 mb-10 overflow-x-auto pb-2 scrollbar-none">
                    <div class="step-dot active shrink-0" data-step="1">1</div>
                    <div class="step-dot shrink-0" data-step="2">2</div>
                    <div class="step-dot shrink-0" data-step="3">3</div>
                    <div class="step-dot shrink-0" data-step="4">4</div>
                    <div class="step-dot shrink-0" data-step="5">5</div>
                    <div class="step-dot shrink-0" data-step="6">6</div>
                </div>

                <form id="resumeForm" action="process.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="niche" id="nicheInput"
                        value="<?php echo htmlspecialchars($currentNiche); ?>">
                    <?php if ($resumeData): ?>
                        <input type="hidden" name="resume_id" value="<?php echo $resumeData['id']; ?>">
                    <?php endif; ?>
                    <!-- Passo 1: Seleção de Modelo -->
                    <div class="form-step activespace-y-8" id="step1">
                        <div>
                            <h2 class="text-2xl font-bold mb-2">Escolha seu Modelo</h2>
                            <p class="text-sm text-slate-400">Selecione o layout que mais combina com seu perfil
                                profissional.</p>
                        </div>

                        <div class="flex p-1 bg-slate-900/50 rounded-2xl w-fit">
                            <button type="button"
                                class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all <?php echo $currentNiche === 'tech' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'; ?>"
                                onclick="switchNiche('tech')">💻 Tech</button>
                            <button type="button"
                                class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all <?php echo $currentNiche === 'health' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'; ?>"
                                onclick="switchNiche('health')">🩺 Saúde</button>
                        </div>

                        <input type="hidden" name="template_id" id="templateInput"
                            value="<?php echo htmlspecialchars($initialTemplate); ?>" required>

                        <!-- Templates Container -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Template Cards will be visually updated but keep their IDs/logic -->
                            <!-- Tech Templates -->
                            <div class="template-group <?php echo $currentNiche === 'tech' ? '' : 'hidden'; ?>"
                                id="niche-tech-container">
                        <div class="space-y-6">
                            <!-- Niche: Tech -->
                            <div class="template-group <?php echo $niche === 'tech' ? '' : 'hidden'; ?>" id="niche-tech-container">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="group relative glass-card p-3 rounded-2xl cursor-pointer border-2 transition-all overflow-hidden <?php echo $initialTemplate === 'tech' ? 'border-primary ring-2 ring-primary/20' : 'border-white/5'; ?>"
                                        onclick="selectTemplate('tech', this, 'tech')">
                                        <div class="aspect-[4/5] bg-[#0f172a] rounded-xl overflow-hidden p-4 space-y-2 relative">
                                            <div class="bg-indigo-500 h-3 w-2/3 rounded-full"></div>
                                            <div class="bg-slate-800 h-1.5 w-full rounded-full"></div>
                                            <div class="bg-slate-800 h-1.5 w-full rounded-full"></div>
                                            <div class="bg-slate-800 h-1.5 w-4/5 rounded-full"></div>
                                            <div class="absolute inset-0 bg-gradient-to-t from-[#0f172a] to-transparent opacity-40"></div>
                                        </div>
                                        <div class="mt-3 text-center">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider group-hover:text-white transition-colors">TI - Dark Mode</span>
                                        </div>
                                    </div>
                                    
                                    <div class="group relative glass-card p-3 rounded-2xl cursor-pointer border-2 transition-all overflow-hidden <?php echo $initialTemplate === 'tech_modern' ? 'border-primary ring-2 ring-primary/20' : 'border-white/5'; ?>"
                                        onclick="selectTemplate('tech_modern', this, 'tech')">
                                        <div class="aspect-[4/5] bg-indigo-600 rounded-xl overflow-hidden p-4 space-y-2 relative">
                                            <div class="bg-white h-3 w-2/3 rounded-full"></div>
                                            <div class="bg-white/20 h-1.5 w-full rounded-full"></div>
                                            <div class="bg-white/20 h-1.5 w-full rounded-full"></div>
                                            <div class="bg-white/20 h-1.5 w-4/5 rounded-full"></div>
                                            <div class="absolute inset-0 bg-gradient-to-t from-indigo-700 to-transparent opacity-40"></div>
                                        </div>
                                        <div class="mt-3 text-center">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider group-hover:text-white transition-colors">Modern Blue</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Niche: Health -->
                            <div class="template-group <?php echo $niche === 'health' ? '' : 'hidden'; ?>" id="niche-health-container">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="group relative glass-card p-3 rounded-2xl cursor-pointer border-2 transition-all overflow-hidden <?php echo $initialTemplate === 'health' ? 'border-primary ring-2 ring-primary/20' : 'border-white/5'; ?>"
                                        onclick="selectTemplate('health', this, 'health')">
                                        <div class="aspect-[4/5] bg-rose-50 rounded-xl overflow-hidden p-4 space-y-2 relative">
                                            <div class="bg-rose-500 h-3 w-2/3 rounded-full"></div>
                                            <div class="bg-rose-200 h-1.5 w-full rounded-full"></div>
                                            <div class="bg-rose-200 h-1.5 w-full rounded-full"></div>
                                            <div class="bg-rose-200 h-1.5 w-4/5 rounded-full"></div>
                                        </div>
                                        <div class="mt-3 text-center">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider group-hover:text-white transition-colors">Rose Healthcare</span>
                                        </div>
                                    </div>
                                    
                                    <div class="group relative glass-card p-3 rounded-2xl cursor-pointer border-2 transition-all overflow-hidden <?php echo $initialTemplate === 'health_standard' ? 'border-primary ring-2 ring-primary/20' : 'border-white/5'; ?>"
                                        onclick="selectTemplate('health_standard', this, 'health')">
                                        <div class="aspect-[4/5] bg-white rounded-xl overflow-hidden p-4 space-y-2 border border-slate-100 relative">
                                            <div class="bg-emerald-600 h-3 w-2/3 rounded-full"></div>
                                            <div class="bg-slate-100 h-1.5 w-full rounded-full"></div>
                                            <div class="bg-slate-100 h-1.5 w-full rounded-full"></div>
                                            <div class="bg-slate-100 h-1.5 w-4/5 rounded-full"></div>
                                        </div>
                                        <div class="mt-3 text-center">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider group-hover:text-white transition-colors">Standard Hospitalar</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-6">
                            <button type="button" class="btn-next w-full md:w-auto" onclick="nextStep(2)">Começar
                                Preenchimento</button>
                        </div>
                    </div>

                    <!-- Passo 2: Dados Pessoais -->
                    <div class="form-step hidden space-y-6" id="step2">
                        <h2 class="text-2xl font-bold mb-6">Dados Pessoais</h2>

                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-300 ml-1">Nome Completo</label>
                                <input type="text" name="full_name" class="form-input" required placeholder="João Silva"
                                    value="<?php echo htmlspecialchars($resumeData['full_name'] ?? ''); ?>">
                                <p class="text-[11px] text-slate-500 ml-1 italic">Use seu nome completo e oficial.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-300 ml-1">E-mail</label>
                                    <input type="email" name="email" class="form-input" required
                                        placeholder="joao@email.com"
                                        value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-300 ml-1">Telefone</label>
                                    <input type="text" name="phone" id="phone" class="form-input"
                                        placeholder="(11) 99999-9999" maxlength="15" required
                                        value="<?php echo htmlspecialchars($resumeData['phone'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-300 ml-1">Cidade</label>
                                    <input type="text" name="city" class="form-input" placeholder="São Paulo" required
                                        value="<?php echo htmlspecialchars($resumeData['city'] ?? ''); ?>">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-300 ml-1">Estado (UF)</label>
                                    <input type="text" name="state" class="form-input" placeholder="SP" required
                                        value="<?php echo htmlspecialchars($resumeData['state'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex justify-between items-center px-1">
                                    <label class="text-sm font-semibold text-slate-300">Resumo Profissional</label>
                                    <button type="button"
                                        class="text-xs font-bold text-indigo-400 hover:text-white flex items-center gap-1 transition-colors"
                                        onclick="openSuggestions('summary', this)">✨ Sugestões</button>
                                </div>
                                <textarea name="summary" rows="4" class="form-input resize-none"
                                    placeholder="Fale um pouco sobre sua carreira..."
                                    required><?php echo htmlspecialchars($resumeData['summary'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <button type="button" class="btn-prev flex-1" onclick="nextStep(1)">Anterior</button>
                            <button type="button" class="btn-next flex-1" onclick="nextStep(3)">Próximo</button>
                        </div>
                    </div>

                    <!-- Passo 3: Foto -->
                    <div class="form-stephidden space-y-6" id="step3">
                        <h2 class="text-2xl font-bold mb-6">Sua Foto</h2>

                        <div class="space-y-4">
                            <label class="text-sm font-semibold text-slate-300 ml-1">Sua Melhor Foto (Opcional)</label>
                            <div class="relative group">
                                <input type="hidden" name="photo_base64" id="photoBase64">
                                <input type="file" name="photo" id="photoInput" accept="image/*" class="hidden">
                                <button type="button" id="uploadBtn"
                                    class="w-full flex flex-col items-center justify-center gap-4 p-8 glass-card rounded-3xl border-2 border-dashed border-white/10 hover:border-indigo-500/50 hover:bg-indigo-500/5 transition-all group-hover:scale-[1.01]">
                                    <div
                                        class="w-16 h-16 bg-slate-900/50 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                                        📸</div>
                                    <div class="text-center">
                                        <span
                                            class="block font-bold text-slate-200"><?php echo ($resumeData && $resumeData['photo_path']) ? 'Trocar Foto' : 'Escolher Foto'; ?></span>
                                        <span class="text-xs text-slate-500 mt-1 block italic">Dica: Uma foto
                                            profissional aumenta suas chances em 70%.</span>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <input type="hidden" name="photo_path"
                            value="<?php echo htmlspecialchars($resumeData['photo_path'] ?? ''); ?>">

                        <div id="photoPreview"
                            class="<?php echo ($resumeData && $resumeData['photo_path']) ? 'block' : 'hidden'; ?> animate-in fade-in zoom-in duration-300">
                            <div class="flex flex-col items-center gap-3">
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Visualização Atual
                                </p>
                                <img id="previewImg"
                                    src="<?php echo ($resumeData && $resumeData['photo_path']) ? $resumeData['photo_path'] : ''; ?>"
                                    class="w-32 h-40 object-cover rounded-2xl border-2 border-indigo-500 shadow-xl shadow-indigo-500/20">
                            </div>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <button type="button" class="btn-prev flex-1" onclick="nextStep(2)">Anterior</button>
                            <button type="button" class="btn-next flex-1" onclick="nextStep(4)">Próximo</button>
                        </div>
                    </div>

                    <!-- Passo 4: Experiência -->
                    <div class="form-step hidden space-y-6" id="step4">
                        <h2 class="text-2xl font-bold mb-6">Experiência Profissional</h2>
                        <div id="experienceContainer" class="space-y-6">
                            <?php foreach ($experiencesData as $index => $exp): ?>
                                <div
                                    class="dynamic-field glass-card p-6 rounded-3xl space-y-4 group transition-all hover:border-white/20 relative">
                                    <div class="flex justify-between items-center">
                                        <div
                                            class="drag-handle text-slate-500 cursor-move p-1 hover:text-white transition-colors">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M4 8h16M4 16h16" />
                                            </svg>
                                        </div>
                                        <?php if ($index > 0): ?>
                                            <button type="button"
                                                class="text-xs font-bold text-red-400 hover:text-red-300 transition-colors"
                                                onclick="removeField(this)">Remover</button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="space-y-2">
                                            <label
                                                class="text-xs font-bold text-slate-400 ml-1 uppercase tracking-wider">Empresa</label>
                                            <input type="text" name="experience[<?php echo $index; ?>][company]"
                                                class="form-input !py-2.5 !px-3 text-sm" placeholder="Nome da Empresa"
                                                value="<?php echo htmlspecialchars($exp['company'] ?? ''); ?>">
                                        </div>
                                        <div class="space-y-2">
                                            <label
                                                class="text-xs font-bold text-slate-400 ml-1 uppercase tracking-wider">Cargo</label>
                                            <input type="text" name="experience[<?php echo $index; ?>][position]"
                                                class="form-input !py-2.5 !px-3 text-sm" placeholder="Seu Cargo"
                                                value="<?php echo htmlspecialchars($exp['position'] ?? ''); ?>">
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <label
                                                    class="text-xs font-bold text-slate-400 ml-1 uppercase tracking-wider">Início</label>
                                                <input type="text" name="experience[<?php echo $index; ?>][start_date]"
                                                    class="form-input !py-2.5 !text-center date-mask" placeholder="MM/AAAA"
                                                    maxlength="7"
                                                    value="<?php echo htmlspecialchars($exp['start_date'] ?? ''); ?>">
                                            </div>
                                            <div class="space-y-2">
                                                <label
                                                    class="text-xs font-bold text-slate-400 ml-1 uppercase tracking-wider">Fim</label>
                                                <input type="text" name="experience[<?php echo $index; ?>][end_date]"
                                                    class="form-input !py-2.5 !text-center date-mask" placeholder="Atual"
                                                    maxlength="7"
                                                    value="<?php echo htmlspecialchars($exp['end_date'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="flex justify-between items-center px-1">
                                                <label
                                                    class="text-xs font-bold text-slate-400 uppercase tracking-wider">Descrição</label>
                                                <button type="button"
                                                    class="text-[10px] font-bold text-indigo-400 hover:text-white transition-colors"
                                                    onclick="openSuggestions('experience', this)">✨ Ver Sugestões</button>
                                            </div>
                                            <textarea name="experience[<?php echo $index; ?>][description]" rows="3"
                                                class="form-input text-sm resize-none"><?php echo htmlspecialchars($exp['description'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button"
                            class="w-full py-4 rounded-2xl border-2 border-dashed border-white/5 text-slate-500 hover:border-indigo-500/30 hover:text-indigo-400 transition-all font-bold text-sm"
                            onclick="addExperience()">+ Adicionar Experiência</button>

                        <div class="flex gap-4 pt-4">
                            <button type="button" class="btn-prev flex-1" onclick="nextStep(3)">Anterior</button>
                            <button type="button" class="btn-next flex-1" onclick="nextStep(5)">Próximo</button>
                        </div>
                    </div>

                    <!-- Passo 5: Formação -->
                    <div class="form-step hidden space-y-6" id="step5">
                        <h2 class="text-2xl font-bold mb-6">Educação</h2>
                        <div id="educationContainer" class="space-y-6">
                            <?php foreach ($educationData as $index => $edu): ?>
                                <div
                                    class="dynamic-field glass-card p-6 rounded-3xl space-y-4 group transition-all hover:border-white/20 relative">
                                    <div class="flex justify-between items-center">
                                        <div
                                            class="drag-handle text-slate-500 cursor-move p-1 hover:text-white transition-colors">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M4 8h16M4 16h16" />
                                            </svg>
                                        </div>
                                        <?php if ($index > 0): ?>
                                            <button type="button"
                                                class="text-xs font-bold text-red-400 hover:text-red-300 transition-colors"
                                                onclick="removeField(this)">Remover</button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="space-y-2">
                                            <label
                                                class="text-xs font-bold text-slate-400 ml-1 uppercase tracking-wider">Instituição</label>
                                            <input type="text" name="education[<?php echo $index; ?>][institution]"
                                                class="form-input !py-2.5 !px-3 text-sm" required
                                                value="<?php echo htmlspecialchars($edu['institution'] ?? ''); ?>">
                                        </div>
                                        <div class="space-y-2">
                                            <label
                                                class="text-xs font-bold text-slate-400 ml-1 uppercase tracking-wider">Curso/Grau</label>
                                            <input type="text" name="education[<?php echo $index; ?>][degree]"
                                                class="form-input !py-2.5 !px-3 text-sm" required
                                                value="<?php echo htmlspecialchars($edu['degree'] ?? ''); ?>">
                                        </div>
                                        <div class="space-y-2">
                                            <label
                                                class="text-xs font-bold text-slate-400 ml-1 uppercase tracking-wider">Área
                                                de Estudo</label>
                                            <input type="text" name="education[<?php echo $index; ?>][field_of_study]"
                                                class="form-input !py-2.5 !px-3 text-sm" required
                                                value="<?php echo htmlspecialchars($edu['field_of_study'] ?? ''); ?>">
                                        </div>
                                        <div class="space-y-2">
                                            <label
                                                class="text-xs font-bold text-slate-400 ml-1 uppercase tracking-wider">Conclusão</label>
                                            <input type="text" name="education[<?php echo $index; ?>][graduation_date]"
                                                class="form-input !py-2.5 !text-center date-mask" placeholder="MM/AAAA"
                                                maxlength="7" required
                                                value="<?php echo htmlspecialchars($edu['graduation_date'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button"
                            class="w-full py-4 rounded-2xl border-2 border-dashed border-white/5 text-slate-500 hover:border-indigo-500/30 hover:text-indigo-400 transition-all font-bold text-sm"
                            onclick="addEducation()">+ Adicionar Formação</button>

                        <div class="flex gap-4 pt-4">
                            <button type="button" class="btn-prev flex-1" onclick="nextStep(4)">Anterior</button>
                            <button type="button" class="btn-next flex-1" onclick="nextStep(6)">Próximo</button>
                        </div>
                    </div>

                    <!-- Passo 6: Habilidades e Estilo -->
                    <div class="form-step hidden space-y-6" id="step6">
                        <h2 class="text-2xl font-bold mb-6">Habilidades e Estilo</h2>

                        <div class="space-y-4">
                            <div class="space-y-2">
                                <div class="flex justify-between items-center px-1">
                                    <label class="text-sm font-semibold text-slate-300">Habilidades</label>
                                    <button type="button"
                                        class="text-xs font-bold text-indigo-400 hover:text-white transition-colors"
                                        onclick="openSuggestions('skills', this)">✨ Sugestões</button>
                                </div>
                                <input type="text" name="skills" class="form-input"
                                    placeholder="PHP, PostgreSQL, Docker, UX Design" required
                                    value="<?php echo htmlspecialchars($skillsData); ?>">
                                <p class="text-[10px] text-slate-500 italic ml-1">Separe por vírgula. Ex: Excel, Java,
                                    Liderança...</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4 pt-4">
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-300 ml-1">Cor Principal</label>
                                    <div class="flex items-center gap-3 glass-card p-2 rounded-xl">
                                        <input type="color" name="primary_color"
                                            value="<?php echo htmlspecialchars($resumeData['primary_color'] ?? '#6366f1'); ?>"
                                            class="w-10 h-10 rounded-lg bg-transparent cursor-pointer border-none shrink-0">
                                        <span
                                            class="text-xs font-mono text-slate-400 uppercase"><?php echo htmlspecialchars($resumeData['primary_color'] ?? '#6366f1'); ?></span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-300 ml-1">Fonte</label>
                                    <select name="font_family" class="form-input !py-2">
                                        <option value="jakarta" <?php echo ($resumeData && ($resumeData['font_family'] ?? '') === 'jakarta') ? 'selected' : ''; ?>>Plus Jakarta Sans</option>
                                        <option value="inter" <?php echo ($resumeData && ($resumeData['font_family'] ?? '') === 'inter') ? 'selected' : ''; ?>>Inter</option>
                                        <option value="roboto" <?php echo ($resumeData && ($resumeData['font_family'] ?? '') === 'roboto') ? 'selected' : ''; ?>>Roboto</option>
                                        <option value="outfit" <?php echo ($resumeData && ($resumeData['font_family'] ?? '') === 'outfit') ? 'selected' : ''; ?>>Outfit</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4 pt-8">
                            <button type="button" class="btn-prev flex-1" onclick="nextStep(5)">Anterior</button>
                            <button type="submit"
                                class="btn-next flex-1 bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/20">Gerar
                                Currículo</button>
                        </div>

                        <?php if (isset($resumeData['slug'])): ?>
                            <div class="pt-6 border-t border-white/5 text-center">
                                <a href="cv.php?slug=<?php echo $resumeData['slug']; ?>" target="_blank"
                                    class="inline-flex items-center gap-2 text-sm font-bold text-indigo-400 hover:text-white hover:underline transition-all">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
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
        </div> <!-- Sidebar Form Area -->

        <!-- Main Preview Area (Desktop) -->
        <div class="hidden lg:flex flex-grow bg-[#0f172a] items-center justify-center p-8 xl:p-12 relative">
            <!-- Background Orbs -->
            <div
                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-500/5 blur-[120px] rounded-full">
            </div>

            <div
                class="w-full h-full max-w-[800px] bg-slate-900 rounded-[2.5rem] shadow-2xl border border-white/5 overflow-hidden flex flex-col relative">
                <div class="flex justify-between items-center p-5 bg-slate-800/50 border-b border-white/5">
                    <div class="flex gap-2">
                        <div class="w-3 h-3 rounded-full bg-red-500/30"></div>
                        <div class="w-3 h-3 rounded-full bg-amber-500/30"></div>
                        <div class="w-3 h-3 rounded-full bg-emerald-500/30"></div>
                    </div>
                    <span class="text-xs font-bold text-slate-500 tracking-widest uppercase">Visualização em Tempo
                        Real</span>
                    <div class="w-8"></div>
                </div>
                <div class="flex-grow bg-white">
                    <iframe id="previewIframe" class="w-full h-full border-none"></iframe>
                </div>
            </div>
        </div>

        <!-- Suggestion Modal -->
        <div id="suggestionModal"
            class="fixed inset-0 z-[110] hidden items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
            onclick="closeSuggestions()">
            <div class="glass-card w-full max-w-lg rounded-[2.5rem] p-8 space-y-6 animate-in zoom-in duration-300"
                onclick="event.stopPropagation()">
                <div>
                    <h3 class="text-2xl font-bold text-white mb-2">Sugestões Profissionais</h3>
                    <p class="text-sm text-slate-400">Escolha uma opção abaixo. Você poderá editá-la depois.</p>
                </div>

                <div id="suggestionsList"
                    class="space-y-3 max-h-[400px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-white/10"></div>

                <button type="button" class="btn-prev w-full" onclick="closeSuggestions()">Fechar</button>
            </div>
        </div>
    </div> <!-- .flex -->

    <script>
        const niche = '<?php echo $_GET['niche'] ?? ''; ?>';
        let expCount = <?php echo count($experiencesData ?? []); ?>;
        let eduCount = <?php echo count($educationData ?? []); ?>;
    </script>
    <script src="public/assets/js/generator.js"></script>
</body>

</html>