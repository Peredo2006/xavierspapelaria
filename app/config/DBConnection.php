

<?php 


class DBConnection{
    private $host   = "";
    private $user   = "";
    private $pass   = "";
    private $port   = "";
    private $schema = "";

    private $connection = null;

    // Agora aceita credenciais opcionais no construtor
    function __construct($host = null, $user = null, $pass = null, $schema = null, $port = null){
        if ($host !== null) $this->host = $host;
        if ($user !== null) $this->user = $user;
        if ($pass !== null) $this->pass = $pass;
        if ($schema !== null) $this->schema = $schema;
        if ($port !== null) $this->port = $port;

        try {
            $dsn = 'mysql:host='.$this->host.';dbname='.$this->schema;
            if (!empty($this->port)) {
                $dsn .= ';port='.$this->port;
            }
            $this->connection = new PDO($dsn, $this->user, $this->pass);
        } catch (PDOException $e) {
            die ( "Erro de Conxão com o Banco de Dados!: " . $e->getMessage() );
        }
    }


    function query( $sql){
        $resultSet = $this->connection->query( $sql );
        return ($resultSet);
    }




}

?>