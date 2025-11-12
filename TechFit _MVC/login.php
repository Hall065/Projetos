<?php
// Controlador da Página de Login
require 'models/Database.php'; // Inicia a sessão

// Se o usuário já está logado, redireciona para o home
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
}

// Pega e limpa mensagens de erro/sucesso da sessão
$login_error = $_SESSION['login_error'] ?? null;
$register_success = $_SESSION['register_success'] ?? null;
unset($_SESSION['login_error'], $_SESSION['register_success']);

// Carrega a View (que terá acesso às variáveis $login_error e $register_success)
require 'views/login_view.php';