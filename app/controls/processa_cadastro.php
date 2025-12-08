<?php
// models/processa_cadastro.php
session_start();
require_once '../models/Database.php';
require_once '../models/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $usuario = new Usuario($database);
    
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    
    // Validações básicas
    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $_SESSION['erro_cadastro'] = 'Todos os campos são obrigatórios!';
        header('Location: ../views/cadastro.php');
        exit;
    }
    
    if ($senha !== $confirmar_senha) {
        $_SESSION['erro_cadastro'] = 'As senhas não coincidem!';
        header('Location: ../views/cadastro.php');
        exit;
    }
    
    if (strlen($senha) < 6) {
        $_SESSION['erro_cadastro'] = 'A senha deve ter pelo menos 6 caracteres!';
        header('Location: ../views/cadastro.php');
        exit;
    }
    
    // Verificar se email já existe
    if (!$usuario->verificarEmailUnico($email)) {
        $_SESSION['erro_cadastro'] = 'Este e-mail já está cadastrado!';
        header('Location: ../views/cadastro.php');
        exit;
    }
    
    // Criar usuário (tipo padrão: Vendedor)
    $dados = [
        'nome' => $nome,
        'email' => $email,
        'senha' => $senha, // Será criptografada na classe Usuario
        'tipo' => 'Vendedor'
    ];
    
    $novo_id = $usuario->criar($dados);
    
    if ($novo_id) {
        $_SESSION['sucesso_cadastro'] = 'Cadastro realizado com sucesso! Faça o login.';
        header('Location: ../views/login.php');
        exit;
    } else {
        $_SESSION['erro_cadastro'] = 'Erro ao realizar cadastro. Tente novamente.';
        header('Location: ../views/cadastro.php');
        exit;
    }
} else {
    header('Location: ../views/cadastro.php');
    exit;
}
?>