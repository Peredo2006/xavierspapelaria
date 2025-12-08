<?php
// views/notificacoes.php
session_start();
require_once '../models/Database.php';
require_once '../models/Tarefa.php';
require_once '../models/Auth.php';

// Inicializar models
$database = new Database();
$auth = new Auth($database);
$tarefa = new Tarefa($database);

// Verificar se o usuário está logado
$auth->requireAuth();

// Processar filtros
$filtro_prioridade = $_GET['prioridade'] ?? 'todas';
$filtro_concluida = $_GET['concluida'] ?? 'pendentes';

// Buscar tarefas usando POO com filtros
$tarefas = $tarefa->buscarTarefas($filtro_prioridade, $filtro_concluida);

// Endpoint JSON (usado pelo modal de edição)
if (isset($_GET['json'])) {
    $id = intval($_GET['json']);
    $tarefaData = $tarefa->buscarPorId($id);
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($tarefaData ?? []);
    exit;
}

// Helper de escape
function e($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Xavier's - Gerenciamento de Notificações</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=National+Park&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <style>
        /* Estilos específicos para a página de notificações */
        .tarefas-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); 
            gap: 20px; 
            margin-top: 20px; 
        }
        
        .tarefa-card {
            background: #bff6d9; 
            border-radius: 16px; 
            padding: 20px; 
            display: flex; 
            flex-direction: column; 
            gap: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border: 3px solid transparent;
            height: fit-content;
            position: relative;
        }
        
        .tarefa-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }

        .tarefa-card.concluida {
            background: #e9ecef;
            opacity: 0.8;
        }

        .tarefa-card.concluida .tarefa-titulo {
            text-decoration: line-through;
            color: #6c757d;
        }

        .tarefa-prioridade {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }

        .prioridade-alta { background: #dc3545; }
        .prioridade-media { background: #ffc107; color: #000; }
        .prioridade-baixa { background: #17a2b8; }

        .tarefa-titulo {
            color: #FF7300; 
            font-weight: 800;
            font-size: 16px;
            margin-right: 80px;
            line-height: 1.3;
        }

        .tarefa-descricao {
            color: #666;
            font-size: 14px;
            line-height: 1.4;
            margin: 8px 0;
        }

        .tarefa-data {
            color: #7A06C7;
            font-weight: 600;
            font-size: 14px;
            background: rgba(162, 245, 184, 0.3);
            padding: 6px 12px;
            border-radius: 15px;
            width: fit-content;
        }

        .tarefa-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            font-size: 12px;
            color: #666;
        }

        .tarefa-observacoes {
            background: rgba(255, 255, 255, 0.5);
            padding: 10px;
            border-radius: 8px;
            border-left: 3px solid #7A06C7;
            font-size: 13px;
            color: #555;
        }

        .acoes { 
            display: flex; 
            gap: 8px; 
            justify-content: flex-end; 
            margin-top: 15px; 
            width: 100%;
            flex-wrap: nowrap; /* Adicione esta linha */
        }

        .acao-btn { 
            min-width: auto; /* Mude isto */
            width: auto; /* Adicione esta linha */
            height: 36px; 
            border-radius: 8px; 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            border: 0; 
            cursor: pointer; 
            color: #fff; 
            font-size: 14px; 
            transition: all 0.3s ease;
            padding: 0 12px;
            text-decoration: none; /* Adicione para remover sublinhado dos links */
            white-space: nowrap; /* Impede quebra de texto */
        }

        .acao-btn[href] {
            display: inline-flex; 
            align-items: center;
            text-decoration: none;
        }
        
        .acao-btn:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }
        
        .acao-editar { 
            background: #7A06C7; 
        }
        
        .acao-concluir { 
            background: #28a745; 
        }
        
        .acao-pendente { 
            background: #ffc107; 
            color: #000;
        }
        
        .acao-excluir { 
            background: #E03A7F; 
        }

        /* Filtros */
        .filtros-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .btn-group-filtros {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-filtro {
            border-radius: 20px;
            padding: 8px 20px;
            border: 2px solid #7A06C7;
            color: #7A06C7;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-filtro.filtro-ativo {
            background-color: #7A06C7;
            color: white;
            border-color: #7A06C7;
        }

        .btn-filtro:hover {
            background-color: #7A06C7;
            color: white;
        }

        /* Estados vazios */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 48px;
            color: #A2F5B8;
            margin-bottom: 16px;
        }

        /* Badge de status */
        .status-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }

        .status-pendente { background: #ffc107; color: #000; }
        .status-concluido { background: #28a745; color: white; }

        /* Responsividade */
        @media (max-width: 768px) {
            .tarefas-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .tarefa-card {
                padding: 16px;
            }

            .btn-group-filtros {
                flex-direction: column;
            }

            .btn-filtro {
                text-align: center;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .tarefas-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">
            <a href="../../index.php"><img src="../../public/assets/images/logo.png" alt="Logo Xavier's"></a>
        </div>
        <div>
            <button class="btn-sair" onclick="sair()">
                <i class="fas fa-sign-out-alt"></i> Sair
            </button>
        </div>
    </header>

    <!-- Principal -->
    <div class="container-principal">
        <h1 class="titulo-pagina">Gerenciamento de Notificações</h1>
        
        <!-- Mensagens de feedback -->
        <?php if (isset($_SESSION['sucesso'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['sucesso']); ?>
                <?php unset($_SESSION['sucesso']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['erro'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['erro']); ?>
                <?php unset($_SESSION['erro']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <span>Lista de Lembretes e Tarefas</span>
                <button class="btn-adicionar" data-bs-toggle="modal" data-bs-target="#modalTarefa">
                    <i class="fas fa-plus"></i> Nova Tarefa
                </button>
            </div>
            <div class="card-body">
                <!-- Filtros -->
                <div class="filtros-container">
                    <h5 style="margin-bottom: 15px; color: #292627;">Filtrar por:</h5>
                    
                    <div class="btn-group-filtros">
                        <!-- Filtro de Prioridade -->
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <small style="color: #666; font-weight: bold;">Prioridade:</small>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <a href="notificacoes.php?prioridade=todas&concluida=<?php echo $filtro_concluida; ?>" 
                                    class="btn-filtro <?php echo $filtro_prioridade == 'todas' ? 'filtro-ativo' : ''; ?>">
                                    Todas
                                </a>
                                <a href="notificacoes.php?prioridade=alta&concluida=<?php echo $filtro_concluida; ?>" 
                                    class="btn-filtro <?php echo $filtro_prioridade == 'alta' ? 'filtro-ativo' : ''; ?>">
                                    Alta
                                </a>
                                <a href="notificacoes.php?prioridade=media&concluida=<?php echo $filtro_concluida; ?>" 
                                class="btn-filtro <?php echo $filtro_prioridade == 'media' ? 'filtro-ativo' : ''; ?>">
                                    Média
                                </a>
                                <a href="notificacoes.php?prioridade=baixa&concluida=<?php echo $filtro_concluida; ?>" 
                                    class="btn-filtro <?php echo $filtro_prioridade == 'baixa' ? 'filtro-ativo' : ''; ?>">
                                    Baixa
                                </a>
                            </div>
                        </div>

                        <!-- Filtro de Status -->
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <small style="color: #666; font-weight: bold;">Status:</small>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <a href="notificacoes.php?prioridade=<?php echo $filtro_prioridade; ?>&concluida=pendentes" 
                                    class="btn-filtro <?php echo $filtro_concluida == 'pendentes' ? 'filtro-ativo' : ''; ?>">
                                    Pendentes
                                </a>
                                <a href="notificacoes.php?prioridade=<?php echo $filtro_prioridade; ?>&concluida=concluidas" 
                                    class="btn-filtro <?php echo $filtro_concluida == 'concluidas' ? 'filtro-ativo' : ''; ?>">
                                    Concluídas
                                </a>
                                <a href="notificacoes.php?prioridade=<?php echo $filtro_prioridade; ?>&concluida=todas" 
                                    class="btn-filtro <?php echo $filtro_concluida == 'todas' ? 'filtro-ativo' : ''; ?>">
                                    Todas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (empty($tarefas)): ?>
                    <div class="empty-state">
                        <i class="fas fa-tasks"></i>
                        <h4>
                            <?php if ($filtro_prioridade !== 'todas' || $filtro_concluida !== 'pendentes'): ?>
                                Nenhuma tarefa encontrada
                            <?php else: ?>
                                Nenhuma tarefa cadastrada
                            <?php endif; ?>
                        </h4>
                        <p>
                            <?php if ($filtro_prioridade !== 'todas' || $filtro_concluida !== 'pendentes'): ?>
                                Tente ajustar os filtros ou <a href="notificacoes.php">ver todas as tarefas</a>.
                            <?php else: ?>
                                Comece adicionando sua primeira tarefa ou lembrete.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="tarefas-grid">
                        <?php foreach ($tarefas as $t): ?>
                            <div class="tarefa-card <?php echo $t['concluida'] ? 'concluida' : ''; ?>">
                                <span class="tarefa-prioridade prioridade-<?php echo $t['prioridade']; ?>">
                                    <?php echo ucfirst($t['prioridade']); ?>
                                </span>
                                
                                <div class="tarefa-titulo">
                                    <?php echo e($t['titulo']); ?>
                                </div>
                                
                                <?php if (!empty($t['descricao'])): ?>
                                    <div class="tarefa-descricao"><?php echo e($t['descricao']); ?></div>
                                <?php endif; ?>
                                
                                <div class="tarefa-data">
                                    <i class="fas fa-calendar"></i> 
                                    <?php echo date('d/m/Y', strtotime($t['data_tarefa'])); ?>
                                </div>

                                <?php if (!empty($t['observacoes'])): ?>
                                    <div class="tarefa-observacoes">
                                        <strong>Observações:</strong> <?php echo e($t['observacoes']); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="tarefa-info">
                                    <span class="status-badge status-<?php echo $t['concluida'] ? 'concluido' : 'pendente'; ?>">
                                        <?php echo $t['concluida'] ? 'Concluída' : 'Pendente'; ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-history"></i> 
                                        Criada em: <?php echo date('d/m/Y', strtotime($t['data_criacao'])); ?>
                                    </span>
                                </div>

                                <div class="acoes">
                                    <?php if (!$t['concluida']): ?>
                                        <a class="acao-btn acao-concluir" 
                                            href="../controls/processa_tarefa.php?acao=concluir&id=<?php echo (int)$t['id_tarefa']; ?>" 
                                            title="Marcar como concluída"> 
                                            <i class="fas fa-check"></i> Concluir
                                        </a>
                                    <?php else: ?>
                                        <a class="acao-btn acao-pendente" 
                                            href="../controls/processa_tarefa.php?acao=pendente&id=<?php echo (int)$t['id_tarefa']; ?>" 
                                            title="Marcar como pendente">
                                            <i class="fas fa-undo"></i> Reabrir
                                        </a>
                                    <?php endif; ?>
                                    
                                    <button class="acao-btn acao-editar" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalTarefa"
                                            data-id="<?php echo $t['id_tarefa']; ?>"
                                            data-titulo="<?php echo e($t['titulo']); ?>"
                                            data-descricao="<?php echo e($t['descricao']); ?>"
                                            data-data_tarefa="<?php echo $t['data_tarefa']; ?>"
                                            data-prioridade="<?php echo $t['prioridade']; ?>"
                                            data-repeticao="<?php echo $t['repeticao']; ?>"
                                            data-observacoes="<?php echo e($t['observacoes']); ?>">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    
                                    <a class="acao-btn acao-excluir" 
                                        href="../controls/processa_tarefa.php?acao=excluir&id=<?php echo (int)$t['id_tarefa']; ?>" 
                                        onclick="return confirm('Tem certeza que deseja excluir a tarefa \'<?php echo e($t['titulo']); ?>\'?')"
                                        title="Excluir tarefa">
                                        <i class="fas fa-trash"></i> Excluir
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para Adicionar/Editar Tarefa -->
    <div class="modal fade" id="modalTarefa" tabindex="-1" aria-labelledby="modalTarefaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTarefaLabel">Nova Tarefa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formTarefa" method="post" action="../controls/processa_tarefa.php">
                    <div class="modal-body">
                        <input type="hidden" name="id_tarefa" id="id_tarefa">
                        <input type="hidden" name="acao" id="formAcao" value="nova">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Título da Tarefa <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" required maxlength="255" placeholder="Digite o título da tarefa">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="data_tarefa" class="form-label">Data <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="data_tarefa" name="data_tarefa" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3" maxlength="500" placeholder="Descrição detalhada da tarefa (opcional)"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="prioridade" class="form-label">Prioridade <span class="text-danger">*</span></label>
                                    <select class="form-control" id="prioridade" name="prioridade" required>
                                        <option value="alta">Alta</option>
                                        <option value="media" selected>Média</option>
                                        <option value="baixa">Baixa</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="repeticao" class="form-label">Repetição</label>
                                    <select class="form-control" id="repeticao" name="repeticao">
                                        <option value="nenhuma" selected>Nenhuma</option>
                                        <option value="diaria">Diária</option>
                                        <option value="semanal">Semanal</option>
                                        <option value="mensal">Mensal</option>
                                        <option value="dias_uteis">Dias Úteis</option>
                                        <option value="anual">Anual</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="observacoes" class="form-label">Observações</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="2" maxlength="500" placeholder="Observações adicionais (opcional)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-modal">Salvar Tarefa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Função para sair
        function sair() {
            if (confirm('Deseja realmente sair do sistema?')) {
                window.location.href = '../controls/logout.php';
            }
        }

        // Preencher modal de edição
        var modalTarefa = document.getElementById('modalTarefa');
        if (modalTarefa) {
            modalTarefa.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var modalTitle = modalTarefa.querySelector('.modal-title');
                var formAcao = modalTarefa.querySelector('#formAcao');
                var idTarefa = modalTarefa.querySelector('#id_tarefa');
                
                if (button && button.getAttribute('data-id')) {
                    // Modo edição
                    modalTitle.textContent = 'Editar Tarefa';
                    formAcao.value = 'editar';
                    idTarefa.value = button.getAttribute('data-id');
                    document.getElementById('titulo').value = button.getAttribute('data-titulo');
                    document.getElementById('descricao').value = button.getAttribute('data-descricao');
                    document.getElementById('data_tarefa').value = button.getAttribute('data-data_tarefa');
                    document.getElementById('prioridade').value = button.getAttribute('data-prioridade') || 'media';
                    document.getElementById('repeticao').value = button.getAttribute('data-repeticao') || 'nenhuma';
                    document.getElementById('observacoes').value = button.getAttribute('data-observacoes') || '';
                } else {
                    // Modo adição
                    modalTitle.textContent = 'Nova Tarefa';
                    formAcao.value = 'nova';
                    idTarefa.value = '';
                    document.getElementById('titulo').value = '';
                    document.getElementById('descricao').value = '';
                    document.getElementById('data_tarefa').value = '';
                    document.getElementById('prioridade').value = 'media';
                    document.getElementById('repeticao').value = 'nenhuma';
                    document.getElementById('observacoes').value = '';
                    
                    // Definir data padrão como hoje
                    var hoje = new Date().toISOString().split('T')[0];
                    document.getElementById('data_tarefa').value = hoje;
                }
            });
        }

        // Validação do formulário
        document.getElementById('formTarefa').addEventListener('submit', function(e) {
            var titulo = document.getElementById('titulo').value.trim();
            var dataTarefa = document.getElementById('data_tarefa').value;
            
            if (!titulo) {
                e.preventDefault();
                alert('Por favor, preencha o título da tarefa.');
                document.getElementById('titulo').focus();
                return;
            }
            
            if (!dataTarefa) {
                e.preventDefault();
                alert('Por favor, selecione uma data para a tarefa.');
                document.getElementById('data_tarefa').focus();
                return;
            }
        });

        // Auto-focus no título quando o modal abrir
        modalTarefa.addEventListener('shown.bs.modal', function () {
            document.getElementById('titulo').focus();
        });
    </script>
</body>
</html>