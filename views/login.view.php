<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ApproveMax</title>
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
                @apply bg-[#0f172a] text-slate-200 antialiased min-h-screen flex items-center justify-center p-4;
            }
        }
        @layer components {
            .glass-card {
                @apply bg-slate-800/50 backdrop-blur-xl border border-white/10;
            }
            .form-input {
                @apply w-full bg-slate-900/50 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder:text-slate-500;
            }
            .btn-primary {
                @apply w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-indigo-500/20 transition-all active:scale-[0.98];
            }
        }
    </style>
</head>

<body>

    <div class="glass-card p-8 md:p-10 rounded-[2.5rem] w-full max-w-md relative overflow-hidden">
        <!-- Background Orb -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-500/10 blur-3xl rounded-full"></div>

        <div
            class="text-3xl font-extrabold bg-gradient-to-r from-white to-indigo-400 bg-clip-text text-transparent mb-8 text-center">
            ApproveMax</div>

        <div id="alert"
            class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-xl text-sm mb-6 flex items-center gap-3 animate-pulse"
            style="display: none;">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                </path>
            </svg>
            <span id="errorText"></span>
        </div>

        <form id="loginForm" onsubmit="event.preventDefault();" class="space-y-6">
            <!-- Passo 1: Email -->
            <div id="step1" class="space-y-6">
                <div class="text-center">
                    <h2 class="text-2xl font-bold mb-2">Boas-vindas!</h2>
                    <p class="text-slate-400">Insira seu e-mail para continuar.</p>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-slate-300 ml-1">E-mail</label>
                    <input type="email" id="email" class="form-input" placeholder="nome@exemplo.com" required autofocus>
                </div>
                <button type="button" onclick="checkEmail()" class="btn-primary" id="btnNext">Continuar</button>
            </div>

            <!-- Passo 2: Login com Senha -->
            <div id="step2-login" class="space-y-6" style="display: none;">
                <div class="text-center">
                    <h2 class="text-2xl font-bold mb-2">Bem-vindo de volta!</h2>
                    <p class="text-slate-400">Digite sua senha para acessar sua conta.</p>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-slate-300 ml-1">Senha</label>
                    <input type="password" id="password" class="form-input" placeholder="Sua senha">
                </div>
                <button type="button" onclick="doLogin()" class="btn-primary" id="btnLogin">Entrar</button>
                <div class="text-center">
                    <button type="button" onclick="backToStep1()"
                        class="text-sm text-slate-500 hover:text-white underline decoration-dashed underline-offset-4 transition-colors">Alterar
                        e-mail</button>
                </div>
            </div>

            <!-- Passo 2: Primeiro Acesso (Definir Senha) -->
            <div id="step2-register" class="space-y-6" style="display: none;">
                <div class="text-center">
                    <h2 class="text-2xl font-bold mb-2">Primeiro acesso?</h2>
                    <p class="text-slate-400">Crie uma senha segura para proteger seus currículos.</p>
                </div>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-300 ml-1">Definir Senha</label>
                        <input type="password" id="reg_password" class="form-input" placeholder="Mínimo 6 caracteres">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-300 ml-1">Confirmar Senha</label>
                        <input type="password" id="reg_confirm" class="form-input" placeholder="Confirme sua senha">
                    </div>
                </div>
                <button type="button" onclick="registerPassword()" class="btn-primary" id="btnRegister">Criar Conta e
                    Acessar</button>
                <div class="text-center">
                    <button type="button" onclick="backToStep1()"
                        class="text-sm text-slate-500 hover:text-white underline decoration-dashed underline-offset-4 transition-colors">Alterar
                        e-mail</button>
                </div>
            </div>
        </form>
    </div>

    <script src="public/assets/js/login.js"></script>
</body>

</html>