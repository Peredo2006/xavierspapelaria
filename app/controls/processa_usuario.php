<?php
// models/processa_usuario.php
session_start();
require_once '../models/Database.php';
require_once '../models/Usuario.php';
require_once '../models/Auth.php';

// Inicializar models
$database = new Database();
$usuario = new Usuario($database);
$auth = new Auth($database);

// Verificar se é gerente
if (!$auth->isAdmin()) {
    $_SESSION['erro'] = 'Acesso negado! Apenas gerentes podem gerenciar usuários.';
    header('Location: ../views/usuarios.php');
    exit;
}

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = intval($_POST['id_usuario'] ?? 0);
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $tipo = $_POST['tipo'];
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmarSenha'] ?? '';

    // Verificar se email é único
    if (!$usuario->verificarEmailUnico($email, $id_usuario)) {
        $_SESSION['erro'] = 'E-mail já cadastrado no sistema!';
        header('Location: ../views/usuarios.php');
        exit;
    }

    // Verificar senha se for novo usuário
    if ($id_usuario == 0 && empty($senha)) {
        $_SESSION['erro'] = 'A senha é obrigatória para novo usuário!';
        header('Location: ../views/usuarios.php');
        exit;
    }

    // Verificar se as senhas coincidem
    if (!empty($senha) && $senha !== $confirmar_senha) {
        $_SESSION['erro'] = 'As senhas não coincidem!';
        header('Location: ../views/usuarios.php');
        exit;
    }

    // Preparar dados
    $dados = [
        'nome' => $nome,
        'email' => $email,
        'tipo' => $tipo
    ];

    // Adicionar senha se fornecida
    if (!empty($senha)) {
        $dados['senha'] = $senha;
    }

    if ($id_usuario == 0) {
        // Criar novo usuário
        $novo_id = $usuario->criar($dados);
        if ($novo_id) {
            $_SESSION['sucesso'] = 'Usuário cadastrado com sucesso!';
        } else {
            $_SESSION['erro'] = 'Erro ao cadastrar usuário.';
        }
    } else {
        // Atualizar usuário existente
        if ($usuario->atualizar($id_usuario, $dados)) {
            $_SESSION['sucesso'] = 'Usuário atualizado com sucesso!';
        } else {
            $_SESSION['erro'] = 'Erro ao atualizar usuário.';
        }
    }

    header('Location: ../views/usuarios.php');
    exit;
}

// Redirecionar se acesso direto
header('Location: ../views/usuarios.php');
exit;
?>