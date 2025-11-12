<?php
require 'models/Database.php'; // Apenas para iniciar a sessão

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Destruir a sessão
session_destroy();

// Redirecionar para a página de login
header("Location: login.php");
exit();