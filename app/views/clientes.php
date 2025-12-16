<?php
// views/clientes.php
session_start();
require_once '../models/Database.php';
require_once '../models/Cliente.php';
require_once '../models/HistoricoCliente.php';
require_once '../models/Auth.php';

// Inicializar models
$database = new Database();
$auth = new Auth($database);
$cliente = new Cliente($database);
$historico = new HistoricoCliente($database);

// Verificar se o usuário está logado
$auth->requireAuth();

// Processar filtro
$filtro = $_GET['filtro'] ?? 'todos';

// Buscar clientes do banco de dados usando POO
$clientes = $cliente->listarTodos($filtro);

// Processar atualização rápida de limite
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_limite'])) {
    $id_cliente = intval($_POST['id_cliente']);
    $novo_limite = floatval($_POST['novo_limite']);
    $observacao = $_POST['observacao'] ?? 'Atualização rápida via tabela';
    
    // Buscar valor anterior
    $cliente_antigo = $cliente->buscarPorId($id_cliente);
    $valor_anterior = $cliente_antigo ? $cliente_antigo['limite_credito'] : 0;
    
    // Atualizar limite
    if ($cliente->atualizarLimite($id_cliente, $novo_limite)) {
        // Registrar no histórico se o valor mudou
        if ($valor_anterior != $novo_limite) {
            $historico->registrar($id_cliente, $valor_anterior, $novo_limite, $observacao);
        }
        $_SESSION['sucesso'] = 'Valor atualizado com sucesso!';
    } else {
        $_SESSION['erro'] = 'Erro ao atualizar valor.';
    }
    
    header('Location: clientes.php?filtro=' . $filtro);
    exit();
}

// Processar exclusão de cliente
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);
    
    try {
        // Primeiro tentar excluir o histórico (se a tabela existir)
        $historico->excluirPorCliente($id_excluir);
        
        // Depois excluir o cliente
        if ($cliente->excluir($id_excluir)) {
            $_SESSION['sucesso'] = 'Cliente excluído com sucesso!';
        } else {
            $_SESSION['erro'] = 'Erro ao excluir cliente.';
        }
    } catch (Exception $e) {
        // Se houver erro, tenta excluir apenas o cliente
        if ($cliente->excluir($id_excluir)) {
            $_SESSION['sucesso'] = 'Cliente excluído com sucesso! (Histórico não pôde ser removido)';
        } else {
            $_SESSION['erro'] = 'Erro ao excluir cliente.';
        }
    }
    
    header('Location: clientes.php?filtro=' . $filtro);
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xavier's - Clientes Devedores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=National+Park&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <style>
        .btn-whatsapp {
            background-color: #25D366;
            color: white;
        }
        
        .btn-whatsapp:hover {
            background-color: #128C7E;
            color: white;
        }
        
        .btn-detalhes {
            background-color: #FF7300;
            color: white;
        }
        
        .btn-detalhes:hover {
            background-color: #e56500;
            color: white;
        }
        
        .filtro-ativo {
            background-color: #7A06C7 !important;
            color: white !important;
        }
        
        .valor-input {
            width: 100px;
            display: inline-block;
            margin-right: 10px;
        }
        
        .form-rapido {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .badge-valor {
            font-size: 0.9rem;
            padding: 6px 12px;
        }

        /* Estilos para a página de clientes */
        .header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo img {
            height: 40px;
        }

        .btn-sair {
            background-color: #FF7300;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: bold;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-sair:hover {
            background-color: #e56500;
            transform: translateY(-2px);
            color: white;
        }

        .btn-acao {
            padding: 8px 12px;
            margin: 0 2px;
            border-radius: 8px;
            border: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
        }

        .btn-editar {
            background-color: #7A06C7;
            color: white;
        }

        .btn-excluir {
            background-color: #E03A7F;
            color: white;
        }

        .btn-whatsapp {
            background-color: #25D366;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 8px;
        }

        .btn-detalhes {
            background-color: #FF7300;
            color: white;
        }

        .btn-acao:hover, .btn-whatsapp:hover {
            opacity: 0.9;
            transform: scale(1.05);
            color: white;
        }

        /* Melhorar a aparência dos filtros */
        .btn-group .btn {
            border-radius: 20px;
            margin: 0 5px;
            border: 2px solid #7A06C7;
            color: #7A06C7;
        }

        .btn-group .btn.filtro-ativo {
            background-color: #7A06C7;
            color: white;
        }

        .btn-group .btn:hover {
            background-color: #7A06C7;
        }

        /* Melhorar a tabela */
        .table th {
            background-color: #f8f9fa;
            color: #292627;
            font-weight: bold;
            border-bottom: 2px solid #A2F5B8;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(162, 245, 184, 0.1);
        }

        .valor-input {
            width: 120px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 5px 10px;
        }

        .form-rapido {
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
        <h1 class="titulo-pagina">Clientes Devedores</h1>
        
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
        
        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-header">
                <span>Filtrar Clientes</span>
            </div>
            <div class="card-body">
                <div class="btn-group" role="group">
                    <a href="clientes.php?filtro=todos" 
                    class="btn btn-outline-primary <?php echo $filtro == 'todos' ? 'filtro-ativo' : ''; ?>">
                        Todos
                    </a>
                    <a href="clientes.php?filtro=recentes" 
                    class="btn btn-outline-primary <?php echo $filtro == 'recentes' ? 'filtro-ativo' : ''; ?>">
                        Clientes Recentes
                    </a>
                    <a href="clientes.php?filtro=ate_20" 
                    class="btn btn-outline-primary <?php echo $filtro == 'ate_20' ? 'filtro-ativo' : ''; ?>">
                        Até R$ 20
                    </a>
                    <a href="clientes.php?filtro=20_a_50" 
                    class="btn btn-outline-primary <?php echo $filtro == '20_a_50' ? 'filtro-ativo' : ''; ?>">
                        R$ 20 a R$ 50
                    </a>
                    <a href="clientes.php?filtro=mais_50" 
                    class="btn btn-outline-primary <?php echo $filtro == 'mais_50' ? 'filtro-ativo' : ''; ?>">
                        Mais de R$ 50
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <span>Lista de Clientes Devedores</span>
                <button class="btn-adicionar" data-bs-toggle="modal" data-bs-target="#modalCliente">
                    <i class="fas fa-plus"></i> Adicionar Cliente
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>Telefone</th>
                                <th>Valor Devido</th>
                                <th>Data Cadastro</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($clientes)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        Nenhum cliente encontrado
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($clientes as $cliente_item): ?>
                                    <tr>
                                        <td><?php echo $cliente_item['id_cliente']; ?></td>
                                        <td><?php echo htmlspecialchars($cliente_item['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($cliente_item['cpf']); ?></td>
                                        <td><?php echo htmlspecialchars($cliente_item['telefone']); ?></td>
                                        <td>
                                            <form method="POST" class="form-rapido" onsubmit="return validarValor(this)">
                                                <input type="hidden" name="id_cliente" value="<?php echo $cliente_item['id_cliente']; ?>">
                                                <input type="number" name="novo_limite" class="form-control valor-input" 
                                                    value="<?php echo number_format($cliente_item['limite_credito'], 2, '.', ''); ?>" 
                                                    step="0.01" min="0" required>
                                                <input type="hidden" name="atualizar_limite" value="1">
                                                <button type="submit" class="btn-acao btn-editar" title="Atualizar valor">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($cliente_item['data_cadastro'])); ?></td>
                                        <td>
                                            <?php if (!empty($cliente_item['telefone'])): ?>
                                                <a href="https://wa.me/55<?php echo preg_replace('/[^0-9]/', '', $cliente_item['telefone']); ?>" 
                                                target="_blank" class="btn-acao btn-whatsapp" title="Enviar WhatsApp">
                                                    <i class="fab fa-whatsapp"></i>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn-acao btn-whatsapp" disabled title="Sem telefone">
                                                    <i class="fab fa-whatsapp"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button class="btn-acao btn-detalhes" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalDetalhes"
                                                    data-id="<?php echo $cliente_item['id_cliente']; ?>">
                                                <i class="fas fa-info-circle"></i>
                                            </button>
                                            
                                            <button class="btn-acao btn-excluir" 
                                                    onclick="confirmarExclusao(<?php echo $cliente_item['id_cliente']; ?>, '<?php echo htmlspecialchars($cliente_item['nome']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Adicionar/Editar Cliente -->
    <div class="modal fade" id="modalCliente" tabindex="-1" aria-labelledby="modalClienteLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalClienteLabel">Adicionar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formCliente" action="../controls/processa_cliente.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="id_cliente" name="id_cliente">
                        <input type="hidden" name="acao" id="formAcao" value="novo">
                        
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cpf" class="form-label">CPF <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cpf" name="cpf">
                        </div>
                        
                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone/WhatsApp <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="telefone" name="telefone" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="endereco" class="form-label">Endereço</label>
                            <textarea class="form-control" id="endereco" name="endereco" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="limite_credito" class="form-label">Valor Devido (R$) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="limite_credito" name="limite_credito" 
                                   step="0.01" min="0" value="0.00" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-modal">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Detalhes do Cliente -->
    <div class="modal fade" id="modalDetalhes" tabindex="-1" aria-labelledby="modalDetalhesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetalhesLabel">Detalhes do Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detalhesCliente">
                    <!-- Conteúdo carregado via JavaScript -->
                </div>
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

        // Função para confirmar exclusão
        function confirmarExclusao(id, nome) {
            if (confirm('Tem certeza que deseja excluir o cliente "' + nome + '"?')) {
                window.location.href = 'clientes.php?excluir=' + id;
            }
        }

        // Validar valor antes de enviar
        function validarValor(form) {
            const valor = parseFloat(form.novo_limite.value);
            if (valor < 0) {
                alert('O valor não pode ser negativo!');
                return false;
            }
            
            // Adicionar observação automática
            const observacaoInput = document.createElement('input');
            observacaoInput.type = 'hidden';
            observacaoInput.name = 'observacao';
            observacaoInput.value = 'Atualização rápida via tabela';
            form.appendChild(observacaoInput);
            
            return true;
        }

        // Preencher modal de edição
        var modalCliente = document.getElementById('modalCliente');
        if (modalCliente) {
            modalCliente.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var modalTitle = modalCliente.querySelector('.modal-title');
                var formAcao = modalCliente.querySelector('#formAcao');
                var idCliente = modalCliente.querySelector('#id_cliente');
                
                if (button && button.getAttribute('data-id')) {
                    // Modo edição - carregar dados via AJAX
                    const id = button.getAttribute('data-id');
                    carregarDadosCliente(id);
                    modalTitle.textContent = 'Editar Cliente';
                    formAcao.value = 'editar';
                } else {
                    // Modo adição
                    modalTitle.textContent = 'Adicionar Cliente';
                    formAcao.value = 'novo';
                    idCliente.value = '';
                    document.getElementById('nome').value = '';
                    document.getElementById('cpf').value = '';
                    document.getElementById('telefone').value = '';
                    document.getElementById('endereco').value = '';
                    document.getElementById('limite_credito').value = '0.00';
                }
            });
        }

        // Carregar detalhes do cliente
        var modalDetalhes = document.getElementById('modalDetalhes');
        if (modalDetalhes) {
            modalDetalhes.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var idCliente = button.getAttribute('data-id');
                carregarDetalhesCliente(idCliente);
            });
        }

        // Função para carregar dados do cliente via AJAX
        function carregarDadosCliente(id) {
            fetch(`../controls/processa_cliente.php?acao=buscar&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('id_cliente').value = data.cliente.id_cliente;
                        document.getElementById('nome').value = data.cliente.nome;
                        document.getElementById('cpf').value = data.cliente.cpf;
                        document.getElementById('telefone').value = data.cliente.telefone;
                        document.getElementById('endereco').value = data.cliente.endereco || '';
                        document.getElementById('limite_credito').value = data.cliente.limite_credito;
                    }
                })
                .catch(error => console.error('Erro:', error));
        }

        // Função para carregar detalhes do cliente
        function carregarDetalhesCliente(id) {
            fetch(`../controls/processa_cliente.php?acao=detalhes&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('detalhesCliente').innerHTML = data.html;
                    } else {
                        document.getElementById('detalhesCliente').innerHTML = '<p>Erro ao carregar detalhes.</p>';
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    document.getElementById('detalhesCliente').innerHTML = '<p>Erro ao carregar detalhes.</p>';
                });
        }

        // Máscaras para CPF e Telefone
        document.addEventListener('DOMContentLoaded', function() {
            // Máscara para CPF
            const cpfInput = document.getElementById('cpf');
            if (cpfInput) {
                cpfInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length <= 11) {
                        value = value.replace(/(\d{3})(\d)/, '$1.$2');
                        value = value.replace(/(\d{3})(\d)/, '$1.$2');
                        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                    }
                    e.target.value = value;
                });
            }

            // Máscara para Telefone
            const telInput = document.getElementById('telefone');
            if (telInput) {
                telInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length <= 11) {
                        value = value.replace(/(\d{2})(\d)/, '($1) $2');
                        value = value.replace(/(\d{5})(\d)/, '$1-$2');
                    }
                    e.target.value = value;
                });
            }
        });
    </script>
</body>

</html>
