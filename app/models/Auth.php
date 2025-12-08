<?php
// classes/Auth.php
require_once 'Usuario.php';

class Auth {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function login($email, $senha) {
        $usuario = new Usuario($this->db);
        $userData = $usuario->buscarPorEmail($email);
        
        if ($userData && password_verify($senha, $userData['senha'])) {
            // Configurar sessão
            $_SESSION['user_id'] = $userData['id_usuario'];
            $_SESSION['user_name'] = htmlspecialchars($userData['nome']);
            $_SESSION['user_email'] = $userData['email'];
            $_SESSION['user_type'] = $userData['tipo'];
            
            // Atualizar último login
            $usuario->atualizarUltimoLogin($userData['id_usuario']);
            
            // Regenerar ID da sessão para segurança
            session_regenerate_id(true);
            
            return true;
        }
        
        return false;
    }
    
    public function logout() {
        // Limpar todas as variáveis de sessão
        $_SESSION = [];

        // Configurar parâmetros do cookie de sessão
        $cookieParams = session_get_cookie_params();

        // Expirar o cookie de sessão
        setcookie(
            session_name(),           // Nome do cookie de sessão
            '',                       // Valor vazio
            time() - 3600,           // Tempo no passado (expira)
            $cookieParams['path'],    // Caminho
            $cookieParams['domain'],  // Domínio
            $cookieParams['secure'],  // Seguro (HTTPS)
            $cookieParams['httponly'] // Apenas HTTP
        );

        // Destruir a sessão
        session_destroy();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function getUser() {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'nome' => $_SESSION['user_name'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'tipo' => $_SESSION['user_type'] ?? null
        ];
    }
    
    public function isAdmin() {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Gerente';
    }
    
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: ../views/login.php');
            exit;
        }
    }
    
    public function requireAdmin() {
        $this->requireAuth();
        
        if (!$this->isAdmin()) {
            header('Location: ../views/index.php');
            exit;
        }
    }
}
?>