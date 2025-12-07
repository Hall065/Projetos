<?php
// View/logout.php

// 1. Inicia a sessão para poder destruí-la
session_start();

// 2. Limpa todas as variáveis de sessão
$_SESSION = array();

// 3. Se desejar matar o cookie da sessão (garantia extra)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Destrói a sessão no servidor
session_destroy();

// 5. Redireciona para o Login
header("Location: Login.php");
exit;
?>