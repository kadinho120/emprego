<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Auth;

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);

if ($action === 'check-email') {
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'E-mail é obrigatório']);
        exit;
    }

    $result = Auth::checkEmail($email);
    echo json_encode(['success' => true, 'data' => $result]);
    exit;
}

if ($action === 'login') {
    $password = $_POST['password'] ?? '';
    if (Auth::login($email, $password)) {
        echo json_encode(['success' => true, 'redirect' => 'generator.php']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Senha incorreta ou e-mail inválido']);
    }
    exit;
}

if ($action === 'register-password') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($password) || strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'A senha deve ter pelo menos 6 caracteres']);
        exit;
    }

    if ($password !== $confirm) {
        echo json_encode(['success' => false, 'message' => 'As senhas não coincidem']);
        exit;
    }

    if (Auth::registerPassword($email, $password)) {
        echo json_encode(['success' => true, 'redirect' => 'generator.php']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao definir senha ou e-mail não autorizado.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Ação inválida']);
