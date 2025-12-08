<?php
// models/Tarefa.php
class Tarefa {
    private $db;
    private $id_tarefa;
    private $titulo;
    private $descricao;
    private $data_tarefa;
    private $data_criacao;
    private $prioridade;
    private $repeticao;
    private $concluida;
    private $data_conclusao;
    private $observacoes;

    public function __construct($db) {
        $this->db = $db;
    }

    // Getters
    public function getIdTarefa() { return $this->id_tarefa; }
    public function getTitulo() { return $this->titulo; }
    public function getDescricao() { return $this->descricao; }
    public function getDataTarefa() { return $this->data_tarefa; }
    public function getDataCriacao() { return $this->data_criacao; }
    public function getPrioridade() { return $this->prioridade; }
    public function getRepeticao() { return $this->repeticao; }
    public function getConcluida() { return $this->concluida; }
    public function getDataConclusao() { return $this->data_conclusao; }
    public function getObservacoes() { return $this->observacoes; }

    // Setters
    public function setTitulo($titulo) { $this->titulo = $titulo; }
    public function setDescricao($descricao) { $this->descricao = $descricao; }
    public function setDataTarefa($data_tarefa) { $this->data_tarefa = $data_tarefa; }
    public function setPrioridade($prioridade) { $this->prioridade = $prioridade; }
    public function setRepeticao($repeticao) { $this->repeticao = $repeticao; }
    public function setConcluida($concluida) { $this->concluida = $concluida; }
    public function setObservacoes($observacoes) { $this->observacoes = $observacoes; }

    // Buscar tarefas para hoje (usado no index.php)
    public function buscarTarefasHoje() {
        $hoje = date('Y-m-d');
        $sql = "SELECT * FROM tarefas WHERE data_tarefa = ? AND concluida = 0 ORDER BY 
                CASE 
                    WHEN prioridade = 'alta' THEN 1
                    WHEN prioridade = 'media' THEN 2
                    WHEN prioridade = 'baixa' THEN 3
                    ELSE 4
                END";
        $stmt = $this->db->executeQuery($sql, [$hoje]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Buscar todas as tarefas com filtros
    public function buscarTarefas($filtro_prioridade = 'todas', $filtro_concluida = 'pendentes') {
        $sql = "SELECT * FROM tarefas WHERE 1=1";
        $params = [];

        // Filtro de prioridade
        if ($filtro_prioridade !== 'todas' && in_array($filtro_prioridade, ['alta', 'media', 'baixa'])) {
            $sql .= " AND prioridade = ?";
            $params[] = $filtro_prioridade;
        }

        // Filtro de conclusão
        if ($filtro_concluida === 'pendentes') {
            $sql .= " AND concluida = 0";
        } elseif ($filtro_concluida === 'concluidas') {
            $sql .= " AND concluida = 1";
        }

        $sql .= " ORDER BY 
                CASE 
                    WHEN prioridade = 'alta' THEN 1
                    WHEN prioridade = 'media' THEN 2
                    WHEN prioridade = 'baixa' THEN 3
                    ELSE 4
                END,
                data_tarefa ASC";

        $stmt = $this->db->executeQuery($sql, $params);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Buscar tarefa por ID
    public function buscarPorId($id_tarefa) {
        $sql = "SELECT * FROM tarefas WHERE id_tarefa = ?";
        $stmt = $this->db->executeQuery($sql, [$id_tarefa]);
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

    // Criar nova tarefa
    public function criar($dados) {
        $this->setTitulo($dados['titulo']);
        $this->setDescricao($dados['descricao'] ?? '');
        $this->setDataTarefa($dados['data_tarefa']);
        $this->setPrioridade($dados['prioridade']);
        $this->setRepeticao($dados['repeticao'] ?? 'nenhuma');
        $this->setObservacoes($dados['observacoes'] ?? '');
        
        $sql = "INSERT INTO tarefas (titulo, descricao, data_tarefa, prioridade, repeticao, observacoes) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->executeQuery($sql, [
            $this->titulo,
            $this->descricao,
            $this->data_tarefa,
            $this->prioridade,
            $this->repeticao,
            $this->observacoes
        ]);
        
        $this->id_tarefa = $stmt->insert_id;
        return $this->id_tarefa;
    }

    // Atualizar tarefa
    public function atualizar($id_tarefa, $dados) {
        $this->setTitulo($dados['titulo']);
        $this->setDescricao($dados['descricao'] ?? '');
        $this->setDataTarefa($dados['data_tarefa']);
        $this->setPrioridade($dados['prioridade']);
        $this->setRepeticao($dados['repeticao'] ?? 'nenhuma');
        $this->setObservacoes($dados['observacoes'] ?? '');
        
        $sql = "UPDATE tarefas SET titulo = ?, descricao = ?, data_tarefa = ?, 
                prioridade = ?, repeticao = ?, observacoes = ? WHERE id_tarefa = ?";
        
        $stmt = $this->db->executeQuery($sql, [
            $this->titulo,
            $this->descricao,
            $this->data_tarefa,
            $this->prioridade,
            $this->repeticao,
            $this->observacoes,
            $id_tarefa
        ]);
        
        return $stmt->affected_rows;
    }

    // Marcar tarefa como concluída
    public function marcarConcluida($id_tarefa) {
        $sql = "UPDATE tarefas SET concluida = 1, data_conclusao = NOW() WHERE id_tarefa = ?";
        $stmt = $this->db->executeQuery($sql, [$id_tarefa]);
        return $stmt->affected_rows;
    }

    // Marcar tarefa como pendente
    public function marcarPendente($id_tarefa) {
        $sql = "UPDATE tarefas SET concluida = 0, data_conclusao = NULL WHERE id_tarefa = ?";
        $stmt = $this->db->executeQuery($sql, [$id_tarefa]);
        return $stmt->affected_rows;
    }

    // Excluir tarefa
    public function excluir($id_tarefa) {
        $sql = "DELETE FROM tarefas WHERE id_tarefa = ?";
        $stmt = $this->db->executeQuery($sql, [$id_tarefa]);
        return $stmt->affected_rows;
    }

    // Validar dados da tarefa
    public function validarDados($dados) {
        if (empty($dados['titulo']) || empty($dados['data_tarefa'])) {
            return false;
        }
        return true;
    }
}
?>