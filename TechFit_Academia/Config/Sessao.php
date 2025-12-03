<?php
// Define o caminho da pasta de sessões (subindo um nível da pasta Config)
$session_dir = __DIR__ . '/../sessions_data';

// Garante que a pasta existe (igual você fez no AuthController)
if (!is_dir($session_dir)) {
    mkdir($session_dir, 0777, true);
}

// Configura o PHP para salvar as sessões lá
session_save_path($session_dir);

// Inicia a sessão se ela ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>