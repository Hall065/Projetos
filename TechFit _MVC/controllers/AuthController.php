<?php
// Este arquivo é o "cérebro" por trás do login e cadastro
require '../models/Database.php';
require '../models/User.php';

$pdo = Database::connect();
$userModel = new User($pdo);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $email = trim($_POST['email']);
        $senha = trim($_POST['senha']);

        $usuario = $userModel->login($email, $senha);

        if ($usuario) {
            // Salvar dados na sessão
            $_SESSION['user_id'] = $usuario['id_usuario'];
            $_SESSION['user_nome'] = $usuario['nome'];
            $_SESSION['user_tipo'] = $usuario['tipo_usuario'];
            $_SESSION['user_plano'] = $usuario['plano'];

            // Redirecionar
            if ($usuario['tipo_usuario'] == 'admin') {
                header("Location: ../admin.php");
            } else {
                header("Location: ../home.php");
            }
        } else {
            // Erro no login
            $_SESSION['login_error'] = "Email ou senha inválidos.";
            header("Location: ../login.php");
        }
        exit();

    case 'register':
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $telefone = trim($_POST['telefone']);
        $senha = trim($_POST['senha']);

        // Validação básica (pode ser melhorada)
        if (empty($nome) || empty($email) || empty($telefone) || empty($senha)) {
            $_SESSION['register_error'] = "Todos os campos são obrigatórios.";
            header("Location: ../cadastro.php");
            exit();
        }

        $resultado = $userModel->create($nome, $email, $telefone, $senha);

        if ($resultado === true) {
            $_SESSION['register_success'] = "Cadastro realizado com sucesso! Faça o login.";
            header("Location: ../login.php");
        } else {
            // $resultado contém a mensagem de erro do Model
            $_SESSION['register_error'] = $resultado;
            header("Location: ../cadastro.php");
        }
        exit();
}