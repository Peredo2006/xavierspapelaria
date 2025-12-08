<?php
// models/logout.php
session_start();
require_once '../models/Database.php';
require_once '../models/Auth.php';
require_once '../models/Usuario.php';

// Inicializar classes
$database = new Database();
$auth = new Auth($database);
$usuario = new Usuario($database);

// Registrar logout se usuário estiver logado (com tratamento de erro)
if (isset($_SESSION['user_id'])) {
    try {
        $usuario->registrarLogout($_SESSION['user_id']);
    } catch (Exception $e) {
        // Ignora erros no logout para não impedir o processo
        error_log("Erro ao registrar logout: " . $e->getMessage());
    }
}

// Fazer logout
$auth->logout();

// Redirecionar para login
header('Location: ../views/login.php');
exit;
?>