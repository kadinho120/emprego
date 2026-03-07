<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Auth;
Auth::init();
if (Auth::isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ApproveMax</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --background: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image:
                radial-gradient(circle at 0% 0%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(168, 85, 247, 0.15) 0%, transparent 50%);
        }

        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            padding: 3rem;
            border-radius: 32px;
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .logo {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
            text-decoration: none;
        }

        h2 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        p {
            color: var(--text-muted);
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-main);
        }

        input {
            width: 100%;
            padding: 0.8rem 1.2rem;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(15, 23, 42, 0.8);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .btn-primary {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: none;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .step-2 {
            display: none;
        }

        /* Loading */
        .btn-primary.loading {
            opacity: 0.7;
            pointer-events: none;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="logo">ApproveMax</div>

        <div id="alert" class="alert alert-error"></div>

        <form id="loginForm" onsubmit="event.preventDefault();">
            <!-- Step 1: Email -->
            <div id="step1" class="step">
                <h2>Boas-vindas!</h2>
                <p>Insira seu e-mail para continuar.</p>
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" id="email" placeholder="nome@exemplo.com" required>
                </div>
                <button type="button" onclick="checkEmail()" class="btn-primary" id="btnNext">Continuar</button>
            </div>

            <!-- Step 2: Password (Login) -->
            <div id="step2-login" class="step step-2">
                <h2>Seja bem-vindo de volta!</h2>
                <p>Digite sua senha para acessar sua conta.</p>
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" id="password" placeholder="Sua senha">
                </div>
                <button type="button" onclick="doLogin()" class="btn-primary" id="btnLogin">Entrar</button>
                <button type="button" onclick="backToStep1()"
                    style="background:transparent; border:none; color:var(--text-muted); margin-top:1rem; cursor:pointer; font-size:0.875rem;">Alterar
                    e-mail</button>
            </div>

            <!-- Step 2: Set Password (First Access) -->
            <div id="step2-register" class="step step-2">
                <h2>Primeiro acesso?</h2>
                <p>Crie uma senha segura para proteger seus currículos.</p>
                <div class="form-group">
                    <label>Definir Senha</label>
                    <input type="password" id="reg_password" placeholder="Mínimo 6 caracteres">
                </div>
                <div class="form-group">
                    <label>Confirmar Senha</label>
                    <input type="password" id="reg_confirm" placeholder="Confirme sua senha">
                </div>
                <button type="button" onclick="registerPassword()" class="btn-primary" id="btnRegister">Criar Conta e
                    Acessar</button>
                <button type="button" onclick="backToStep1()"
                    style="background:transparent; border:none; color:var(--text-muted); margin-top:1rem; cursor:pointer; font-size:0.875rem;">Alterar
                    e-mail</button>
            </div>
        </form>
    </div>

    <script>
        const alert = document.getElementById('alert');
        const emailInput = document.getElementById('email');

        function showAlert(msg) {
            alert.textContent = msg;
            alert.style.display = 'block';
        }

        function hideAlert() {
            alert.style.display = 'none';
        }

        async function checkEmail() {
            const email = emailInput.value;
            if (!email) {
                showAlert('E-mail é obrigatório');
                return;
            }

            const btn = document.getElementById('btnNext');
            btn.classList.add('loading');
            hideAlert();

            try {
                const formData = new FormData();
                formData.append('action', 'check-email');
                formData.append('email', email);

                const response = await fetch('login-process.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    const data = result.data;
                    document.getElementById('step1').style.display = 'none';

                    if (data.status === 'not_found' && data.role !== 'admin') {
                        showAlert('E-mail não autorizado para acesso.');
                        document.getElementById('step1').style.display = 'block';
                    } else if (data.has_password) {
                        document.getElementById('step2-login').style.display = 'block';
                    } else {
                        document.getElementById('step2-register').style.display = 'block';
                    }
                } else {
                    showAlert(result.message);
                }
            } catch (err) {
                showAlert('Erro de conexão ao verificar e-mail.');
            } finally {
                btn.classList.remove('loading');
            }
        }

        async function doLogin() {
            const email = emailInput.value;
            const password = document.getElementById('password').value;
            const btn = document.getElementById('btnLogin');

            if (!password) {
                showAlert('Senha é obrigatória');
                return;
            }

            btn.classList.add('loading');
            hideAlert();

            try {
                const formData = new FormData();
                formData.append('action', 'login');
                formData.append('email', email);
                formData.append('password', password);

                const response = await fetch('login-process.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    const params = new URLSearchParams(window.location.search);
                    const redirect = params.get('redirect') || result.redirect;
                    window.location.href = decodeURIComponent(redirect);
                } else {
                    showAlert(result.message);
                }
            } catch (err) {
                showAlert('Erro ao realizar login.');
            } finally {
                btn.classList.remove('loading');
            }
        }

        async function registerPassword() {
            const email = emailInput.value;
            const password = document.getElementById('reg_password').value;
            const confirm = document.getElementById('reg_confirm').value;
            const btn = document.getElementById('btnRegister');

            if (!password || password.length < 6) {
                showAlert('A senha deve ter pelo menos 6 caracteres');
                return;
            }

            if (password !== confirm) {
                showAlert('As senhas não coincidem');
                return;
            }

            btn.classList.add('loading');
            hideAlert();

            try {
                const formData = new FormData();
                formData.append('action', 'register-password');
                formData.append('email', email);
                formData.append('password', password);
                formData.append('confirm_password', confirm);

                const response = await fetch('login-process.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    const params = new URLSearchParams(window.location.search);
                    const redirect = params.get('redirect') || result.redirect;
                    window.location.href = decodeURIComponent(redirect);
                } else {
                    showAlert(result.message);
                }
            } catch (err) {
                showAlert('Erro ao definir senha.');
            } finally {
                btn.classList.remove('loading');
            }
        }

        function backToStep1() {
            document.querySelectorAll('.step').forEach(s => s.style.display = 'none');
            document.getElementById('step1').style.display = 'block';
            hideAlert();
        }

        // Enter key support
        document.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                if (document.getElementById('step1').style.display !== 'none') {
                    checkEmail();
                } else if (document.getElementById('step2-login').style.display === 'block') {
                    doLogin();
                } else if (document.getElementById('step2-register').style.display === 'block') {
                    registerPassword();
                }
            }
        });
    </script>
</body>

</html>