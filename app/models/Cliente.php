<?php
// models/Cliente.php
class Cliente {
    private $db;
    private $id_cliente;
    private $nome;
    private $cpf;
    private $telefone;
    private $endereco;
    private $limite_credito;
    private $data_cadastro;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Getters
    public function getId() { return $this->id_cliente; }
    public function getNome() { return $this->nome; }
    public function getCpf() { return $this->cpf; }
    public function getTelefone() { return $this->telefone; }
    public function getEndereco() { return $this->endereco; }
    public function getLimiteCredito() { return $this->limite_credito; }
    public function getDataCadastro() { return $this->data_cadastro; }
    
    // Setters
    public function setNome($nome) { $this->nome = $nome; }
    public function setCpf($cpf) { $this->cpf = $cpf; }
    public function setTelefone($telefone) { $this->telefone = $telefone; }
    public function setEndereco($endereco) { $this->endereco = $endereco; }
    public function setLimiteCredito($limite_credito) { $this->limite_credito = floatval($limite_credito); }
    
    // Método para listar clientes com filtro
    public function listarTodos($filtro = 'todos') {
        $where = '';
        
        switch ($filtro) {
            case 'recentes':
                $where = "WHERE data_cadastro >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
            case 'ate_20':
                $where = "WHERE limite_credito <= 20";
                break;
            case '20_a_50':
                $where = "WHERE limite_credito > 20 AND limite_credito <= 50";
                break;
            case 'mais_50':
                $where = "WHERE limite_credito > 50";
                break;
            default:
                $where = "";
        }
        
        $sql = "SELECT * FROM clientes $where ORDER BY data_cadastro DESC";
        $stmt = $this->db->executeQuery($sql);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Método para buscar cliente por ID
    public function buscarPorId($id_cliente) {
        $sql = "SELECT * FROM clientes WHERE id_cliente = ?";
        $stmt = $this->db->executeQuery($sql, [$id_cliente]);
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    // Método para verificar se CPF é único
    public function verificarCpfUnico($cpf, $id_excluir = 0) {
        $sql = "SELECT id_cliente FROM clientes WHERE cpf = ? AND id_cliente != ?";
        $stmt = $this->db->executeQuery($sql, [$cpf, $id_excluir]);
        $result = $stmt->get_result();
        
        return $result->num_rows === 0;
    }
    
    // Método para criar cliente
    public function criar($dados) {
        $this->setNome($dados['nome']);
        $this->setCpf($dados['cpf']);
        $this->setTelefone($dados['telefone']);
        $this->setEndereco($dados['endereco'] ?? '');
        $this->setLimiteCredito($dados['limite_credito']);
        
        $sql = "INSERT INTO clientes (nome, cpf, telefone, endereco, limite_credito, data_cadastro) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->executeQuery($sql, [
            $this->nome,
            $this->cpf,
            $this->telefone,
            $this->endereco,
            $this->limite_credito
        ]);
        
        $this->id_cliente = $stmt->insert_id;
        return $this->id_cliente;
    }
    
    // Método para atualizar cliente
    public function atualizar($id_cliente, $dados) {
        $sql = "UPDATE clientes SET nome = ?, cpf = ?, telefone = ?, endereco = ?, limite_credito = ? WHERE id_cliente = ?";
        $stmt = $this->db->executeQuery($sql, [
            $dados['nome'],
            $dados['cpf'],
            $dados['telefone'],
            $dados['endereco'],
            $dados['limite_credito'],
            $id_cliente
        ]);
        
        return $stmt->affected_rows;
    }
    
    // Método para atualizar apenas o limite de crédito
    public function atualizarLimite($id_cliente, $novo_limite) {
        try {
            $sql = "UPDATE clientes SET limite_credito = ? WHERE id_cliente = ?";
            $stmt = $this->db->executeQuery($sql, [$novo_limite, $id_cliente]);
            return $stmt->affected_rows;
        } catch (Exception $e) {
            error_log("Erro ao atualizar limite: " . $e->getMessage());
            return false;
        }
    }
    
    // Método para excluir cliente
    public function excluir($id_cliente) {
        $sql = "DELETE FROM clientes WHERE id_cliente = ?";
        $stmt = $this->db->executeQuery($sql, [$id_cliente]);
        return $stmt->affected_rows;
    }
}
?>