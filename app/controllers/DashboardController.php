<?php

session_start();

// Define o caminho base para facilitar as inclusões, assumindo que este arquivo é chamado pelo index.php na raiz
// Ou usamos __DIR__ para garantir que o caminho seja relativo a este arquivo de controller
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/Auth.php';
require_once __DIR__ . '/../models/Tarefa.php';

// Inicializar classes
$database = new Database();
$auth = new Auth($database);
$tarefa = new Tarefa($database);

// Verificar se o usuário está logado
if (!$auth->isLoggedIn()) {
    header('Location: app/views/login.php');
    exit();
}

// Lógica de Negócio (Preparar dados para a View)
$is_gerente = $auth->isAdmin();
$user = $auth->getUser();
$tarefas_hoje = $tarefa->buscarTarefasHoje();

// Lógica do Modal
$alertas_vistos = $_SESSION['alertas_vistos'] ?? false;
$mostrar_modal = (!empty($tarefas_hoje) && !$alertas_vistos);

// O arquivo termina aqui, deixando as variáveis ($user, $tarefas_hoje, etc) 
// disponíveis para quem incluir este arquivo.