<?php
// views/produtos.php
session_start();
require_once '../models/Database.php';
require_once '../models/Produto.php';
require_once '../models/Auth.php';

// Inicializar models
$database = new Database();
$auth = new Auth($database);
$produto = new Produto($database);

// Verificar se o usuário está logado
$auth->requireAuth();

// Processar busca e filtro de categoria
$termo_busca = '';
$categoria_filtro = 'todos';

if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $termo_busca = trim($_GET['busca']);
}

if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
    $categoria_filtro = trim($_GET['categoria']);
}

// Endpoint JSON (usado pelo modal de edição)
if (isset($_GET['json'])) {
    $id = intval($_GET['json']);
    $produtoData = $produto->buscarPorId($id);
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($produtoData ?? []);
    exit;
}

// Buscar produtos usando POO com filtro de categoria
$produtos = $produto->listarTodos($categoria_filtro);

// Filtrar produtos se houver busca (após a busca do banco)
if (!empty($termo_busca)) {
    $produtos = array_filter($produtos, function($p) use ($termo_busca) {
        return stripos($p['nome'], $termo_busca) !== false || 
               stripos($p['descricao'], $termo_busca) !== false ||
               stripos($p['categoria'], $termo_busca) !== false;
    });
}

// Helper de escape
function e($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Xavier's - Gerenciamento de Produtos</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=National+Park&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <style>
        /* Estilos específicos para a página de produtos */
        .produtos-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
            gap: 20px; 
            margin-top: 20px; 
        }
        
        .produto-card {
            background: #bff6d9; 
            border-radius: 16px; 
            padding: 16px; 
            text-align: center;
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border: 3px solid transparent;
            height: fit-content;
        }
        
        .produto-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
            border-color: #7A06C7;
        }

        .produto-foto {
            width: 100%; 
            height: 180px; 
            background: #f8f9fa; 
            border-radius: 12px; 
            display: flex;
            align-items: center; 
            justify-content: center; 
            color: #666; 
            font-weight: 600; 
            overflow: hidden;
            border: 2px solid #A2F5B8;
        }
        
        .produto-foto img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            border-radius: 10px; 
        }

        .produto-nome {
            color: #FF7300; 
            font-weight: 800;
            text-transform: uppercase;
            font-size: 14px;
            margin: 6px 0 4px 0;
            line-height: 1.3;
            min-height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .produto-categoria .badge {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 10px;
        }

        .produto-valor {
            color: #E03A7F; 
            font-weight: 700;
            font-size: 16px;
        }

        .produto-quantidade {
            color: #7A06C7;
            font-weight: 600;
            font-size: 13px;
            background: rgba(162, 245, 184, 0.3);
            padding: 4px 10px;
            border-radius: 15px;
            width: fit-content;
        }
        
        .produto-descricao {
            color: #666;
            font-size: 12px;
            line-height: 1.3;
            margin: 4px 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 32px;
        }

        .acoes { 
            display: flex; 
            gap: 8px; 
            justify-content: center; 
            margin-top: 10px; 
            width: 100%;
        }
        
        .acao-btn { 
            width: 36px; 
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
            flex: 1;
        }
        
        .acao-btn:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }
        
        .acao-editar { 
            background: #7A06C7; 
        }
        
        .acao-excluir { 
            background: #E03A7F; 
        }

        /* Barra de busca */
        .busca-container {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .busca-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .busca-input {
            flex: 1;
            border-radius: 20px;
            padding: 10px 20px;
            border: 2px solid #A2F5B8;
        }

        .btn-busca {
            background: #7A06C7;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-busca:hover {
            background: #6805ad;
            transform: translateY(-2px);
        }

        .btn-limpar {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 20px;
            font-weight: bold;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-limpar:hover {
            background: #5a6268;
            color: white;
            transform: translateY(-2px);
        }

        /* Estilos para os filtros */
        .filtros-container {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .btn-group .btn {
            border-radius: 20px;
            margin: 0 3px;
            border: 2px solid #7A06C7;
            color: #7A06C7;
            font-weight: 600;
        }

        .btn-group .btn.filtro-ativo {
            background-color: #7A06C7;
            color: white;
            border-color: #7A06C7;
        }

        .btn-group .btn:hover {
            background-color: #7A06C7;
            color: white;
        }

        /* Modal estilizado */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .modal-header {
            background-color: #A2F5B8;
            color: #292627;
            border-radius: 15px 15px 0 0;
            padding: 15px 20px;
            font-weight: bold;
        }

        .modal-title {
            font-weight: bold;
            color: #292627;
        }

        .btn-modal {
            background-color: #7A06C7;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
        }

        .btn-modal:hover {
            background-color: #6805ad;
            color: white;
        }

        .form-control {
            border-radius: 10px;
            padding: 10px;
            border: 2px solid #ced4da;
            font-family: 'National Park', sans-serif;
        }

        .form-control:focus {
            border-color: #7A06C7;
            box-shadow: 0 0 0 0.2rem rgba(122, 6, 199, 0.25);
        }

        .form-label {
            color: #292627;
            font-weight: bold;
            margin-bottom: 8px;
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

        /* Badge para estoque baixo */
        .estoque-baixo {
            background: #E03A7F !important;
            color: white !important;
        }

        /* Resultados da busca */
        .resultado-busca {
            background: #A2F5B8;
            color: #292627;
            padding: 10px 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .produtos-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .produto-card {
                padding: 14px;
            }
            
            .produto-foto {
                height: 160px;
            }

            .busca-form {
                flex-direction: column;
            }

            .busca-input {
                width: 100%;
            }

            .btn-group {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
            }

            .btn-group .btn {
                flex: 1;
                min-width: 80px;
                margin: 2px;
                font-size: 12px;
                padding: 8px 12px;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .produtos-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1025px) {
            .produtos-grid {
                grid-template-columns: repeat(4, 1fr);
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
        <h1 class="titulo-pagina">Gerenciamento de Produtos</h1>
        
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
                <span>Lista de Produtos</span>
                <button class="btn-adicionar" data-bs-toggle="modal" data-bs-target="#modalProduto">
                    <i class="fas fa-plus"></i> Adicionar Produto
                </button>
            </div>
            <div class="card-body">
                <!-- Barra de Busca -->
                <div class="busca-container">
                    <form method="GET" action="produtos.php" class="busca-form">
                        <input type="text" 
                               name="busca" 
                               class="form-control busca-input" 
                               placeholder="Buscar produtos por nome, descrição ou categoria..."
                               value="<?php echo e($termo_busca); ?>">
                        <button type="submit" class="btn-busca">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <?php if (!empty($termo_busca) || $categoria_filtro !== 'todos'): ?>
                            <a href="produtos.php" class="btn-limpar">
                                <i class="fas fa-times"></i> Limpar
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Filtros de Categoria -->
                <div class="filtros-container">
                    <div class="btn-group" role="group">
                        <a href="produtos.php?categoria=todos<?php echo !empty($termo_busca) ? '&busca=' . urlencode($termo_busca) : ''; ?>" 
                           class="btn btn-outline-primary <?php echo $categoria_filtro == 'todos' ? 'filtro-ativo' : ''; ?>">
                            Todos
                        </a>
                        <a href="produtos.php?categoria=Doces<?php echo !empty($termo_busca) ? '&busca=' . urlencode($termo_busca) : ''; ?>" 
                           class="btn btn-outline-primary <?php echo $categoria_filtro == 'Doces' ? 'filtro-ativo' : ''; ?>">
                            Doces
                        </a>
                        <a href="produtos.php?categoria=Brinquedos<?php echo !empty($termo_busca) ? '&busca=' . urlencode($termo_busca) : ''; ?>" 
                           class="btn btn-outline-primary <?php echo $categoria_filtro == 'Brinquedos' ? 'filtro-ativo' : ''; ?>">
                            Brinquedos
                        </a>
                        <a href="produtos.php?categoria=Papelaria<?php echo !empty($termo_busca) ? '&busca=' . urlencode($termo_busca) : ''; ?>" 
                           class="btn btn-outline-primary <?php echo $categoria_filtro == 'Papelaria' ? 'filtro-ativo' : ''; ?>">
                            Papelaria
                        </a>
                        <a href="produtos.php?categoria=Outros<?php echo !empty($termo_busca) ? '&busca=' . urlencode($termo_busca) : ''; ?>" 
                           class="btn btn-outline-primary <?php echo $categoria_filtro == 'Outros' ? 'filtro-ativo' : ''; ?>">
                            Outros
                        </a>
                    </div>
                </div>

                <?php if (!empty($termo_busca) || $categoria_filtro !== 'todos'): ?>
                    <div class="resultado-busca">
                        <i class="fas fa-filter"></i>
                        <?php 
                        $mensagem = '';
                        if (!empty($termo_busca) && $categoria_filtro !== 'todos') {
                            $mensagem = count($produtos) . ' produto(s) encontrado(s) para "' . e($termo_busca) . '" na categoria "' . e($categoria_filtro) . '"';
                        } elseif (!empty($termo_busca)) {
                            $mensagem = count($produtos) . ' produto(s) encontrado(s) para "' . e($termo_busca) . '"';
                        } elseif ($categoria_filtro !== 'todos') {
                            $mensagem = count($produtos) . ' produto(s) na categoria "' . e($categoria_filtro) . '"';
                        }
                        echo $mensagem;
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($produtos)): ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h4>
                            <?php if (!empty($termo_busca) || $categoria_filtro !== 'todos'): ?>
                                Nenhum produto encontrado
                            <?php else: ?>
                                Nenhum produto cadastrado
                            <?php endif; ?>
                        </h4>
                        <p>
                            <?php if (!empty($termo_busca) || $categoria_filtro !== 'todos'): ?>
                                Tente buscar com outros termos ou <a href="produtos.php">ver todos os produtos</a>.
                            <?php else: ?>
                                Comece adicionando seu primeiro produto ao catálogo.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="produtos-grid">
                        <?php foreach ($produtos as $p): ?>
                            <div class="produto-card">
                                <div class="produto-foto">
                                    <?php if (!empty($p['imagem']) && file_exists(__DIR__ . '/../../public/uploads/' . $p['imagem'])): ?>
                                        <img src="../../public/uploads/<?php echo e($p['imagem']); ?>" alt="<?php echo e($p['nome']); ?>">
                                    <?php else: ?>
                                        <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                            <i class="fas fa-image" style="font-size: 28px;"></i>
                                            <span style="font-size: 11px;">SEM IMAGEM</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="produto-nome"><?php echo e($p['nome']); ?></div>
                                
                                <div class="produto-categoria">
                                    <span class="badge bg-<?php 
                                        switch($p['categoria']) {
                                            case 'Doces': echo 'warning'; break;
                                            case 'Brinquedos': echo 'info'; break;
                                            case 'Papelaria': echo 'success'; break;
                                            default: echo 'secondary';
                                        }
                                    ?>"><?php echo e($p['categoria']); ?></span>
                                </div>
                                
                                <?php if (!empty($p['descricao'])): ?>
                                    <div class="produto-descricao"><?php echo e($p['descricao']); ?></div>
                                <?php endif; ?>
                                
                                <div class="produto-valor">R$ <?php echo number_format((float)$p['preco'], 2, ',', '.'); ?></div>
                                
                                <div class="produto-quantidade <?php echo ((int)$p['quantidade'] <= 5) ? 'estoque-baixo' : ''; ?>">
                                    <i class="fas fa-boxes"></i> 
                                    Estoque: <?php echo (int)$p['quantidade']; ?>
                                </div>

                                <div class="acoes">
                                    <button class="acao-btn acao-editar" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalProduto"
                                            data-id="<?php echo $p['id_produto']; ?>"
                                            data-nome="<?php echo e($p['nome']); ?>"
                                            data-descricao="<?php echo e($p['descricao']); ?>"
                                            data-categoria="<?php echo e($p['categoria']); ?>"
                                            data-preco="<?php echo number_format((float)$p['preco'], 2, '.', ''); ?>"
                                            data-quantidade="<?php echo (int)$p['quantidade']; ?>"
                                            data-imagem="<?php echo e($p['imagem']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <a class="acao-btn acao-excluir" 
                                       href="../controls/processa_produto.php?acao=excluir&id=<?php echo (int)$p['id_produto']; ?>" 
                                       onclick="return confirm('Tem certeza que deseja excluir o produto \'<?php echo e($p['nome']); ?>\'?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para Adicionar/Editar Produto -->
    <div class="modal fade" id="modalProduto" tabindex="-1" aria-labelledby="modalProdutoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProdutoLabel">Adicionar Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formProduto" method="post" enctype="multipart/form-data" action="../controls/processa_produto.php">
                    <div class="modal-body">
                        <input type="hidden" name="id_produto" id="id_produto">
                        <input type="hidden" name="foto_atual" id="foto_atual">
                        <input type="hidden" name="acao" id="formAcao" value="novo">
                        
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome do Produto <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nome" name="nome" required maxlength="100" placeholder="Digite o nome do produto">
                        </div>
                        
                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3" maxlength="500" placeholder="Descrição opcional do produto"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="categoria" class="form-label">Categoria <span class="text-danger">*</span></label>
                            <select class="form-control" id="categoria" name="categoria" required>
                                <option value="Doces">Doces</option>
                                <option value="Brinquedos">Brinquedos</option>
                                <option value="Papelaria">Papelaria</option>
                                <option value="Outros">Outros</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="preco" class="form-label">Preço (R$) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="preco" name="preco" step="0.01" min="0" value="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantidade" class="form-label">Quantidade <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="quantidade" name="quantidade" min="0" value="0" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="imagem" class="form-label">Imagem do Produto</label>
                            <input type="file" class="form-control" id="imagem" name="imagem" accept="image/jpeg,image/png,image/gif,image/webp">
                            <div class="form-text">Formatos: JPG, PNG, GIF, WebP (tamanho máximo: 5MB)</div>
                        </div>
                        
                        <div id="imagem-atual" class="mb-3" style="display: none;">
                            <label class="form-label">Imagem Atual</label>
                            <div>
                                <img id="preview-imagem" src="" alt="Imagem atual" style="max-width: 100%; max-height: 200px; border-radius: 8px; object-fit: cover;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-modal">Salvar Produto</button>
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
        var modalProduto = document.getElementById('modalProduto');
        if (modalProduto) {
            modalProduto.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var modalTitle = modalProduto.querySelector('.modal-title');
                var formAcao = modalProduto.querySelector('#formAcao');
                var idProduto = modalProduto.querySelector('#id_produto');
                var fotoAtual = modalProduto.querySelector('#foto_atual');
                var imagemAtualDiv = document.getElementById('imagem-atual');
                var previewImagem = document.getElementById('preview-imagem');
                
                if (button && button.getAttribute('data-id')) {
                    // Modo edição
                    modalTitle.textContent = 'Editar Produto';
                    formAcao.value = 'editar';
                    idProduto.value = button.getAttribute('data-id');
                    document.getElementById('nome').value = button.getAttribute('data-nome');
                    document.getElementById('descricao').value = button.getAttribute('data-descricao');
                    document.getElementById('categoria').value = button.getAttribute('data-categoria') || 'Outros';
                    document.getElementById('preco').value = button.getAttribute('data-preco');
                    document.getElementById('quantidade').value = button.getAttribute('data-quantidade');
                    
                    // Tratar imagem atual
                    var imagem = button.getAttribute('data-imagem');
                    fotoAtual.value = imagem;
                    
                    if (imagem) {
                        imagemAtualDiv.style.display = 'block';
                        previewImagem.src = '../../public/uploads/' + imagem;
                        previewImagem.style.height = '200px'; // Imagem mais comprida
                    } else {
                        imagemAtualDiv.style.display = 'none';
                    }
                } else {
                    // Modo adição
                    modalTitle.textContent = 'Adicionar Produto';
                    formAcao.value = 'novo';
                    idProduto.value = '';
                    fotoAtual.value = '';
                    document.getElementById('nome').value = '';
                    document.getElementById('descricao').value = '';
                    document.getElementById('categoria').value = 'Outros';
                    document.getElementById('preco').value = '0.00';
                    document.getElementById('quantidade').value = '0';
                    document.getElementById('imagem').value = '';
                    imagemAtualDiv.style.display = 'none';
                }
            });
        }

        // Validação de preço
        document.getElementById('preco').addEventListener('blur', function() {
            let value = parseFloat(this.value);
            if (value < 0) this.value = '0.00';
            if (value > 0) this.value = value.toFixed(2);
        });

        // Validação de quantidade
        document.getElementById('quantidade').addEventListener('blur', function() {
            let value = parseInt(this.value);
            if (value < 0) this.value = '0';
        });

        // Preview de imagem ao selecionar
        document.getElementById('imagem').addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagem-atual').style.display = 'block';
                    document.getElementById('preview-imagem').src = e.target.result;
                    document.getElementById('preview-imagem').style.height = '200px'; // Imagem mais comprida
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>