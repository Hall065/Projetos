<?php
session_start();

// Redireciona para dashboard se o usuário já estiver logado
if(isset($_SESSION['user'])) {
    header("Location: View/DashBoard.php");
    exit;
}

// Pega a rota da URL (?rota=*lugar_desejado*)
$rota = $_GET['rota'] ?? '';

switch($rota) {
    case 'login':
        include "View/Login.php";
        break;
    case 'cadastro':
        include "View/Cadastro.php";
        break;
    default:
        include "View/Principal.php";
        break;
}

?>