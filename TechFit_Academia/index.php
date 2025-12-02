<?php
session_start();

// 1. Se o usuário já estiver logado, manda direto para o painel
if(isset($_SESSION['user'])) {
    // Aqui você poderia colocar aquela lógica de verificar se é admin
    // Mas por segurança e simplicidade, mandamos pro Dashboard e ele redireciona se precisar
    header("Location: View/DashBoard.php");
    exit;
}

// 2. Se NÃO estiver logado, manda para a tela inicial do site (Principal)
// Se você não tiver a Principal.php pronta, mude para "View/Login.php"
header("Location: View/Principal.php");
exit;
?>