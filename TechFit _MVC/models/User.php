<?php
class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Encontra um usuário pelo email
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Processa a tentativa de login
    public function login($email, $senha) {
        $usuario = $this->findByEmail($email);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Senha correta, retorna dados do usuário
            return $usuario;
        }
        // Email não encontrado ou senha incorreta
        return false;
    }

    // Cria um novo usuário
    public function create($nome, $email, $telefone, $senha) {
        // Verificar se o email já existe
        if ($this->findByEmail($email)) {
            return "Este email já está cadastrado.";
        }

        // Criptografar a senha
        $senha_hash = password_hash($senha, PASSWORD_BCRYPT);

        try {
            // Inserir no banco
            $stmt = $this->pdo->prepare("INSERT INTO usuarios (nome, email, telefone, senha) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $telefone, $senha_hash]);
            return true;
        } catch (PDOException $e) {
            return "Erro ao cadastrar: " . $e->getMessage();
        }
    }
}