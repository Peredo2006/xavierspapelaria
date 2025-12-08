<?php
// classes/Produto.php
class Produto {
    private $db;
    private $id_produto;
    private $nome;
    private $descricao;
    private $categoria;
    private $preco;
    private $quantidade;
    private $imagem;

    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Getters
    public function getid_produto() { return $this->id_produto; }
    public function getNome() { return $this->nome; }
    public function getDescricao() { return $this->descricao; }
    public function getCategoria() { return $this->categoria; }
    public function getPreco() { return $this->preco; }
    public function getQuantidade() { return $this->quantidade; }
    public function getImagem() { return $this->imagem; }

    
    // Setters
    public function setNome($nome) { $this->nome = $nome; }
    public function setDescricao($descricao) { $this->descricao = $descricao; }
    public function setCategoria($categoria) { $this->categoria = $categoria; }
    public function setPreco($preco) { $this->preco = floatval($preco); }
    public function setQuantidade($quantidade) { $this->quantidade = intval($quantidade); }
    public function setImagem($imagem) { $this->imagem = $imagem; }
    
    // Método para validar imagem
    public function validarImagem($arquivo) {
        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        // Verificar tipo de arquivo
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $tipoArquivo = mime_content_type($arquivo['tmp_name']);
        
        if (!in_array($tipoArquivo, $tiposPermitidos)) {
            return false;
        }
        
        // Verificar tamanho (máximo 5MB)
        if ($arquivo['size'] > 5 * 1024 * 1024) {
            return false;
        }
        
        return true;
    }
    
    // Método para fazer upload de imagem
    public function fazerUploadImagem($arquivo, $uploadDir) {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $nomeArquivo = uniqid('p_') . '.' . $ext;
        
        if (move_uploaded_file($arquivo['tmp_name'], $uploadDir . $nomeArquivo)) {
            return $nomeArquivo;
        }
        
        return null; // Retorna null em caso de erro
    }
    
    // Método para criar produto CORRIGIDO
    public function criar($dados, $imagem = null) {
        $this->setNome($dados['nome']);
        $this->setDescricao($dados['descricao'] ?? '');
        $this->setCategoria($dados['categoria'] ?? 'Outros');
        $this->setPreco($dados['preco']);
        $this->setQuantidade($dados['quantidade']);
        
        // Processar imagem se for fornecida
        if ($imagem && $this->validarImagem($imagem)) {
            $uploadDir = __DIR__ . '/../../public/uploads/';
            $this->imagem = $this->fazerUploadImagem($imagem, $uploadDir);
        } else {
            $this->setImagem(''); // Define como string vazia em vez de null
        }
        
        $sql = "INSERT INTO produtos (nome, descricao, categoria, preco, quantidade, imagem) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->executeQuery($sql, [
            $this->nome,
            $this->descricao,
            $this->categoria,
            $this->preco,
            $this->quantidade,
            $this->imagem,
        ]);
        
        $this->id_produto = $stmt->insert_id;
        return $this->id_produto;
    }
    
    // Método para atualizar produto CORRIGIDO
    public function atualizar($id_produto, $dados, $imagem = null, $fotoAtual = '') {
        $this->setNome($dados['nome']);
        $this->setDescricao($dados['descricao'] ?? '');
        $this->setCategoria($dados['categoria'] ?? 'Outros');
        $this->setPreco($dados['preco']);
        $this->setQuantidade($dados['quantidade']);
        
        $novaImagem = $fotoAtual;
        
        // Processar nova imagem se for fornecida
        if ($imagem && $this->validarImagem($imagem)) {
            $uploadDir = __DIR__ . '/../../public/uploads/';
            $novaImagem = $this->fazerUploadImagem($imagem, $uploadDir);
            
            // Remover imagem antiga se for diferente
            if ($novaImagem && $fotoAtual && $fotoAtual !== $novaImagem) {
                $this->removerImagem($fotoAtual, $uploadDir);
            }
        }
        
        $sql = "UPDATE produtos SET nome = ?, descricao = ?, categoria = ?, preco = ?, quantidade = ?, imagem = ? WHERE id_produto = ?";
        $stmt = $this->db->executeQuery($sql, [
            $this->nome,
            $this->descricao,
            $this->categoria,
            $this->preco,
            $this->quantidade,
            $novaImagem,
            $id_produto
        ]);
        
        return $stmt->affected_rows;
    }

    // Método para listar produtos com filtro de categoria
    public function listarTodos($categoria = 'todos') {
        if ($categoria !== 'todos' && in_array($categoria, ['Doces', 'Brinquedos', 'Papelaria', 'Outros'])) {
            $sql = "SELECT * FROM produtos WHERE categoria = ? ORDER BY id_produto DESC";
            $stmt = $this->db->executeQuery($sql, [$categoria]);
        } else {
            $sql = "SELECT * FROM produtos ORDER BY id_produto DESC";
            $stmt = $this->db->executeQuery($sql);
        }
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Método para buscar produto por id
    public function buscarPorId($id_produto) {
        $sql = "SELECT * FROM produtos WHERE id_produto = ?";
        $stmt = $this->db->executeQuery($sql, [$id_produto]);
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    // Método para validar dados do produto
    public function validarDados($dados) {
        if (empty($dados['nome']) || $dados['quantidade'] < 0 || $dados['preco'] < 0) {
            return false;
        }
        return true;
    }

    // Método para excluir produto
    public function excluir($id) {
        // Buscar informações do produto para remover a imagem
        $produto = $this->buscarPorId($id);
        if ($produto && !empty($produto['imagem'])){
            $uploadDir = __DIR__ . '/../../public/uploads/';
            $this->removerImagem($produto['imagem'], $uploadDir);
        }
        
        $sql = "DELETE FROM produtos WHERE id_produto = ?";
        $stmt = $this->db->executeQuery($sql, [$id]);
        return $stmt->affected_rows;
    }
    
    // Método para remover imagem do servidor
    private function removerImagem($nomeImagem, $uploadDir) {
        $caminhoCompleto = $uploadDir . $nomeImagem;
        if (file_exists($caminhoCompleto)) {
            @unlink($caminhoCompleto);
        }
    }
}
?>