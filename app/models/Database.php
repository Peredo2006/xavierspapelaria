<?php
// classes/Database.php
class Database {
    private $host = "40.160.64.65";
    private $username = "hostdeprojetos_6tech";
    private $password = "YawJzLrtGlJCb8R?";
    private $database = "hostdeprojetos_xavierspapelaria";
    private $conn;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
        
        if ($this->conn->connect_error) {
            die("Erro de conexão: " . $this->conn->connect_error);
        }
        
        $this->conn->set_charset("utf8");
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
    
    // Método para executar queries com segurança
    public function executeQuery($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Erro na preparação da query: " . $this->conn->error . " - SQL: " . $sql);
            }
            
            if (!empty($params)) {
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            return $stmt;
        } catch (Exception $e) {
            // Log do erro sem interromper completamente a aplicação
            error_log("Erro na query: " . $e->getMessage());
            throw $e; // Re-lança a exceção para tratamento específico
        }
    }
}
?>