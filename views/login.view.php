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
    <link rel="stylesheet" href="public/assets/css/login.css">
</head>

<body>

    <div class="login-card">
        <div class="logo">ApproveMax</div>

        <div id="alert" class="error-msg" style="display: none;"></div>

        <form id="loginForm" onsubmit="event.preventDefault();">
            <!-- Passo 1: Email -->
            <div id="step1" class="step">
                <h2>Boas-vindas!</h2>
                <p>Insira seu e-mail para continuar.</p>
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" id="email" placeholder="nome@exemplo.com" required autofocus>
                </div>
                <button type="button" onclick="checkEmail()" class="btn-login" id="btnNext">Continuar</button>
            </div>

            <!-- Passo 2: Login com Senha -->
            <div id="step2-login" class="step" style="display: none;">
                <h2>Seja bem-vindo de volta!</h2>
                <p>Digite sua senha para acessar sua conta.</p>
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" id="password" placeholder="Sua senha">
                </div>
                <button type="button" onclick="doLogin()" class="btn-login" id="btnLogin">Entrar</button>
                <button type="button" onclick="backToStep1()"
                    style="background:transparent; border:none; color:var(--text-muted); margin-top:1.5rem; cursor:pointer; font-size:0.875rem; text-decoration: underline;">Alterar
                    e-mail</button>
            </div>

            <!-- Passo 2: Primeiro Acesso (Definir Senha) -->
            <div id="step2-register" class="step" style="display: none;">
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
                <button type="button" onclick="registerPassword()" class="btn-login" id="btnRegister">Criar Conta e
                    Acessar</button>
                <button type="button" onclick="backToStep1()"
                    style="background:transparent; border:none; color:var(--text-muted); margin-top:1.5rem; cursor:pointer; font-size:0.875rem; text-decoration: underline;">Alterar
                    e-mail</button>
            </div>
        </form>
    </div>

    <script src="public/assets/js/login.js"></script>
</body>

</html>