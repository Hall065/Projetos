<?php
class Database {
    private static $pdo = null;

    // Dados de conexão do Database
    private $host = 'localhost';
    private $db_name = 'techfit';
    private $username = 'root';
    private $password = 'SenaiSP';
    private $port = 3306;

    // Construtor privado para impedir instâncias diretas
    private function __construct() {
        try {
            self::$pdo = new PDO(
                "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8",
                $this->username,
                $this->password
            );
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }

    // Método estático para obter a conexão (Singleton)
    public static function connect() {
        if (self::$pdo == null) {
            new Database();
        }
        return self::$pdo;
    }
}

// Iniciar a sessão globalmente
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}