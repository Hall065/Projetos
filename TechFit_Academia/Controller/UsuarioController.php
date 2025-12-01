<?php
require_once __DIR__ . '/../Models/Usuario.php';

class UsuarioController {

    public function cadastrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nome = $_POST['nome'] ?? '';
            $email = $_POST['email'] ?? '';
            $telefone = $_POST['telefone'] ?? '';
            $senha = $_POST['senha'] ?? '';

            if (Usuario::buscarPorEmail($email)) {
                return "Email já cadastrado!";
            }

            if (Usuario::cadastrar($nome, $email, $telefone, $senha)) {
                header("Location: Login.php?cadastro=sucesso");
                exit;
            }

            return "Erro ao cadastrar.";
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = $_POST['email'] ?? '';
            $senha = $_POST['senha'] ?? '';

            $usuario = Usuario::buscarPorEmail($email);

            if ($usuario && password_verify($senha, $usuario['senha'])) {
                session_start();
                $_SESSION['usuario'] = $usuario['id'];
                header("Location: ../Home/Dashboard.php");
                exit;
            }

            return "Email ou senha inválidos.";
        }
    }
}
