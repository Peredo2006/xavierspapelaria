<?php
// views/usuarios.php
session_start();
require_once '../models/Database.php';
require_once '../models/Usuario.php';
require_once '../models/Auth.php';

// Inicializar models
$database = new Database();
$auth = new Auth($database);

// Verificar se o usuário está logado
$auth->requireAuth();

// Verificar se é gerente
$is_gerente = $auth->isAdmin();

// Processar exclusão de usuário (apenas gerentes)
if ($is_gerente && isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);
    $usuario = new Usuario($database);
    
    // Não permitir que o usuário exclua a si mesmo
    if ($id_excluir != $_SESSION['user_id']) {
        if ($usuario->excluir($id_excluir)) {
            $_SESSION['sucesso'] = 'Funcionário excluído com sucesso!';
        } else {
            $_SESSION['erro'] = 'Erro ao excluir funcionário.';
        }
    } else {
        $_SESSION['erro'] = 'Você não pode excluir a si mesmo!';
    }
    
    header('Location: usuarios.php');
    exit();
}

// Buscar usuários do banco de dados usando POO
$usuario = new Usuario($database);
$usuarios = $usuario->listarTodos();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xavier's - Gerenciamento de Funcionários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=National+Park&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/assets/css/style.css">
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
        <h1 class="titulo-pagina">Gerenciamento de Funcionários</h1>
        
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
                <span>Lista de Funcionários</span>
                <?php if ($is_gerente): ?>
                    <button class="btn-adicionar" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                        <i class="fas fa-plus"></i> Adicionar Funcionário
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Tipo</th>
                                <th>Data de Cadastro</th>
                                <?php if ($is_gerente): ?>
                                    <th>Ações</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="<?php echo $is_gerente ? '6' : '5'; ?>" class="text-center text-muted">
                                        Nenhum funcionário cadastrado
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usuarios as $usuario_item): ?>
                                    <tr>
                                        <td><?php echo $usuario_item['id_usuario']; ?></td>
                                        <td><?php echo htmlspecialchars($usuario_item['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario_item['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $usuario_item['tipo'] === 'Gerente' ? 'warning' : 'info'; ?>">
                                                <?php echo htmlspecialchars($usuario_item['tipo']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($usuario_item['data_cadastro'])); ?></td>
                                        <?php if ($is_gerente): ?>
                                            <td>
                                                <button class="btn-acao btn-editar" data-bs-toggle="modal" data-bs-target="#modalUsuario" 
                                                    data-id="<?php echo $usuario_item['id_usuario']; ?>"
                                                    data-nome="<?php echo htmlspecialchars($usuario_item['nome']); ?>"
                                                    data-email="<?php echo htmlspecialchars($usuario_item['email']); ?>"
                                                    data-tipo="<?php echo htmlspecialchars($usuario_item['tipo']); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($usuario_item['id_usuario'] != $_SESSION['user_id']): ?>
                                                    <button class="btn-acao btn-excluir" 
                                                        onclick="confirmarExclusao(<?php echo $usuario_item['id_usuario']; ?>, '<?php echo htmlspecialchars($usuario_item['nome']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn-acao btn-excluir" disabled title="Não é possível excluir a si mesmo">
                                                        <i class="fas fa-trash text-muted"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para adicionar/editar usuário (apenas para gerentes) -->
    <?php if ($is_gerente): ?>
    <div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUsuarioLabel">Adicionar Funcionário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formUsuario" action="../controls/processa_usuario.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="id_usuario" name="id_usuario">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nome" name="nome" placeholder="Digite o nome completo" required
                                minlength="2" maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Digite o e-mail" required
                                maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select class="form-control" id="tipo" name="tipo" required>
                                <option value="">Selecione um tipo</option>
                                <option value="Gerente">Gerente</option>
                                <option value="Vendedor">Vendedor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha <span class="text-danger" id="senha-required">*</span></label>
                            <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite a senha"
                                minlength="4">
                            <small class="form-text text-muted">Mínimo 4 caracteres. Deixe em branco para manter a senha atual (ao editar)</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirmarSenha" class="form-label">Confirmar Senha</label>
                            <input type="password" class="form-control" id="confirmarSenha" name="confirmarSenha" placeholder="Confirme a senha">
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
    <?php endif; ?>

    <!-- Scripts Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Função para confirmar exclusão
        function confirmarExclusao(id, nome) {
            if (confirm('Tem certeza que deseja excluir o funcionário "' + nome + '"?')) {
                window.location.href = 'usuarios.php?excluir=' + id;
            }
        }
        
        // Função para o botão Sair
        function sair() {
            if (confirm('Deseja realmente sair do sistema?')) {
                window.location.href = '../controls/logout.php';
            }
        }

        // Preencher modal de edição
        var modalUsuario = document.getElementById('modalUsuario');
        if (modalUsuario) {
            modalUsuario.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var modalTitle = modalUsuario.querySelector('.modal-title');
                var modalBodyInputId = modalUsuario.querySelector('#id_usuario');
                var modalBodyInputNome = modalUsuario.querySelector('#nome');
                var modalBodyInputEmail = modalUsuario.querySelector('#email');
                var modalBodyInputTipo = modalUsuario.querySelector('#tipo');
                var senhaRequired = modalUsuario.querySelector('#senha-required');
                
                if (button.getAttribute('data-id')) {
                    // Modo edição
                    modalTitle.textContent = 'Editar Funcionário';
                    modalBodyInputId.value = button.getAttribute('data-id');
                    modalBodyInputNome.value = button.getAttribute('data-nome');
                    modalBodyInputEmail.value = button.getAttribute('data-email');
                    modalBodyInputTipo.value = button.getAttribute('data-tipo');
                    senhaRequired.classList.remove('text-danger');
                    senhaRequired.classList.add('text-muted');
                } else {
                    // Modo adição
                    modalTitle.textContent = 'Adicionar Funcionário';
                    modalBodyInputId.value = '';
                    modalBodyInputNome.value = '';
                    modalBodyInputEmail.value = '';
                    modalBodyInputTipo.value = '';
                    senhaRequired.classList.remove('text-muted');
                    senhaRequired.classList.add('text-danger');
                }
                
                // Limpar campos de senha
                document.getElementById('senha').value = '';
                document.getElementById('confirmarSenha').value = '';
            });
        }

        // Validação de senha
        var formUsuario = document.getElementById('formUsuario');
        if (formUsuario) {
            formUsuario.addEventListener('submit', function(e) {
                var idUsuario = document.getElementById('id_usuario').value;
                var senha = document.getElementById('senha').value;
                var confirmarSenha = document.getElementById('confirmarSenha').value;
                
                // Se for novo usuário, senha é obrigatória
                if (!idUsuario && senha === '') {
                    e.preventDefault();
                    alert('A senha é obrigatória para novo funcionário!');
                    return false;
                }
                
                // Se informou senha, deve ter pelo menos 4 caracteres
                if (senha !== '' && senha.length < 4) {
                    e.preventDefault();
                    alert('A senha deve ter pelo menos 4 caracteres!');
                    return false;
                }
                
                // Verificar se as senhas coincidem
                if (senha !== confirmarSenha) {
                    e.preventDefault();
                    alert('As senhas não coincidem!');
                    return false;
                }
            });
        }
    </script>
</body>
</html>