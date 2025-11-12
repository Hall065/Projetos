<?php
// Controlador da Página de Cadastro
require 'models/Database.php'; // Inicia a sessão

// Se o usuário já está logado, redireciona para o home
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
}

// Pega e limpa mensagens de erro
$register_error = $_SESSION['register_error'] ?? null;
unset($_SESSION['register_error']);

// Carrega a View
require 'views/cadastro_view.php';

?>

