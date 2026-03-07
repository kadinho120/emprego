const alertBox = document.getElementById('alert');
const emailInput = document.getElementById('email');

function showAlert(msg) {
    alertBox.textContent = msg;
    alertBox.style.display = 'block';
}

function hideAlert() {
    alertBox.style.display = 'none';
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
        const step1 = document.getElementById('step1');
        const step2Login = document.getElementById('step2-login');
        const step2Register = document.getElementById('step2-register');

        if (step1 && step1.style.display !== 'none') {
            checkEmail();
        } else if (step2Login && step2Login.style.display === 'block') {
            doLogin();
        } else if (step2Register && step2Register.style.display === 'block') {
            registerPassword();
        }
    }
});
