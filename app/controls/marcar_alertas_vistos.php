<?php
// models/marcar_alertas_vistos.php
session_start();

// Marcar que o usuário já viu os alertas de hoje
$_SESSION['alertas_vistos'] = true;

// Retornar resposta JSON para o AJAX
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>