<?php
class Conexao {
    private static $instance;

    public static function getConexao() {
        if (!self::$instance) {
            try {
                self::$instance = new PDO(
                    "mysql:host=localhost;dbname=techfit;charset=utf8",
                    "root",
                    "cada2110"
                );
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Erro na conexão: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
