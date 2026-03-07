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
        }
    </style>
</head>

<body>

    <nav
        class="fixed top-4 left-4 right-4 z-50 glass-card rounded-full px-6 py-3 flex justify-between items-center transition-all">
        <div class="text-xl font-extrabold bg-gradient-to-r from-white to-indigo-400 bg-clip-text text-transparent">
            ApproveMax</div>
        <div class="flex gap-6 items-center">
            <a href="dashboard.php" class="text-sm font-semibold hover:text-indigo-400 transition-colors">Meus
                Currículos</a>
            <a href="logout.php"
                class="text-sm font-semibold text-slate-400 hover:text-red-400 transition-colors">Sair</a>
        </div>
    </nav>

    <div class="relative overflow-hidden pt-32 pb-16 px-4">
        <!-- Background Orbs -->
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-[500px] bg-indigo-500/10 blur-[120px] rounded-full -z-10">
        </div>

        <section class="max-w-4xl mx-auto text-center mb-24">
            <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-6">
                O primeiro gerador de currículos <br class="hidden md:block">
                <span
                    class="bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent italic px-2">focado
                    100% em conversão.</span>
            </h1>
            <p class="text-lg md:text-xl text-slate-400 mb-10 max-w-2xl mx-auto">
                Não apenas um PDF, mas uma ferramenta estratégica desenhada para passar em qualquer software de RH e
                impressionar recrutadores.
            </p>
            <a href="javascript:void(0)" onclick="openNicheModal()"
                class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-8 rounded-xl shadow-xl shadow-indigo-500/20 transform hover:-translate-y-1 transition-all">
                Criar Currículo Agora
            </a>
        </section>

        <section class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="glass-card p-8 rounded-3xl hover:border-white/20 transition-colors">
                <div class="text-3xl mb-4">🚀</div>
                <h3 class="text-xl font-bold mb-3">Foco em ATS</h3>
                <p class="text-slate-400 leading-relaxed">Nossos layouts são otimizados para serem lidos perfeitamente
                    por robôs de seleção.</p>
            </div>
            <div class="glass-card p-8 rounded-3xl hover:border-white/20 transition-colors">
                <div class="text-3xl mb-4">💎</div>
                <h3 class="text-xl font-bold mb-3">Design Premium</h3>
                <p class="text-slate-400 leading-relaxed">Templates modernos e elegantes que transmitem autoridade e
                    profissionalismo instantaneamente.</p>
            </div>
            <div class="glass-card p-8 rounded-3xl hover:border-white/20 transition-colors">
                <div class="text-3xl mb-4">⚡</div>
                <h3 class="text-xl font-bold mb-3">Inteligência Sugestiva</h3>
                <p class="text-slate-400 leading-relaxed">Receba sugestões de textos profissionais baseadas no seu nicho
                    para não perder tempo escrevendo.</p>
            </div>
        </section>
    </div>

    <!-- Modal de Seleção de Nicho -->
    <div id="nicheModal"
        class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="glass-card p-8 md:p-12 rounded-[2rem] max-w-2xl w-full text-center relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4">
                <button onclick="closeNicheModal()" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <h2 class="text-3xl font-extrabold mb-3">Quase lá!</h2>
            <p class="text-slate-400 mb-10">Para te dar as melhores sugestões e modelos, qual sua área de atuação?</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="generator.php?niche=tech"
                    class="group glass-card p-8 rounded-2xl hover:bg-indigo-600/20 hover:border-indigo-500/50 transition-all text-center">
                    <span class="text-4xl block mb-4 group-hover:scale-110 transition-transform">💻</span>
                    <h4 class="text-xl font-bold mb-2">Tecnologia / TI</h4>
                    <p class="text-sm text-slate-400">Devs, UX, Dados, Gestão...</p>
                </a>
                <a href="generator.php?niche=health"
                    class="group glass-card p-8 rounded-2xl hover:bg-indigo-600/20 hover:border-indigo-500/50 transition-all text-center">
                    <span class="text-4xl block mb-4 group-hover:scale-110 transition-transform">🏥</span>
                    <h4 class="text-xl font-bold mb-2">Saúde</h4>
                    <p class="text-sm text-slate-400">Enfermeiros, Técnicos, Gestão...</p>
                </a>
            </div>

            <button onclick="closeNicheModal()"
                class="mt-8 text-sm text-slate-500 hover:text-white underline decoration-dashed underline-offset-4 transition-colors">Voltar</button>
        </div>
    </div>

    <script src="public/assets/js/index.js"></script>
</body>

</html>