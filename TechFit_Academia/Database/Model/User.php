<?php
require_once __DIR__ . '/../Database/Conexao.php';

class User {
    private $conn;

    public function __construct() {
        $db = new Conexao();
        $this->conn = $db->conn;
    }

    public function register($name, $email, $phone, $password) {
        $sql = "INSERT INTO users (name, email, phone, password) VALUES (:name, :email, :phone, :password)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":name", $name);
        $stmt->bindValue(":email", $email);
        $stmt->bindValue(":phone", $phone);
        $stmt->bindValue(":password", password_hash($password, PASSWORD_DEFAULT));
        return $stmt->execute();
    }

    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function exists($email) {
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":email", $email);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
?>
