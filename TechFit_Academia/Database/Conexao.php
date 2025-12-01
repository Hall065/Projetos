<?php
class Conexao {
    private $host = "localhost";
    private $dbname = "techfit";
    private $user = "root"; // ou o usuário do seu MySQL
    private $pass = "";     // ou a senha do MySQL
    public $conn;

    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
                $this->user,
                $this->pass
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }
}
?>
