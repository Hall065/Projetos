<?php
// Controlador da Página Principal
require 'models/Database.php'; // Inicia a sessão

// Este controlador é simples, apenas exibe a view.
// A lógica de login/logout no header é tratada pela sessão.
require 'views/principal_view.php';