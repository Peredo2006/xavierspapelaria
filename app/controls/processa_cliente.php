<?php
// models/processa_cliente.php
session_start();
require_once '../models/Database.php';
require_once '../models/Cliente.php';
require_once '../models/HistoricoCliente.php';

// Inicializar models
$database = new Database();
$cliente = new Cliente($database);
$historico = new HistoricoCliente($database);

// Determinar o tipo de resposta
$is_ajax = isset($_GET['acao']) || (isset($_POST['acao']) && in_array($_POST['acao'], ['buscar', 'detalhes']));

if ($is_ajax) {
    header('Content-Type: application/json');
}

$acao = $_GET['acao'] ?? ($_POST['acao'] ?? '');

// Buscar cliente individual
if ($acao === 'buscar' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $cliente_data = $cliente->buscarPorId($id);
    
    echo json_encode([
        'success' => !!$cliente_data,
        'cliente' => $cliente_data ?: null
    ]);
    exit;
}

// Buscar detalhes do cliente com histórico
if ($acao === 'detalhes' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Buscar dados do cliente
    $cliente_data = $cliente->buscarPorId($id);
    
    // Buscar histórico
    $historico_data = $historico->buscarPorCliente($id, 10);
    
    if ($cliente_data) {
        ob_start();
        ?>
        <form id="formDetalhes" action="../controls/processa_cliente.php" method="POST">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id_cliente" value="<?php echo $cliente_data['id_cliente']; ?>">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nome Completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nome" value="<?php echo htmlspecialchars($cliente_data['nome']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">CPF <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="cpf" value="<?php echo htmlspecialchars($cliente_data['cpf']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Telefone <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="telefone" value="<?php echo htmlspecialchars($cliente_data['telefone']); ?>" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Valor Devido (R$) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="limite_credito" 
                               value="<?php echo number_format($cliente_data['limite_credito'], 2, '.', ''); ?>" 
                               step="0.01" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Data de Cadastro</label>
                        <input type="text" class="form-control" value="<?php echo date('d/m/Y H:i', strtotime($cliente_data['data_cadastro'])); ?>" readonly>
                    </div>
                    
                    <?php if (!empty($cliente_data['telefone'])): ?>
                    <div class="mb-3">
                        <label class="form-label">WhatsApp</label>
                        <br>
                        <a href="https://wa.me/55<?php echo preg_replace('/[^0-9]/', '', $cliente_data['telefone']); ?>" 
                           target="_blank" class="btn btn-success">
                            <i class="fab fa-whatsapp"></i> Enviar Mensagem
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Endereço</label>
                <textarea class="form-control" name="endereco" rows="3"><?php echo htmlspecialchars($cliente_data['endereco'] ?? ''); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Observação para Histórico (opcional)</label>
                <textarea class="form-control" name="observacao" rows="2" placeholder="Ex: Cliente pagou parte da dívida..."></textarea>
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </form>
        
        <hr>
        
        <h6>Histórico de Alterações</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Valor Anterior</th>
                        <th>Valor Atual</th>
                        <th>Observação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($historico_data)): ?>
                        <?php foreach ($historico_data as $hist): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($hist['data_alteracao'])); ?></td>
                            <td>R$ <?php echo number_format($hist['valor_anterior'], 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($hist['valor_atual'], 2, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($hist['observacao']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">Nenhum registro no histórico</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
        $html = ob_get_clean();
        
        echo json_encode([
            'success' => true,
            'html' => $html
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'html' => '<p>Cliente não encontrado.</p>'
        ]);
    }
    exit;
}

// Processar ações principais
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = intval($_POST['id_cliente'] ?? 0);
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'] ?? '';
    $limite_credito = floatval($_POST['limite_credito']);
    $observacao = $_POST['observacao'] ?? '';

    if ($acao === 'novo') {
        // Verificar se CPF já existe
        if (!$cliente->verificarCpfUnico($cpf)) {
            $_SESSION['erro'] = 'CPF já cadastrado no sistema!';
            header('Location: ../views/clientes.php');
            exit;
        }
        
        // Criar novo cliente
        $dados = [
            'nome' => $nome,
            'cpf' => $cpf,
            'telefone' => $telefone,
            'endereco' => $endereco,
            'limite_credito' => $limite_credito
        ];
        
        $novo_id = $cliente->criar($dados);
        
        if ($novo_id) {
            // Registrar no histórico
            $historico->registrar($novo_id, 0, $limite_credito, 'Cadastro inicial');
            $_SESSION['sucesso'] = 'Cliente cadastrado com sucesso!';
        } else {
            $_SESSION['erro'] = 'Erro ao cadastrar cliente.';
        }
        
    } elseif ($acao === 'editar') {
        // Buscar dados antigos
        $cliente_antigo = $cliente->buscarPorId($id_cliente);
        
        // Verificar se CPF já existe (excluindo o próprio cliente)
        if (!$cliente->verificarCpfUnico($cpf, $id_cliente)) {
            $_SESSION['erro'] = 'CPF já cadastrado no sistema!';
            header('Location: ../views/clientes.php');
            exit;
        }
        
        // Atualizar cliente
        $dados = [
            'nome' => $nome,
            'cpf' => $cpf,
            'telefone' => $telefone,
            'endereco' => $endereco,
            'limite_credito' => $limite_credito
        ];
        
        if ($cliente->atualizar($id_cliente, $dados)) {
            // Registrar no histórico se o limite mudou
            if ($cliente_antigo && $cliente_antigo['limite_credito'] != $limite_credito) {
                $historico->registrar($id_cliente, $cliente_antigo['limite_credito'], $limite_credito, $observacao);
            }
            $_SESSION['sucesso'] = 'Cliente atualizado com sucesso!';
        } else {
            $_SESSION['erro'] = 'Erro ao atualizar cliente.';
        }
    }
    
    // Redirecionar de volta para a página de clientes
    header('Location: ../views/clientes.php');
    exit;
}

// Redirecionar se nenhuma ação válida
header('Location: ../views/clientes.php');
exit;
?>