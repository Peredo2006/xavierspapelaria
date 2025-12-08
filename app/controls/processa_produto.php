<?php
// models/processa_produto.php
session_start();
require_once '../models/Database.php';
require_once '../models/Produto.php';
require_once '../models/Auth.php';

// Inicializar models
$database = new Database();
$auth = new Auth($database);
$produto = new Produto($database);

// Verificar se o usuário está logado
if (!$auth->isLoggedIn()) {
    $_SESSION['erro'] = 'Acesso negado! Faça login para continuar.';
    header('Location: ../views/login.php');
    exit();
}

// Buscar ação tanto do GET quanto do POST
$acao = isset($_GET['acao']) ? trim($_GET['acao']) : (isset($_POST['acao']) ? trim($_POST['acao']) : '');

try {
    if ($acao === 'novo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitizar e validar dados
        $dados = [
            'nome' => trim(htmlspecialchars($_POST['nome'] ?? '')),
            'quantidade' => intval($_POST['quantidade'] ?? 0),
            'preco' => floatval(str_replace(',', '.', $_POST['preco'] ?? 0)),
            'descricao' => trim(htmlspecialchars($_POST['descricao'] ?? '')),
            'categoria' => trim(htmlspecialchars($_POST['categoria'] ?? 'Outros')), // NOVO CAMPO
        ];

        // Validações básicas
        if (!$produto->validarDados($dados)) {
            throw new Exception('Dados inválidos fornecidos.');
        }

        // Processar imagem (agora opcional)
        $imagem = null;
        if (!empty($_FILES['imagem']['name']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $imagem = $_FILES['imagem'];
        }

        // Criar produto
        $novoid_produto = $produto->criar($dados, $imagem);
        
        if ($novoid_produto > 0) {
            $_SESSION['sucesso'] = 'Produto cadastrado com sucesso!';
        } else {
            throw new Exception('Erro ao cadastrar produto.');
        }

    } elseif ($acao === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_produto = intval($_POST['id_produto'] ?? 0);
        $dados = [
            'nome' => trim(htmlspecialchars($_POST['nome'] ?? '')),
            'quantidade' => intval($_POST['quantidade'] ?? 0),
            'preco' => floatval(str_replace(',', '.', $_POST['preco'] ?? 0)),
            'descricao' => trim(htmlspecialchars($_POST['descricao'] ?? '')),
            'categoria' => trim(htmlspecialchars($_POST['categoria'] ?? 'Outros')), // NOVO CAMPO
        ];
        $fotoAtual = $_POST['foto_atual'] ?? '';

        // Validações básicas
        if ($id_produto <= 0 || !$produto->validarDados($dados)) {
            throw new Exception('Dados inválidos fornecidos.');
        }

        // Processar nova imagem (opcional)
        $imagem = null;
        if (!empty($_FILES['imagem']['name']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $imagem = $_FILES['imagem'];
        }

        // Atualizar produto
        $resultado = $produto->atualizar($id_produto, $dados, $imagem, $fotoAtual);
        
        if ($resultado > 0) {
            $_SESSION['sucesso'] = 'Produto atualizado com sucesso!';
        } else {
            throw new Exception('Nenhuma alteração foi realizada.');
        }

    } elseif ($acao === 'excluir' && isset($_GET['id'])) {
        $id_produto = intval($_GET['id']);

        if ($id_produto <= 0) {
            throw new Exception('id_produto inválido.');
        }

        // Excluir produto
        $resultado = $produto->excluir($id_produto);
        
        if ($resultado > 0) {
            $_SESSION['sucesso'] = 'Produto excluído com sucesso!';
        } else {
            throw new Exception('Erro ao excluir produto.');
        }

    } else {
        throw new Exception('Ação inválida: ' . $acao);
    }

} catch (Exception $e) {
    $_SESSION['erro'] = $e->getMessage();
}

header('Location: ../views/produtos.php');
exit();
?>