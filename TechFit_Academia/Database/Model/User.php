<?php
// Arquivo: Database/Model/User.php

// Garante que pega a conexão correta, subindo um nível
require_once __DIR__ . '/../Conexao.php';

class User {
    private $conn;

    public function __construct() {
        // Usa a forma híbrida que definimos
        $db = new Conexao();
        $this->conn = $db->conn;
    }

    public function register($name, $email, $phone, $password) {
        try {
            $email = strtolower(trim($email)); // Trim para remover espaços acidentais

            // Lógica de Admin automática
            $nivel_acesso = 'comum';
            // Verifica se termina com o dominio adm (funciona em PHP 8+)
            if (str_ends_with($email, '@techfit.adm.br')) {
                $nivel_acesso = 'admin';
            }

            $sql = "INSERT INTO usuarios (nome, email, telefone, senha, nivel_acesso) 
                    VALUES (:name, :email, :phone, :password, :nivel)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(":name", strip_tags($name)); // Segurança básica XSS
            $stmt->bindValue(":email", $email);
            $stmt->bindValue(":phone", $phone);
            $stmt->bindValue(":password", password_hash($password, PASSWORD_DEFAULT));
            $stmt->bindValue(":nivel", $nivel_acesso);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            // Log de erro (melhor que echo na tela em produção, mas ok por enquanto)
            error_log("Erro no registro: " . $e->getMessage());
            return false;
        }
    }

    public function login($email, $password) {
        $email = strtolower(trim($email));
        
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['senha'])) {
            // Remove a senha do array retornado por segurança
            unset($user['senha']);
            return $user; 
        }
        return false;
    }

    public function exists($email) {
        $email = strtolower(trim($email));
        $sql = "SELECT count(*) FROM usuarios WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":email", $email);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Adicione dentro da class User, logo após o login
    public function salvarTokenRecuperacao($email, $token, $expires) {
        try {
            $sql = "UPDATE usuarios SET reset_token = :token, reset_expires = :expires WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(":token", $token);
            $stmt->bindValue(":expires", $expires);
            $stmt->bindValue(":email", $email);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>