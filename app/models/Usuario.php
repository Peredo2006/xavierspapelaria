<?php
// models/Usuario.php
class Usuario {
    private $db;
    private $id;
    private $nome;
    private $email;
    private $senha;
    private $tipo;
    private $data_cadastro;
    
    public function __construct($db) {
        $this->db = $db;
    }

    // Método para buscar usuário por email (para login)
    public function buscarPorEmail($email) {
        $sql = "SELECT id_usuario, nome, email, senha, tipo FROM usuarios WHERE email = ? AND ativo = 1";
        $stmt = $this->db->executeQuery($sql, [$email]);
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

    // Método para atualizar último login
    public function atualizarUltimoLogin($id) {
        try {
            // Verificar se a coluna ultimo_login existe
            $sql = "SHOW COLUMNS FROM usuarios LIKE 'ultimo_login'";
            $stmt = $this->db->executeQuery($sql);
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // A coluna existe, podemos atualizar
                $sql = "UPDATE usuarios SET ultimo_login = NOW() WHERE id_usuario = ?";
                $stmt = $this->db->executeQuery($sql, [$id]);
                return $stmt->affected_rows;
            } else {
                // A coluna não existe, apenas retorna sucesso
                return 1;
            }
        } catch (Exception $e) {
            error_log("Erro ao atualizar último login: " . $e->getMessage());
            return 0;
        }
    }

    // Método para registrar logout (corrigido)
    public function registrarLogout($userId) {
        try {
            // Verificar se a coluna ultimo_logout existe
            $sql = "SHOW COLUMNS FROM usuarios LIKE 'ultimo_logout'";
            $stmt = $this->db->executeQuery($sql);
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // A coluna existe, podemos atualizar
                $sql = "UPDATE usuarios SET ultimo_logout = NOW() WHERE id_usuario = ?";
                $stmt = $this->db->executeQuery($sql, [$userId]);
                return $stmt->affected_rows;
            } else {
                // A coluna não existe, apenas retorna sucesso
                return 1;
            }
        } catch (Exception $e) {
            // Em caso de erro, apenas registra e continua
            error_log("Erro ao registrar logout: " . $e->getMessage());
            return 0;
        }
    }
    
    // Getters e Setters
    public function getId() { return $this->id; }
    public function getNome() { return $this->nome; }
    public function getEmail() { return $this->email; }
    public function getTipo() { return $this->tipo; }
    public function getDataCadastro() { return $this->data_cadastro; }
    
    public function setNome($nome) { $this->nome = $nome; }
    public function setEmail($email) { $this->email = $email; }
    public function setSenha($senha) { 
        $this->senha = password_hash($senha, PASSWORD_DEFAULT); 
    }
    public function setTipo($tipo) { $this->tipo = $tipo; }
    
    // Métodos de autenticação
    public function login($email, $senha) {
        $sql = "SELECT id_usuario, nome, email, senha, tipo FROM usuarios WHERE email = ?";
        $stmt = $this->db->executeQuery($sql, [$email]);
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            
            if (password_verify($senha, $usuario['senha'])) {
                $this->id = $usuario['id_usuario'];
                $this->nome = $usuario['nome'];
                $this->email = $usuario['email'];
                $this->tipo = $usuario['tipo'];
                
                return true;
            }
        }
        
        return false;
    }
    
    // Método para verificar se email é único
    public function verificarEmailUnico($email, $id_excluir = 0) {
        $sql = "SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?";
        $stmt = $this->db->executeQuery($sql, [$email, $id_excluir]);
        $result = $stmt->get_result();
        
        return $result->num_rows === 0;
    }
    
    // Método para criar usuário
    public function criar($dados) {
        $this->setNome($dados['nome']);
        $this->setEmail($dados['email']);
        $this->setSenha($dados['senha']); // Usa password_hash no setter
        $this->setTipo($dados['tipo']);
        
        $sql = "INSERT INTO usuarios (nome, email, senha, tipo, data_cadastro, ativo) VALUES (?, ?, ?, ?, NOW(), 1)";
        $stmt = $this->db->executeQuery($sql, [
            $this->nome, 
            $this->email, 
            $this->senha, // Já está criptografada
            $this->tipo
        ]);
        
        $this->id = $stmt->insert_id;
        return $this->id;
    }
    
    // Método para atualizar usuário
    public function atualizar($id, $dados) {
        if (isset($dados['senha']) && !empty($dados['senha'])) {
            // Se tem senha nova, atualiza com senha criptografada
            $this->setSenha($dados['senha']);
            $sql = "UPDATE usuarios SET nome = ?, email = ?, tipo = ?, senha = ? WHERE id_usuario = ?";
            $params = [$dados['nome'], $dados['email'], $dados['tipo'], $this->senha, $id];
        } else {
            // Se não tem senha nova, mantém a atual
            $sql = "UPDATE usuarios SET nome = ?, email = ?, tipo = ? WHERE id_usuario = ?";
            $params = [$dados['nome'], $dados['email'], $dados['tipo'], $id];
        }
        
        $stmt = $this->db->executeQuery($sql, $params);
        return $stmt->affected_rows;
    }
    
    // Método para excluir usuário
    public function excluir($id) {
        $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
        $stmt = $this->db->executeQuery($sql, [$id]);
        return $stmt->affected_rows;
    }
    
    // Método para listar usuários (CORRIGIDO - inclui senha)
    public function listarTodos() {
        $sql = "SELECT id_usuario, nome, email, tipo, senha, data_cadastro FROM usuarios ORDER BY data_cadastro ASC";
        $stmt = $this->db->executeQuery($sql);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Método para buscar usuário por ID
    public function buscarPorId($id) {
        $sql = "SELECT id_usuario, nome, email, tipo, data_cadastro FROM usuarios WHERE id_usuario = ?";
        $stmt = $this->db->executeQuery($sql, [$id]);
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    // Método para verificar se é gerente
    public function isGerente() {
        return $this->tipo === 'Gerente';
    }
}
?>