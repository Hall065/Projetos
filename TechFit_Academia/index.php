<?php
session_start();

// 1. Se o usuário já estiver logado, manda direto para o painel
if(isset($_SESSION['user'])) {
    header("Location: View/DashBoard.php");
    exit;
}

// 2. Se NÃO estiver logado, manda para a tela inicial do site (Principal)
header("Location: View/Principal.php");
exit;
?>