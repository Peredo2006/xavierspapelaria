<?php
// models/processa_login.php
session_start();
require_once '../models/Database.php';
require_once '../models/Usuario.php';
require_once '../models/Auth.php';

// Inicializar models
$database = new Database();
$auth = new Auth($database);
$usuario = new Usuario($database);

// Verificar se já está logado (redirecionar se sim)
if ($auth->isLoggedIn()) {
    header('Location: ../../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitizar e validar dados
        $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
        $senha = $_POST['senha'] ?? '';

        // Validações básicas
        if (empty($email) || empty($senha)) {
            throw new Exception('Por favor, preencha todos os campos.');
        }

        // Validar formato do email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Por favor, insira um e-mail válido.');
        }

        // Tentar login usando a classe Auth
        if ($auth->login($email, $senha)) {
            // Login bem-sucedido - redirecionar para página inicial
            header('Location: ../../index.php');
            exit();
        } else {
            // Credenciais incorretas
            throw new Exception('E-mail ou senha incorretos.');
        }

    } catch (Exception $e) {
        $_SESSION['erro_login'] = $e->getMessage();
        header('Location: ../views/login.php');
        exit();
    }
} else {
    // Se não for POST, redirecionar para login
    header('Location: ../views/login.php');
    exit();
}
?>