<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Currículos - ApproveMax</title>
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
                @apply bg-[#0f172a] text-slate-200 antialiased;
            }
        }
        @layer components {
            .glass-card {
                @apply bg-slate-800/50 backdrop-blur-xl border border-white/10;
            }
            .btn-base {
                @apply px-4 py-2 rounded-xl font-bold transition-all active:scale-[0.98] text-sm text-center;
            }
            .btn-primary {
                @apply btn-base bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-500/20;
            }
            .btn-outline {
                @apply btn-base bg-slate-800/50 border border-white/10 hover:bg-slate-700/50 text-slate-200;
            }
        }
    </style>
    <script src="https://js.puter.com/v2/"></script>
</head>

<body>
    <div class="max-w-7xl mx-auto px-4 py-6 md:py-10">
        <header class="flex flex-col md:flex-row justify-between items-center gap-6 mb-12">
            <div
                class="text-2xl font-extrabold bg-gradient-to-r from-white to-indigo-400 bg-clip-text text-transparent">
                ApproveMax</div>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="index.php" class="btn-outline flex items-center gap-2">🏠 Home</a>
                <a href="generator.php" class="btn-primary">Criar Novo Currículo</a>
                <a href="logout.php" class="btn-outline text-red-400 border-red-500/20 hover:bg-red-500/10">Sair</a>
            </div>
        </header>

        <h1 class="text-3xl md:text-4xl font-extrabold mb-8 text-center md:text-left">Meus Currículos</h1>

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
            <div class="glass-card rounded-[2rem] p-12 text-center">
                <p class="text-slate-400 mb-8">Você ainda não gerou nenhum currículo.</p>
                <a href="generator.php" class="btn-primary inline-block">Começar Agora</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($resumes as $resume): ?>
                    <div class="glass-card p-6 rounded-3xl hover:border-indigo-500/30 transition-all flex flex-col">
                        <div class="flex-grow mb-6">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-bold truncate pr-4">
                                    <?php echo htmlspecialchars($resume['full_name']); ?>
                                </h3>
                                <span
                                    class="text-xs font-bold text-slate-400 bg-slate-900/50 px-2 py-1 rounded-lg flex items-center gap-1 shrink-0">
                                    👁️ <?php echo (int) $resume['views']; ?>
                                </span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <p class="text-sm text-slate-400">
                                    <?php echo date('d/m/Y H:i', strtotime($resume['created_at'])); ?>
                                </p>
                                <p class="text-xs font-bold text-indigo-400 uppercase tracking-wider">
                                    <?php echo htmlspecialchars($resume['template_id']); ?>
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mt-auto">
                            <a href="generate-pdf.php?id=<?php echo $resume['id']; ?>" target="_blank"
                                class="btn-outline bg-indigo-500/5 border-indigo-500/20 text-indigo-300">PDF</a>
                            <a href="export-word.php?id=<?php echo $resume['id']; ?>"
                                class="btn-outline bg-blue-500/5 border-blue-500/20 text-blue-300">Word</a>
                            <a href="#" onclick="openAtsModal(<?php echo $resume['id']; ?>)"
                                class="btn-outline bg-amber-500/5 border-amber-500/20 text-amber-300">ATS</a>
                            <a href="#"
                                onclick="copyLink('<?php echo (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/cv.php?slug=' . $resume['slug']; ?>')"
                                class="btn-outline bg-emerald-500/5 border-emerald-500/20 text-emerald-300">Link</a>
                            <a href="generator.php?id=<?php echo $resume['id']; ?>"
                                class="btn-outline border-indigo-500/40 text-indigo-400">Editar</a>
                            <a href="duplicate-resume.php?id=<?php echo $resume['id']; ?>" class="btn-outline">Duplicar</a>
                            <a href="delete-resume.php?id=<?php echo $resume['id']; ?>"
                                onclick="return confirm('Tem certeza que deseja excluir este currículo? Esta ação não pode ser desfeita.')"
                                class="btn-outline col-span-2 text-red-400 border-red-500/20 hover:bg-red-500/10">Excluir</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="atsModal"
        class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="glass-card p-8 md:p-10 rounded-[2.5rem] max-w-2xl w-full relative overflow-hidden">
            <h2 class="text-2xl font-extrabold mb-4 text-amber-400">Análise de IA (ATS)</h2>
            <p class="text-slate-400 text-sm mb-6">Cole abaixo a descrição da vaga para ver quão bem seu currículo se
                adapta aos requisitos.</p>

            <input type="hidden" id="atsResumeId">
            <textarea id="jobDescription"
                class="w-full h-40 bg-slate-900/50 border border-white/10 rounded-2xl p-4 text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-all placeholder:text-slate-600 mb-6"
                placeholder="Cole aqui a descrição da vaga..."></textarea>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <button onclick="analyzeAts()" class="btn-primary bg-amber-600 hover:bg-amber-700 shadow-amber-500/20"
                    id="btnAnalyze">Analisar Agora</button>
                <button onclick="closeAtsModal()" class="btn-outline">Fechar</button>
            </div>

            <div id="atsResult" class="hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="flex items-center gap-6 mb-6 p-4 glass-card rounded-2xl">
                    <div class="w-20 h-20 rounded-full border-4 border-amber-500/30 flex items-center justify-center text-2xl font-black text-amber-400"
                        id="atsScore">0%</div>
                    <div>
                        <p class="font-bold text-slate-200">Compatibilidade Geral</p>
                        <p class="text-xs text-slate-400">Baseado em palavras-chave e requisitos.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <p class="text-sm font-bold text-slate-200 mb-2">Palavras-chave encontradas:</p>
                        <div id="atsMatches" class="flex flex-wrap gap-2 text-xs"></div>
                    </div>

                    <div>
                        <p class="text-sm font-bold text-red-400 mb-2 font-black">Palavras-chave ausentes:</p>
                        <div id="atsMissing" class="flex flex-wrap gap-2 text-xs"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="public/assets/js/dashboard.js"></script>
</body>

</html>