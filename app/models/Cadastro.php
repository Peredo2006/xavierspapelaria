<?php
// views/cadastro.php
session_start();
require_once '../models/Database.php';
require_once '../models/Auth.php';

// Inicializar classes
$database = new Database();
$auth = new Auth($database);

// Verificar se já está logado (redirecionar se sim)
if ($auth->isLoggedIn()) {
    header('Location: ../../index.php');
    exit();
}

// Verificar mensagens de erro/sucesso
$mensagem_erro = $_SESSION['erro_cadastro'] ?? '';
$mensagem_sucesso = $_SESSION['sucesso_cadastro'] ?? '';

// Limpar mensagens após exibir
unset($_SESSION['erro_cadastro'], $_SESSION['sucesso_cadastro']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xavier's - Cadastro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=National+Park&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
    <div class="container-fluid container-principal pagina-login">
        <div class="row h-100">
            <!-- Página de cadastro -->
            <div class="col-12 h-100">
                <div class="row h-100 align-items-center">
                    <!-- Formulário -->
                    <div class="col-lg-6 d-flex justify-content-center align-items-center form-col">
                        <div class="quadrado-container">
                            <div class="quadrado-preto"></div>
                            <div class="quadrado-verde">
                                <h1 class="titulo-rosa">Crie sua conta!</h1>
                                
                                <!-- Mensagem de sucesso -->
                                <?php if (!empty($mensagem_sucesso)): ?>
                                    <div class="alert alert-success">
                                        <?php echo htmlspecialchars($mensagem_sucesso); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Mensagem de erro -->
                                <?php if (!empty($mensagem_erro)): ?>
                                    <div class="alert alert-danger">
                                        <?php echo htmlspecialchars($mensagem_erro); ?>
                                    </div>
                                <?php endif; ?>

                                <form action="../models/processa_cadastro.php" method="POST">
                                    <div class="campo-formulario">
                                        <input type="text" id="cadastro-nome" placeholder="Nome completo" name="nome" required
                                            value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>">
                                    </div>
                                    
                                    <div class="campo-formulario">
                                        <input type="email" id="cadastro-email" placeholder="E-mail" name="email" required
                                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                    </div>
                                    
                                    <div class="campo-formulario">
                                        <input type="password" id="cadastro-senha" placeholder="Senha" name="senha" required minlength="6">
                                    </div>
                                    
                                    <div class="campo-formulario">
                                        <input type="password" id="cadastro-confirmar-senha" placeholder="Confirmar senha" name="confirmar_senha" required>
                                    </div>
                                    
                                    <button type="submit" class="btn-cadastrar" id="btn-cadastrar">Cadastrar</button>
                                </form>
                                
                                <p class="texto-login">
                                    Já possui conta? <br> 
                                    <a href="login.php" class="link-roxo">Faça o seu login!</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Logo -->
                    <div class="col-lg-6 logo-container">
                        <img src="../../public/assets/images/logo.png" alt="Xavier's Logo" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação de senha no frontend
        document.querySelector('form').addEventListener('submit', function(e) {
            const senha = document.getElementById('cadastro-senha').value;
            const confirmarSenha = document.getElementById('cadastro-confirmar-senha').value;
            
            if (senha !== confirmarSenha) {
                e.preventDefault();
                alert('As senhas não coincidem!');
                return false;
            }
            
            if (senha.length < 4) {
                e.preventDefault();
                alert('A senha deve ter pelo menos 4 caracteres!');
                return false;
            }
        });
    </script>
</body>
</html>