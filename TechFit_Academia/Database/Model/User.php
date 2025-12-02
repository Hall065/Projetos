<?php
require_once __DIR__ . '/../Conexao.php';

class User {
    private $conn;

    public function __construct() {
        $db = new Conexao();
        $this->conn = $db->conn;
    }

    public function register($name, $email, $phone, $password) {
        try {
            // TRUQUE 1: Converte e-mail para minúsculo para evitar erro
            $email = strtolower($email);

            // TRUQUE 2: Define o nível de acesso AUTOMATICAMENTE baseado no e-mail
            $nivel_acesso = 'comum';
            if (str_ends_with($email, '@techfit.adm.br')) {
                $nivel_acesso = 'admin';
            }

            // Agora salvamos também o nivel_acesso
            $sql = "INSERT INTO usuarios (nome, email, telefone, senha, nivel_acesso) 
                    VALUES (:name, :email, :phone, :password, :nivel)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(":name", $name);
            $stmt->bindValue(":email", $email);
            $stmt->bindValue(":phone", $phone);
            $stmt->bindValue(":password", password_hash($password, PASSWORD_DEFAULT));
            $stmt->bindValue(":nivel", $nivel_acesso); // Salva 'admin' ou 'comum'
            
            return $stmt->execute();
        } catch (PDOException $e) {
            // Se der erro, mostra na tela (modo debug)
            echo "<div style='background:red; color:white; padding:10px;'>Erro SQL: " . $e->getMessage() . "</div>";
            return false;
        }
    }

    public function login($email, $password) {
        $email = strtolower($email); // Força minúsculo no login também
        
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['senha'])) {
            return $user; // Agora retorna o usuário com a coluna 'nivel_acesso'
        }
        return false;
    }

    public function exists($email) {
        $email = strtolower($email);
        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":email", $email);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
?>