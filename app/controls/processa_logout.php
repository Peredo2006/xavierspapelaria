<?php
// models/logout.php
session_start();
require_once '../models/Database.php';
require_once '../models/Auth.php';

// Inicializar models
$database = new Database();
$auth = new Auth($database);

// Registrar log de logout (opcional, para auditoria)
if ($auth->isLoggedIn()) {
    $user = $auth->getUser();
    $userId = $user['id'];
    $userName = $user['nome'] ?? 'Usuário';
    
    // Aqui pode registrar em um arquivo de log ou banco de dados
    error_log("Logout realizado - Usuário: $userName (ID: $userId)");
    
    // Opcional: Registrar no banco de dados
    // $usuario = new Usuario($database);
    // $usuario->registrarLogout($userId);
}

// Fazer logout usando a classe Auth
$auth->logout();

// Redirecionar para login com mensagem de sucesso
header('Location: ../views/login.php?sucesso=logout');
exit();
?>