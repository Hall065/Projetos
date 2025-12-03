<?php

class Conexao {
    // 1. Configurações do Banco (Centralizadas aqui)
    private $host = "localhost";
    private $dbname = "techfit";
    private $user = "root";
    private $pass = "senaisp"; // Sua senha correta

    // 2. Propriedade pública para o User.php (Legacy)
    // O User.php faz: $db = new Conexao(); $db->conn;
    public $conn; 

    // 3. Propriedade estática para a API (Singleton)
    private static $pdoInstance;

    // CONSTRUTOR: Roda quando faz "new Conexao()" (Usado no User.php)
    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
                $this->user,
                $this->pass
            );
            // Configurações padrão de erro
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erro na conexão: " . $e->getMessage());
        }
    }

    // MÉTODO ESTÁTICO: Roda quando faz "Conexao::getConexao()" (Usado na API)
    public static function getConexao() {
        if (!isset(self::$pdoInstance)) {
            // Truque: Criamos um "new self()" para reaproveitar a lógica do construtor acima
            $db = new self(); 
            self::$pdoInstance = $db->conn; // Guardamos apenas a conexão PDO
        }
        return self::$pdoInstance;
    }
}
?>