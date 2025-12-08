<?php
// views/login.php
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

// Processar login
$mensagem_erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    
    if ($auth->login($email, $senha)) {
        header('Location: ../../index.php');
        exit();
    } else {
        $mensagem_erro = 'E-mail ou senha incorretos!';
    }
}

// Verificar mensagens de erro/sucesso do cadastro
$mensagem_sucesso_cadastro = $_SESSION['sucesso_cadastro'] ?? '';
unset($_SESSION['sucesso_cadastro']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xavier's - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=National+Park&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
    <div class="container-fluid container-principal pagina-login">
        <div class="row h-100">
            <!-- Página de login -->
            <div class="col-12 h-100">
                <div class="row h-100 align-items-center">
                    <!-- Logo -->
                    <div class="col-lg-6 logo-container">
                        <img src="../../public/assets/images/logo.png" alt="Xavier's Logo" class="img-fluid">
                    </div>
                    
                    <!-- Formulário -->
                    <div class="col-lg-6 d-flex justify-content-center align-items-center form-col">
                        <div class="quadrado-container">
                            <div class="quadrado-preto"></div>
                            <div class="quadrado-verde">
                                <h1 class="titulo-rosa">Faça seu login!</h1>
                                
                                <!-- Mensagem de sucesso do cadastro -->
                                <?php if (!empty($mensagem_sucesso_cadastro)): ?>
                                    <div class="alert alert-success">
                                        <?php echo htmlspecialchars($mensagem_sucesso_cadastro); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Mensagem de erro -->
                                <?php if (!empty($mensagem_erro)): ?>
                                    <div class="alert alert-danger">
                                        <?php echo htmlspecialchars($mensagem_erro); ?>
                                    </div>
                                <?php endif; ?>

                                <form action="login.php" method="POST">
                                    <div class="campo-formulario">
                                        <input type="email" id="login-email" placeholder="E-mail" name="email" required
                                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                    </div>
                                    
                                    <div class="campo-formulario">
                                        <input type="password" id="login-senha" placeholder="Senha" name="senha" required>
                                    </div>
                                    
                                    <button type="submit" class="btn-cadastrar" id="btn-login">Entrar</button>
                                </form>
                                

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>