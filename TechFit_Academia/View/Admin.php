<?php
// 1. Inclui o Controller (necessário para o Logout funcionar)
require_once __DIR__ . '/../Controller/AuthController.php';

// Se a sessão não estiver iniciada, inicia
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- BLOCO ANTI-CACHE (Adicione isto) ---
// Diz ao navegador: "NUNCA salve uma cópia desta página"
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
// ----------------------------------------

if (!isset($_SESSION['user'])) {
    header("Location: Login.php");
    exit;
}

// 2. Proteção de Segurança (Route Guard)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se estiver logado mas NÃO for admin, manda para o Dashboard comum
$admin_domain = '@techfit.adm.br';
if (!str_ends_with($_SESSION['user'], $admin_domain)) {
    header("Location: DashBoard.php");
    exit;
}

// Pega o nome do usuário (opcional, só visual)
$usuario_logado = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TechFit - Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../Assets/Css/Admin.css">
</head>
<body class="bg-gray-900 text-white min-h-screen font-[Rajdhani]">

  <div class="flex">
    <aside class="w-64 bg-gray-800 h-screen p-6 fixed left-0 top-0 border-r border-gray-700 flex flex-col justify-between">
      <div>
        <h1 class="text-3xl font-bold mb-10 flex items-center text-red-600">
          <i class="fas fa-dumbbell mr-3"></i>ADMIN
        </h1>
        
        <nav class="space-y-4">
          <a href="#" class="flex items-center space-x-3 text-gray-300 hover:text-red-500 hover:bg-gray-700 p-3 rounded-lg transition-all">
            <i class="fas fa-users w-6"></i>
            <span>Usuários</span>
          </a>
          <a href="#" class="flex items-center space-x-3 text-gray-300 hover:text-red-500 hover:bg-gray-700 p-3 rounded-lg transition-all">
            <i class="fas fa-calendar-check w-6"></i>
            <span>Agendamentos</span>
          </a>
          <a href="#" class="flex items-center space-x-3 text-gray-300 hover:text-red-500 hover:bg-gray-700 p-3 rounded-lg transition-all">
            <i class="fas fa-chart-line w-6"></i>
            <span>Relatórios</span>
          </a>
        </nav>
      </div>

      <div class="border-t border-gray-700 pt-6">
        <div class="mb-4 px-3">
            <p class="text-sm text-gray-400">Logado como:</p>
            <p class="text-sm font-bold text-white truncate"><?= $usuario_logado ?></p>
        </div>

        <form method="POST">
            <button type="submit" name="logout" class="w-full flex items-center space-x-3 text-red-400 hover:text-white hover:bg-red-600 p-3 rounded-lg transition-all cursor-pointer">
                <i class="fas fa-sign-out-alt w-6"></i>
                <span>Sair do Sistema</span>
            </button>
        </form>
      </div>
    </aside>

    <main class="flex-1 ml-64 p-8">
      <header class="flex justify-between items-center mb-10">
        <h2 class="text-3xl font-bold">Painel de Controle</h2>
        <div class="flex items-center space-x-4">
            <span class="bg-red-600 text-xs font-bold px-3 py-1 rounded-full">ADMINISTRADOR</span>
        </div>
      </header>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
          <h3 class="text-lg text-gray-400 mb-2">Total Usuários</h3>
          <p class="text-4xl font-bold text-white">120</p>
        </div>
        <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
          <h3 class="text-lg text-gray-400 mb-2">Agendamentos Hoje</h3>
          <p class="text-4xl font-bold text-red-500">45</p>
        </div>
        <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
          <h3 class="text-lg text-gray-400 mb-2">Novos Inscritos</h3>
          <p class="text-4xl font-bold text-green-500">+12</p>
        </div>
      </div>

      <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <h3 class="text-xl font-bold mb-6">Últimos Cadastros</h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center bg-gray-700/50 p-4 rounded-lg">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-gray-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-gray-300"></i>
                    </div>
                    <div>
                        <p class="font-bold">João Silva</p>
                        <p class="text-sm text-gray-400">joao@email.com</p>
                    </div>
                </div>
                <span class="text-green-400 text-sm">Ativo</span>
            </div>
            <div class="flex justify-between items-center bg-gray-700/50 p-4 rounded-lg">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-gray-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-gray-300"></i>
                    </div>
                    <div>
                        <p class="font-bold">Maria Souza</p>
                        <p class="text-sm text-gray-400">maria@email.com</p>
                    </div>
                </div>
                <span class="text-green-400 text-sm">Ativo</span>
            </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>