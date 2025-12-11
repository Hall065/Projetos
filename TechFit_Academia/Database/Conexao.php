<?php
// Arquivo: Database/Conexao.php

class Conexao {
    // 1. Configurações do Banco (CENTRALIZADAS)
    private $host = "localhost"; //http://localhost/TechFit_Academia/View/Principal.php
    private $dbname = "techfit";
    private $user = "root";
    private $pass = "SenaiSP";

    // 2. Compatibilidade Legada ($db->conn)
    public $conn; 

    // 3. Singleton para API (Conexao::getConexao())
    private static $pdoInstance;

    public function __construct() {
        try {
            $this->conn = $this->connect();
        } catch (PDOException $e) {
            die("Erro fatal na conexão (Construtor): " . $e->getMessage());
        }
    }

    public static function getConexao() {
        if (!isset(self::$pdoInstance)) {
            $temp = new self(); // Usa o construtor acima
            self::$pdoInstance = $temp->conn;
        }
        return self::$pdoInstance;
    }

    // Método privado para evitar repetição de código
    private function connect() {
        $pdo = new PDO(
            "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
            $this->user,
            $this->pass
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    }
}
?>