<?php
// models/processa_tarefa.php
session_start();
require_once '../models/Database.php';
require_once '../models/Tarefa.php';
require_once '../models/Auth.php';

// Inicializar models
$database = new Database();
$auth = new Auth($database);
$tarefa = new Tarefa($database);

// Verificar se o usuário está logado
if (!$auth->isLoggedIn()) {
    $_SESSION['erro'] = 'Acesso negado! Faça login para continuar.';
    header('Location: ../views/login.php');
    exit();
}

// Buscar ação tanto do GET quanto do POST
$acao = isset($_GET['acao']) ? trim($_GET['acao']) : (isset($_POST['acao']) ? trim($_POST['acao']) : '');

try {
    if ($acao === 'nova' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitizar e validar dados
        $dados = [
            'titulo' => trim(htmlspecialchars($_POST['titulo'] ?? '')),
            'descricao' => trim(htmlspecialchars($_POST['descricao'] ?? '')),
            'data_tarefa' => trim($_POST['data_tarefa'] ?? ''),
            'prioridade' => trim($_POST['prioridade'] ?? 'media'),
            'repeticao' => trim($_POST['repeticao'] ?? 'nenhuma'),
            'observacoes' => trim(htmlspecialchars($_POST['observacoes'] ?? ''))
        ];

        // Validações básicas
        if (!$tarefa->validarDados($dados)) {
            throw new Exception('Título e data são obrigatórios.');
        }

        // Criar tarefa
        $novoIdTarefa = $tarefa->criar($dados);
        
        if ($novoIdTarefa > 0) {
            $_SESSION['sucesso'] = 'Tarefa criada com sucesso!';
        } else {
            throw new Exception('Erro ao criar tarefa.');
        }

    } elseif ($acao === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_tarefa = intval($_POST['id_tarefa'] ?? 0);
        $dados = [
            'titulo' => trim(htmlspecialchars($_POST['titulo'] ?? '')),
            'descricao' => trim(htmlspecialchars($_POST['descricao'] ?? '')),
            'data_tarefa' => trim($_POST['data_tarefa'] ?? ''),
            'prioridade' => trim($_POST['prioridade'] ?? 'media'),
            'repeticao' => trim($_POST['repeticao'] ?? 'nenhuma'),
            'observacoes' => trim(htmlspecialchars($_POST['observacoes'] ?? ''))
        ];

        // Validações básicas
        if ($id_tarefa <= 0 || !$tarefa->validarDados($dados)) {
            throw new Exception('Dados inválidos fornecidos.');
        }

        // Atualizar tarefa
        $resultado = $tarefa->atualizar($id_tarefa, $dados);
        
        if ($resultado > 0) {
            $_SESSION['sucesso'] = 'Tarefa atualizada com sucesso!';
        } else {
            throw new Exception('Nenhuma alteração foi realizada.');
        }

    } elseif ($acao === 'concluir' && isset($_GET['id'])) {
        $id_tarefa = intval($_GET['id']);

        if ($id_tarefa <= 0) {
            throw new Exception('ID da tarefa inválido.');
        }

        // Marcar como concluída
        $resultado = $tarefa->marcarConcluida($id_tarefa);
        
        if ($resultado > 0) {
            $_SESSION['sucesso'] = 'Tarefa marcada como concluída!';
        } else {
            throw new Exception('Erro ao marcar tarefa como concluída.');
        }

    } elseif ($acao === 'pendente' && isset($_GET['id'])) {
        $id_tarefa = intval($_GET['id']);

        if ($id_tarefa <= 0) {
            throw new Exception('ID da tarefa inválido.');
        }

        // Marcar como pendente
        $resultado = $tarefa->marcarPendente($id_tarefa);
        
        if ($resultado > 0) {
            $_SESSION['sucesso'] = 'Tarefa marcada como pendente!';
        } else {
            throw new Exception('Erro ao marcar tarefa como pendente.');
        }

    } elseif ($acao === 'excluir' && isset($_GET['id'])) {
        $id_tarefa = intval($_GET['id']);

        if ($id_tarefa <= 0) {
            throw new Exception('ID da tarefa inválido.');
        }

        // Excluir tarefa
        $resultado = $tarefa->excluir($id_tarefa);
        
        if ($resultado > 0) {
            $_SESSION['sucesso'] = 'Tarefa excluída com sucesso!';
        } else {
            throw new Exception('Erro ao excluir tarefa.');
        }

    } else {
        throw new Exception('Ação inválida: ' . $acao);
    }

} catch (Exception $e) {
    $_SESSION['erro'] = $e->getMessage();
}

header('Location: ../views/notificacoes.php');
exit();
?>