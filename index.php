<?php
require_once 'app/controllers/DashboardController.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo - Xavier's</title>
    <link href="https://fonts.googleapis.com/css2?family=National+Park&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="public/assets/css/style.css">
    <style>
        /* Modal de Alertas */
        .modal-alertas {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-alertas.mostrar {
            display: flex;
        }

        .modal-conteudo {
            background: white;
            border-radius: 15px;
            padding: 0;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            background: #E03A7F;
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.4rem;
        }

        .contador-alertas {
            background: white;
            color: #E03A7F;
            border-radius: 20px;
            padding: 5px 12px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .modal-body {
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
        }

        .alerta-item {
            background: #f8f9fa;
            border-left: 4px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
        }

        .alerta-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .alerta-item.alta {
            border-left-color: #dc3545;
            background: #f8d7da;
        }

        .alerta-item.media {
            border-left-color: #ffc107;
            background: #fff3cd;
        }

        .alerta-item.baixa {
            border-left-color: #17a2b8;
            background: #d1ecf1;
        }

        .alerta-titulo {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alerta-descricao {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .alerta-prioridade {
            font-size: 0.8rem;
            padding: 3px 8px;
            border-radius: 10px;
            background: #666;
            color: white;
            display: inline-block;
        }

        .alerta-prioridade.alta { background: #dc3545; }
        .alerta-prioridade.media { background: #ffc107; color: #000; }
        .alerta-prioridade.baixa { background: #17a2b8; }

        .modal-footer {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 0 0 15px 15px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }

        .btn-entendido {
            background: #7A06C7;
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-entendido:hover {
            background: #6805ad;
            transform: translateY(-2px);
        }

        /* Opção de menu bloqueada */
        .opcao-bloqueada {
            background-color: #95a5a6 !important; /* Cinza */
            cursor: not-allowed !important;
            opacity: 0.7;
            position: relative;
        }

        .opcao-bloqueada:hover {
            transform: none !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .opcao-bloqueada:hover:before {
            width: 0% !important;
        }

        /* Tooltip para o botão bloqueado */
        .opcao-bloqueada::after {
            content: "Acesso restrito a gerentes";
            position: absolute;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
            z-index: 1000;
        }

        .opcao-bloqueada:hover::after {
            opacity: 1;
        }
    </style>
</head>
<body class="pagina-inicial">
    <!-- Modal de Alertas -->
    <?php if ($mostrar_modal): ?>
    <div class="modal-alertas mostrar" id="modalAlertas">
        <div class="modal-conteudo">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-bell"></i> 
                    Lembretes para Hoje
                </h3>
                <span class="contador-alertas"><?php echo count($tarefas_hoje); ?> tarefas</span>
            </div>
            <div class="modal-body">
                <?php foreach ($tarefas_hoje as $t): ?>
                <div class="alerta-item <?php echo $t['prioridade']; ?>">
                    <div class="alerta-titulo">
                        <i class="fas fa-<?php 
                            switch($t['prioridade']) {
                                case 'alta': echo 'exclamation-triangle'; break;
                                case 'media': echo 'info-circle'; break;
                                case 'baixa': echo 'check-circle'; break;
                            }
                        ?>"></i>
                        <?php echo htmlspecialchars($t['titulo']); ?>
                    </div>
                    <?php if (!empty($t['descricao'])): ?>
                    <div class="alerta-descricao"><?php echo htmlspecialchars($t['descricao']); ?></div>
                    <?php endif; ?>
                    <div>
                        <span class="alerta-prioridade <?php echo $t['prioridade']; ?>">
                            Prioridade <?php echo $t['prioridade']; ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer">
                <button class="btn-entendido" onclick="fecharAlertas()">
                    <i class="fas fa-check"></i> Entendido
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <h1>Bem-vindo(a), <?php echo htmlspecialchars($user['nome']); ?>!</h1>
    
    <div class="logo-central">
        <img src="public/assets/images/logo.png" alt="Xavier's Logo">
    </div>

    <div class="menu-container">
        <div class="menu-coluna esquerda">
            <a href="app/views/produtos.php" class="opcao-menu">Gerenciar produtos</a>
            <?php if ($is_gerente): ?>
                <a href="app/views/usuarios.php" class="opcao-menu">Gerenciar funcionários</a>
            <?php else: ?>
                <a href="javascript:void(0)" class="opcao-menu opcao-bloqueada" title="Acesso restrito a gerentes">
                    <i class="fas fa-lock" style="margin-right: 8px;"></i>Gerenciar funcionários
                </a>
            <?php endif; ?>
        </div>
        <div class="menu-coluna direita">
            <a href="app/views/clientes.php" class="opcao-menu">Gerenciar clientes</a>
            <a href="app/views/notificacoes.php" class="opcao-menu">Notificações</a>
        </div>
    </div>
    
    <div class="user-info">
        <a href="app/controls/logout.php" class="btn-sair">Sair</a>
    </div>

    <script>
        // Funções para o modal de alertas
        function fecharAlertas() {
            document.getElementById('modalAlertas').classList.remove('mostrar');
            // Marcar como visto na sessão
            fetch('app/models/marcar_alertas_vistos.php')
                .then(response => response.json())
                .then(data => {
                    console.log('Alertas marcados como vistos');
                });
        }

        // Fechar modal clicando fora
        document.getElementById('modalAlertas')?.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharAlertas();
            }
        });

        // Fechar modal com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                fecharAlertas();
            }
        });
    </script>
</body>
</html>