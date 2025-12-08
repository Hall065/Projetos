<?php
// Inclui o Controller
require_once __DIR__ . '/../Controller/AuthController.php';

// Garante que a sessão está iniciada
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// --- BLOCO ANTI-CACHE ---
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
// ------------------------------------

// 1. Proteção de Login
if (!isset($_SESSION['user'])) {
  header("Location: Login.php");
  exit;
}

// 2. Proteção de Nível: Garante que um Admin não fique no DashBoard
if (isset($_SESSION['nivel']) && $_SESSION['nivel'] === 'admin') {
  header("Location: Login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TechFit Dashboard</title>
  <link rel="stylesheet" href="../Assets/Css/DashBoard.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>

<body class="bg-gray-900 text-white min-h-screen">
  <!-- Sidebar -->
  <div class="fixed left-0 top-0 h-full w-64 bg-gradient-to-b from-gray-900 to-gray-800 shadow-2xl z-50 border-r border-gray-700">
    <!-- Logo -->
    <div class="p-6 border-b border-gray-700">
      <a href="Principal.php" class="flex items-center space-x-3 hover:opacity-80 transition-opacity duration-300 group">

        <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-800 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-red-900/50 transition-all">
          <i class="fas fa-dumbbell text-white text-xl"></i>
        </div>

        <div>
          <h1 class="text-2xl font-bold text-white">TechFit</h1>
          <p class="text-xs text-gray-400">Fitness Pro</p>
        </div>
      </a>
    </div>

    <!-- Menu Items -->
    <nav class="mt-6 px-3">
      <div class="sidebar-item active px-4 py-3 rounded-lg cursor-pointer flex items-center space-x-3 mb-1" onclick="navigateTo('inicio')">
        <i class="fas fa-home text-lg w-5"></i>
        <span class="font-medium">Início</span>
      </div>
      <div class="sidebar-item px-4 py-3 rounded-lg cursor-pointer flex items-center space-x-3 mb-1" onclick="navigateTo('horarios')">
        <i class="fas fa-clock text-lg w-5"></i>
        <span class="font-medium">Horários</span>
      </div>
      <div class="sidebar-item px-4 py-3 rounded-lg cursor-pointer flex items-center space-x-3 mb-1" onclick="navigateTo('agendamentos')">
        <i class="fas fa-calendar-check text-lg w-5"></i>
        <span class="font-medium">Agendamentos</span>
      </div>
      <div class="sidebar-item px-4 py-3 rounded-lg cursor-pointer flex items-center space-x-3 mb-1" onclick="navigateTo('treinos')">
        <i class="fas fa-dumbbell text-lg w-5"></i>
        <span class="font-medium">Meus Treinos</span>
      </div>
      <div class="sidebar-item px-4 py-3 rounded-lg cursor-pointer flex items-center space-x-3 mb-1" onclick="navigateTo('agenda')">
        <i class="fas fa-calendar text-lg w-5"></i>
        <span class="font-medium">Calendário</span>
      </div>
      <div class="sidebar-item px-4 py-3 rounded-lg cursor-pointer flex items-center space-x-3 mb-1" onclick="navigateTo('perfil')">
        <i class="fas fa-user text-lg w-5"></i>
        <span class="font-medium">Perfil</span>
      </div>
    </nav>

    <!-- User Quick Stats (Sequência) -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-700 bg-gray-900">
      <div class="text-center">
        <p class="text-sm text-gray-400 mb-1">Sequência Atual</p>
        <!-- O ID "sidebar-streak" é o alvo do JavaScript -->
        <p class="text-2xl font-bold metric-number text-white" id="sidebar-streak">...</p>
      </div>
    </div>
  </div> <!-- Fim da Sidebar -->

  <!-- Main Content -->
  <div class="ml-64 min-h-screen">
    <!-- Header -->
    <header class="bg-gradient-to-r from-gray-900 to-gray-800 shadow-xl p-6 border-b border-gray-700 sticky top-0 z-40 backdrop-blur-lg">
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-3xl font-bold" id="page-title">Dashboard</h2>
          <p class="text-gray-400 mt-1">Bem-vindo de volta,
            <span id="user-name" class="text-red-500 font-semibold">
              <?= explode(' ', $_SESSION['user']['nome'])[0] ?>
            </span>!
          </p>
        </div>
        <div class="flex items-center space-x-6">
          <!-- Área de Notificações -->
          <div class="relative">

            <!-- Botão do Sino -->
            <button id="notif-btn" class="relative focus:outline-none group p-2 rounded-full hover:bg-gray-800 transition-colors">
              <i class="fas fa-bell text-2xl text-gray-400 group-hover:text-red-500 transition-colors"></i>

              <!-- Bolinha de Contagem (Oculta por padrão) -->
              <span id="notification-count" class="absolute top-0 right-0 bg-red-600 text-xs text-white rounded-full w-5 h-5 flex items-center justify-center font-bold shadow-lg hidden animate-pulse border-2 border-gray-900">
                0
              </span>
            </button>

            <!-- Menu Dropdown (Oculto) -->
            <div id="notif-menu" class="hidden absolute right-0 mt-3 w-80 bg-gray-800 rounded-xl shadow-2xl border border-gray-700 z-50 overflow-hidden transform origin-top-right transition-all animate-fade-in-down">

              <!-- Cabeçalho do Dropdown -->
              <div class="p-4 border-b border-gray-700 bg-gray-900/50 flex justify-between items-center backdrop-blur-sm">
                <span class="font-bold text-white flex items-center">
                  <i class="fas fa-bell mr-2 text-red-500"></i>Notificações
                </span>
                <span class="text-xs text-gray-500">Recentes</span>
              </div>

              <!-- Lista de Notificações (O JS preenche aqui) -->
              <div id="notif-list" class="max-h-80 overflow-y-auto custom-scrollbar">
                <div class="flex flex-col items-center justify-center py-8 text-gray-500">
                  <i class="fas fa-spinner fa-spin mb-2 text-xl"></i>
                  <p class="text-sm">Carregando...</p>
                </div>
              </div>

              <!-- Rodapé do Dropdown -->
              <div class="p-2 border-t border-gray-700 bg-gray-900/30 text-center">
                <button onclick="loadNotifications()" class="text-xs text-blue-400 hover:text-blue-300 transition-colors w-full py-1">
                  <i class="fas fa-sync-alt mr-1"></i> Atualizar agora
                </button>
              </div>
            </div>
          </div>

          <!-- Perfil Dropdown -->
          <div class="relative">
            <button id="profile-btn" class="flex items-center space-x-3 hover:bg-gray-800 px-3 py-2 rounded-lg transition-colors focus:outline-none">
              <div class="w-10 h-10 rounded-full bg-gray-700 overflow-hidden flex items-center justify-center">
                <img id="header-user-photo" src="" class="w-full h-full object-cover hidden">
                <i id="header-user-icon" class="fas fa-user text-xl text-gray-400"></i>
              </div>
              <div class="text-left">
                <p class="font-semibold" id="header-user-name"><?= $_SESSION['user']['nome'] ?? 'Usuário' ?></p>
                <p class="text-sm text-gray-400" id="user-plan">Membro <?= $_SESSION['user']['plano'] ?? 'Standard' ?></p>
              </div>
              <i class="fas fa-chevron-down text-gray-400 ml-2 transition-transform" id="dropdown-icon"></i>
            </button>

            <!-- Dropdown Menu -->
            <div id="profile-menu" class="hidden absolute right-0 mt-2 w-56 bg-gray-800 rounded-xl shadow-2xl border border-gray-700 z-50 overflow-hidden">
              <a href="#" class="block px-4 py-3 hover:bg-gray-700 transition-colors flex items-center space-x-2" onclick="navigateTo('perfil')">
                <i class="fas fa-user text-red-500"></i>
                <span>Meu Perfil</span>
              </a>
              <a href="#" class="block px-4 py-3 hover:bg-gray-700 transition-colors flex items-center space-x-2">
                <i class="fas fa-cog text-gray-400"></i>
                <span>Configurações</span>
              </a>
              <hr class="border-gray-700">
              <form method="POST" class="w-full">
                <button type="submit" name="logout" class="w-full text-left block px-4 py-3 hover:bg-gray-700 transition-colors text-red-400 flex items-center space-x-2 cursor-pointer">
                  <i class="fas fa-sign-out-alt"></i>
                  <span>Sair</span>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Dashboard Content -->
    <main class="p-6">
      <!-- Início Section -->
      <div id="inicio" class="content-section active">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="card-gradient rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-400 text-sm mb-1">Treinos Este Mês</p>
                <p class="text-4xl font-bold metric-number" id="stat-monthly-workouts">24</p>
                <p class="text-green-500 text-xs mt-1"><i class="fas fa-arrow-up" style="color: lightgreen;"></i> +12% vs mês anterior</p>
              </div>
              <div class="w-14 h-14 bg-gradient-to-br from-red-600 to-red-800 rounded-xl flex items-center justify-center shadow-lg stat-icon">
                <i class="fas fa-dumbbell text-white text-xl"></i>
              </div>
            </div>
          </div>

          <div class="card-gradient rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-400 text-sm mb-1">Próximo Treino</p>
                <p class="text-xl font-bold text-white" id="stat-next-workout">Hoje 18:00</p>
                <p class="text-gray-500 text-xs mt-1" id="stat-next-workout-type">Treino A - Peito</p>
              </div>
              <div class="w-14 h-14 bg-gradient-to-br from-green-600 to-green-800 rounded-xl flex items-center justify-center shadow-lg stat-icon">
                <i class="fas fa-clock text-white text-xl"></i>
              </div>
            </div>
          </div>

          <div class="card-gradient rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-400 text-sm mb-1">Calorias Queimadas</p>
                <p class="text-4xl font-bold metric-number" id="stat-calories">2,450</p>
                <p class="text-orange-500 text-xs mt-1"><i class="fas fa-fire"></i> Este mês</p>
              </div>
              <div class="w-14 h-14 bg-gradient-to-br from-orange-600 to-orange-800 rounded-xl flex items-center justify-center shadow-lg stat-icon">
                <i class="fas fa-fire text-white text-xl"></i>
              </div>
            </div>
          </div>

          <!-- Card 4: Sequência (Streak) -->
          <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-lg relative overflow-hidden group hover:border-purple-500 transition-all">
            <div class="absolute right-0 top-0 h-full w-1 bg-gradient-to-b from-purple-600 to-purple-800"></div>
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-400 text-sm mb-1 uppercase tracking-wider">Sequência Atual</p>
                <!-- O SEGREDO ESTÁ AQUI: id="stat-streak" -->
                <p class="text-4xl font-bold text-white metric-number" id="stat-streak">0</p>
                <p class="text-purple-500 text-xs mt-2 font-medium">Dias consecutivos</p>
              </div>
              <div class="w-14 h-14 bg-gray-700 rounded-xl flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform">
                <i class="fas fa-fire text-purple-500 text-2xl"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions & Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Quick Actions -->
          <div class="card-gradient rounded-xl p-6">
            <h3 class="text-2xl font-bold mb-6 flex items-center">
              <i class="fas fa-bolt text-red-500 mr-3"></i>
              Ações Rápidas
            </h3>
            <div class="space-y-3">
              <button class="w-full action-btn p-4 rounded-xl text-left flex items-center justify-between group" onclick="navigateTo('agendamentos')">
                <div class="flex items-center space-x-3">
                  <i class="fas fa-calendar-plus text-xl"></i>
                  <span class="font-medium">Agendar Treino</span>
                </div>
                <i class="fas fa-arrow-right opacity-0 group-hover:opacity-100 transition-opacity"></i>
              </button>
              <button class="w-full bg-gray-800 hover:bg-gray-700 p-4 rounded-xl text-left flex items-center justify-between transition-all group border border-gray-700" onclick="navigateTo('treinos')">
                <div class="flex items-center space-x-3">
                  <i class="fas fa-list text-xl"></i>
                  <span class="font-medium">Ver Plano de Treino</span>
                </div>
                <i class="fas fa-arrow-right opacity-0 group-hover:opacity-100 transition-opacity"></i>
              </button>
              <button class="w-full bg-gray-800 hover:bg-gray-700 p-4 rounded-xl text-left flex items-center justify-between transition-all group border border-gray-700" onclick="navigateTo('agenda')">
                <div class="flex items-center space-x-3">
                  <i class="fas fa-chart-line text-xl"></i>
                  <span class="font-medium">Acompanhar Progresso</span>
                </div>
                <i class="fas fa-arrow-right opacity-0 group-hover:opacity-100 transition-opacity"></i>
              </button>
              <!-- NOVO: Botão de Assinatura -->
              <button class="w-full bg-gray-800 hover:bg-gray-700 p-4 rounded-xl text-left flex items-center justify-between transition-all group border border-gray-700 hover:border-green-500 mt-3" onclick="openSubscriptionModal()">
                <div class="flex items-center space-x-3">
                  <i class="fas fa-credit-card text-xl text-green-500"></i>
                  <span class="font-medium">Gerenciar Assinatura</span>
                </div>
                <i class="fas fa-external-link-alt opacity-0 group-hover:opacity-100 transition-opacity text-gray-400"></i>
              </button>
            </div>
          </div>

          <!-- Card Motivação do Dia (Substitui o Atividade Recente) -->
          <div class="card-gradient rounded-xl p-6 flex flex-col justify-center items-center text-center relative overflow-hidden">

            <!-- Efeito de brilho no topo -->
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-red-500 to-transparent opacity-50"></div>

            <div class="w-20 h-20 bg-gray-800 rounded-full flex items-center justify-center mb-4 shadow-lg border border-gray-700">
              <i class="fas fa-quote-left text-3xl text-red-500"></i>
            </div>

            <h3 class="text-xl font-bold text-white mb-2">Motivação do Dia</h3>
            <p class="text-gray-400 italic text-lg mb-6">"O único treino ruim é aquele que não aconteceu."</p>

            <div class="w-full">
              <div class="flex justify-between text-xs text-gray-400 mb-1 font-medium uppercase tracking-wider">
                <span>Meta da Semana</span>
                <span>75%</span>
              </div>
              <div class="w-full h-3 bg-gray-800 rounded-full overflow-hidden border border-gray-700">
                <div class="h-full bg-gradient-to-r from-red-600 to-red-500 w-3/4 shadow-[0_0_15px_rgba(220,38,38,0.6)]"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Horários Section -->
      <div id="horarios" class="content-section">
        <div class="card-gradient rounded-xl p-6">
          <h3 class="text-2xl font-bold mb-6 flex items-center">
            <i class="fas fa-clock text-blue-500 mr-3"></i>
            Horários Disponíveis
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="available-schedules">
            <!-- Serão carregados via JavaScript -->
          </div>
        </div>
      </div>

      <!-- Agendamentos Section -->
      <div id="agendamentos" class="content-section">
        <div class="card-gradient rounded-xl p-6">
          <div class="flex justify-between items-center mb-6">

            <h3 class="text-2xl font-bold flex items-center">
              <i class="fas fa-calendar-check text-green-500 mr-3"></i>
              Meus Agendamentos
            </h3>

            <button class="action-btn !w-auto px-5 py-3 rounded-xl font-medium shadow-lg">
              <i class="fas fa-plus mr-2"></i>Novo Agendamento
            </button>

          </div>

          <div class="space-y-4" id="appointments-list">
          </div>
        </div>
      </div>

      <!-- Treinos Section -->
      <div id="treinos" class="content-section">
        <div class="card-gradient rounded-xl p-6">
          <h3 class="text-2xl font-bold mb-6 flex items-center">
            <i class="fas fa-dumbbell text-red-500 mr-3"></i>
            Meus Planos de Treino
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="workout-plans">
          </div>
        </div>
      </div>

      <!-- Agenda Section -->
      <div id="agenda" class="content-section">
        <div class="card-gradient rounded-xl p-6">
          <div class="flex justify-between items-center mb-6">
            <button id="prev-month" class="!w-auto !justify-center !px-4 !py-2 bg-gray-800 rounded-lg hover:bg-red-600 transition-all border border-gray-700 hover:border-red-500 flex items-center">
              <i class="fas fa-chevron-left"></i>
            </button>

            <h3 id="month-year" class="text-2xl font-bold"></h3>

            <button id="next-month" class="!w-auto !justify-center !px-4 !py-2 bg-gray-800 rounded-lg hover:bg-red-600 transition-all border border-gray-700 hover:border-red-500 flex items-center">
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>

          <div class="grid grid-cols-7 gap-2 mb-4 font-bold text-center text-gray-400">
            <div>Dom</div>
            <div>Seg</div>
            <div>Ter</div>
            <div>Qua</div>
            <div>Qui</div>
            <div>Sex</div>
            <div>Sáb</div>
          </div>

          <div id="calendar-days" class="grid grid-cols-7 gap-2"></div>
        </div>
      </div>

      <!-- Perfil Section -->
      <div id="perfil" class="content-section" style="display: none;">

        <div class="card-gradient rounded-xl p-6 mb-6">
          <h3 class="text-2xl font-bold mb-6 flex items-center">
            <i class="fas fa-id-card text-red-500 mr-3"></i>TechFit Pass
          </h3>

          <div class="flex flex-col xl:flex-row gap-8 items-center justify-center">

            <div class="relative w-full max-w-sm h-56 bg-gradient-to-r from-gray-900 to-black rounded-2xl shadow-2xl overflow-hidden border border-gray-700 group transition-transform hover:scale-[1.02] flex-shrink-0">
              <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-red-600 rounded-full opacity-20 blur-xl"></div>
              <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-32 h-32 bg-red-600 rounded-full opacity-10 blur-xl"></div>

              <div class="relative p-6 h-full flex flex-col justify-between z-10">
                <div class="flex justify-between items-start">
                  <div>
                    <h2 class="text-white font-bold text-lg italic tracking-wider">TECHFIT</h2>
                    <p class="text-xs text-red-500 font-bold tracking-widest">MEMBER ACCESS</p>
                  </div>
                  <div class="w-12 h-12 rounded-full border-2 border-red-600 bg-gray-800 overflow-hidden flex items-center justify-center">
                    <img id="card-user-photo" src="" class="w-full h-full object-cover hidden">
                    <i id="card-user-icon" class="fas fa-user text-xl text-gray-400"></i>
                  </div>
                </div>
                <div>
                  <p class="text-gray-400 text-xs uppercase mb-1">Aluno(a)</p>
                  <p class="text-white font-bold text-lg truncate" id="card-name">Carregando...</p>
                </div>
                <div class="flex justify-between items-end">
                  <div>
                    <p class="text-gray-400 text-xs">ID / Token</p>
                    <p class="text-white font-mono text-sm tracking-widest" id="card-token">•••• ••••</p>
                  </div>
                  <div class="text-right">
                    <span class="px-2 py-1 bg-red-600 text-white text-xs font-bold rounded uppercase" id="card-plan">--</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 flex-1 w-full max-w-sm flex flex-col items-center justify-center shadow-lg min-h-[224px]">
              <div class="flex space-x-4 mb-6 bg-gray-900 p-1 rounded-lg">
                <button onclick="switchAccessMethod('qr')" id="tab-qr" class="px-4 py-1 rounded-md text-sm font-bold bg-gray-700 text-white transition-all">QR Code</button>
                <button onclick="switchAccessMethod('bio')" id="tab-bio" class="px-4 py-1 rounded-md text-sm font-bold text-gray-400 hover:text-white transition-all">Digital</button>
              </div>

              <div id="view-qr" class="flex flex-col items-center animate-fade-in">
                <div class="p-2 bg-white rounded-lg">
                  <div id="qrcode"></div>
                </div>
                <p class="text-xs text-gray-400 mt-3">Aproxime da catraca</p>
              </div>

              <div id="view-bio" class="hidden flex flex-col items-center animate-fade-in">
                <div class="relative w-32 h-32 flex items-center justify-center">
                  <div class="absolute inset-0 bg-red-500 opacity-20 rounded-full animate-ping"></div>
                  <i class="fas fa-fingerprint text-6xl text-red-500 animate-pulse"></i>
                </div>
                <p class="text-xs text-gray-400 mt-3">Use o leitor biométrico</p>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

          <div class="card-gradient rounded-xl p-6">
            <h3 class="text-2xl font-bold mb-6 flex items-center"><i class="fas fa-user-edit text-red-500 mr-3"></i>Editar Perfil</h3>
            <form id="profile-form" class="space-y-4">
              <div class="flex flex-col items-center mb-6">
                <div class="relative group cursor-pointer" onclick="openAvatarModal()">
                  <div class="w-24 h-24 rounded-full border-4 border-gray-700 bg-gray-800 overflow-hidden shadow-xl group-hover:border-red-500 transition-all flex items-center justify-center">
                    <img id="profile-edit-img" src="" class="w-full h-full object-cover hidden">
                    <i id="profile-edit-icon" class="fas fa-user text-3xl text-gray-400"></i>
                  </div>

                  <div class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <i class="fas fa-camera text-white"></i>
                  </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Clique para alterar</p>
              </div>
              <div>
                <label class="block text-gray-400 mb-1 text-sm">Nome Completo</label>
                <input type="text" id="profile-name" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-white focus:border-red-500 outline-none">
              </div>
              <div>
                <label class="block text-gray-400 mb-1 text-sm">Email</label>
                <input type="email" id="profile-email" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-500 cursor-not-allowed" disabled>
              </div>
              <div>
                <label class="block text-gray-400 mb-1 text-sm">Telefone</label>
                <input type="tel" id="profile-phone" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-white focus:border-red-500 outline-none">
              </div>
              <div>
                <label class="block text-gray-400 mb-1 text-sm">Nova Senha</label>
                <input type="password" id="profile-password" placeholder="Deixe em branco para manter" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-white focus:border-red-500 outline-none">
              </div>
              <div class="pt-4">
                <button type="button" id="btn-save-profile" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl transition-colors shadow-lg shadow-red-900/30">
                  Salvar Alterações
                </button>
              </div>
            </form>
          </div>

          <div class="card-gradient rounded-xl p-6">
            <h3 class="text-2xl font-bold mb-6 flex items-center"><i class="fas fa-chart-bar text-purple-500 mr-3"></i>Estatísticas</h3>
            <div class="space-y-4">
              <div class="flex justify-between items-center p-3 bg-gray-800 rounded-lg">
                <span class="text-gray-400"><i class="fas fa-calendar-alt mr-2"></i>Membro desde:</span>
                <span class="font-bold capitalize" id="stat-member-since">--</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-gray-800 rounded-lg">
                <span class="text-gray-400"><i class="fas fa-dumbbell mr-2"></i>Treinos este mês:</span>
                <span class="font-bold" id="stat-total-workouts-profile">0</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-gray-800 rounded-lg">
                <span class="text-gray-400"><i class="fas fa-chart-line mr-2"></i>Frequência estimada:</span>
                <span class="font-bold" id="stat-weekly-frequency">--</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-gray-800 rounded-lg">
                <span class="text-gray-400"><i class="fas fa-star mr-2"></i>Plano atual:</span>
                <span class="font-bold text-red-500 uppercase" id="stat-current-plan">--</span>
              </div>
            </div>
          </div>

          <div id="avatar-modal" class="fixed inset-0 bg-black/80 hidden z-[60] flex items-center justify-center backdrop-blur-sm">
            <div class="bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl w-full max-w-2xl p-6 relative">

              <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h3 class="text-xl font-bold text-white"><i class="fas fa-user-circle text-red-500 mr-2"></i>Escolha seu Avatar</h3>
                <button onclick="document.getElementById('avatar-modal').classList.add('hidden')" class="text-gray-400 hover:text-white">
                  <i class="fas fa-times text-xl"></i>
                </button>
              </div>

              <div class="grid grid-cols-2 md:grid-cols-3 gap-6 max-h-[60vh] overflow-y-auto custom-scrollbar p-2" id="avatar-grid">
              </div>

            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
  <script src="../Assets/Js/DashBoard.js"></script>
</body>

</html>