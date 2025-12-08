<?php
// models/HistoricoCliente.php
class HistoricoCliente {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Método para registrar alteração no histórico
    public function registrar($id_cliente, $valor_anterior, $valor_atual, $observacao = '') {
        try {
            $sql = "INSERT INTO historico_clientes (id_cliente, valor_anterior, valor_atual, observacao, data_alteracao) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->db->executeQuery($sql, [
                $id_cliente,
                $valor_anterior,
                $valor_atual,
                $observacao
            ]);
            
            return $stmt->insert_id;
        } catch (Exception $e) {
            // Log do erro, mas não interrompe o fluxo principal
            error_log("Erro ao registrar histórico: " . $e->getMessage());
            return false;
        }
    }
    
    // Método para buscar histórico do cliente
    public function buscarPorCliente($id_cliente, $limite = 10) {
        try {
            $sql = "SELECT * FROM historico_clientes WHERE id_cliente = ? ORDER BY data_alteracao DESC LIMIT ?";
            $stmt = $this->db->executeQuery($sql, [$id_cliente, $limite]);
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar histórico: " . $e->getMessage());
            return [];
        }
    }
    
    // Método para excluir histórico do cliente (quando excluir o cliente)
    public function excluirPorCliente($id_cliente) {
        try {
            $sql = "DELETE FROM historico_clientes WHERE id_cliente = ?";
            $stmt = $this->db->executeQuery($sql, [$id_cliente]);
            return $stmt->affected_rows;
        } catch (Exception $e) {
            // Se a tabela não existir, apenas registra o erro e continua
            error_log("Erro ao excluir histórico (tabela pode não existir): " . $e->getMessage());
            return 0; // Retorna 0 para indicar que não houve exclusão
        }
    }
    
    // Método para verificar se a tabela existe
    public function tabelaExiste() {
        try {
            $sql = "SELECT 1 FROM historico_clientes LIMIT 1";
            $stmt = $this->db->executeQuery($sql);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>