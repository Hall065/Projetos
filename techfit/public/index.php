<?php
// public/index.php

require_once '../controllers/DashboardController.php';

$controller = new DashboardController();
$userData = $controller->getUserData();
$dashboardStats = $controller->getDashboardStats();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit Dashboard</title>
    <link rel="stylesheet" href="css/DashBoard.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos para gerenciar a exibição das seções */
        .content-section {
            display: none;
        }
        
        .content-section.active {
            display: block;
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Estilo para o item ativo do menu */
        .sidebar-item.active {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.4);
        }
        
        .sidebar-item:not(.active):hover {
            background: rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen">
    <!-- Sidebar -->
    <div class="fixed left-0 top-0 h-full w-64 bg-gradient-to-b from-gray-900 to-gray-800 shadow-2xl z-50 border-r border-gray-700">
        <!-- Logo -->
        <div class="p-6 border-b border-gray-700">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-800 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-dumbbell text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">TechFit</h1>
                    <p class="text-xs text-gray-400">Fitness Pro</p>
                </div>
            </div>
        </div>
        
        <!-- Menu Items -->
        <nav class="mt-6 px-3">
            <div class="sidebar-item active px-4 py-3 rounded-lg cursor-pointer flex items-center space-x-3 mb-1 transition-all" data-section="inicio">
                <i class="fas fa-home text-lg w-5"></i>
                <span class="font-medium">Início</span>
            </div>
            <div class="sidebar-item px-4 py-3 rounded-lg cursor-pointer flex items-center space-x-3 mb-1 transition-all" data-section="horarios">
                <i class="fas fa-clock text-lg w-5"></i>
                <span class="font-medium">Horários</span>
            </div>
            <div class="sidebar-item px-4 py-3 rounded-lg cursor-pointer flex items-center space-x-3 mb-1 transition-all" data-section="agendamentos">
                <i class="fas fa-calendar-check text-lg w-5"></i>
                <span class="font-medium">Agendamentos</span>
            </div>
            <div class="sidebar-item px-4 py-3 rounded-lg cursor-pointer flex items-center space-x-3 mb-1 transition-all" data-section="treinos">
                <i class="fas fa-dumbbell text-lg w-5"></i>
                <span class="font-medium">Meus Treinos</span>
            </div>
            <div class="sidebar-item px-4 py-3 rounded-lg cursor-pointer flex items-center space-x-3 mb-1 transition-all" data-section="agenda">
                <i class="fas fa-calendar text-lg w-5"></i>
                <span class="font-medium">Calendário</span>
            </div>
            <div class="sidebar-item px-4 py-3 rounded-lg cursor-pointer flex items-center space-x-3 mb-1 transition-all" data-section="perfil">
                <i class="fas fa-user text-lg w-5"></i>
                <span class="font-medium">Perfil</span>
            </div>
        </nav>

        <!-- User Quick Stats -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-700 bg-gray-900">
            <div class="text-center">
                <p class="text-sm text-gray-400">Sequência Atual</p>
                <p class="text-2xl font-bold metric-number" id="sidebar-streak"><?php echo $dashboardStats['streak']; ?> dias</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="ml-64 min-h-screen">
        <!-- Header -->
        <header class="bg-gradient-to-r from-gray-900 to-gray-800 shadow-xl p-6 border-b border-gray-700 sticky top-0 z-40 backdrop-blur-lg">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold" id="page-title">Dashboard</h2>
                    <p class="text-gray-400 mt-1">Bem-vindo de volta, <span id="user-name" class="text-red-500 font-semibold"><?php echo explode(' ', $userData['name'])[0]; ?></span>!</p>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="relative">
                        <i class="fas fa-bell text-2xl text-gray-400 cursor-pointer hover:text-red-500 transition-colors"></i>
                        <span class="notification-badge absolute -top-2 -right-2 bg-red-600 text-xs text-white rounded-full w-6 h-6 flex items-center justify-center font-bold shadow-lg" id="notification-count"><?php echo $userData['notifications']; ?></span>
                    </div>

                    <div class="relative">
                        <button id="profile-btn" class="flex items-center space-x-3 hover:bg-gray-800 px-3 py-2 rounded-lg transition-colors focus:outline-none">
                            <div class="w-11 h-11 bg-gradient-to-br from-red-600 to-red-800 rounded-full flex items-center justify-center shadow-lg ring-2 ring-red-500 ring-opacity-50">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div class="text-left">
                                <p class="font-semibold" id="header-user-name"><?php echo $userData['name']; ?></p>
                                <p class="text-sm text-gray-400" id="user-plan">Membro <?php echo $userData['plan']; ?></p>
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 ml-2 transition-transform" id="dropdown-icon"></i>
                        </button>

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
                            <a href="#" class="block px-4 py-3 hover:bg-gray-700 transition-colors text-red-400 flex items-center space-x-2">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Sair</span>
                            </a>
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
                                <p class="text-4xl font-bold metric-number" id="stat-monthly-workouts"><?php echo $dashboardStats['monthly_workouts']; ?></p>
                                <p class="text-green-500 text-xs mt-1"><i class="fas fa-arrow-up"></i> +12% vs mês anterior</p>
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
                                <p class="text-xl font-bold text-white" id="stat-next-workout"><?php echo $dashboardStats['next_workout']; ?></p>
                                <p class="text-gray-500 text-xs mt-1" id="stat-next-workout-type"><?php echo $dashboardStats['next_workout_type']; ?></p>
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
                                <p class="text-4xl font-bold metric-number" id="stat-calories"><?php echo number_format($dashboardStats['calories'], 0, ',', '.'); ?></p>
                                <p class="text-orange-500 text-xs mt-1"><i class="fas fa-fire"></i> Este mês</p>
                            </div>
                            <div class="w-14 h-14 bg-gradient-to-br from-orange-600 to-orange-800 rounded-xl flex items-center justify-center shadow-lg stat-icon">
                                <i class="fas fa-fire text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-gradient rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-400 text-sm mb-1">Sequência</p>
                                <p class="text-4xl font-bold metric-number" id="stat-streak"><?php echo $dashboardStats['streak']; ?></p>
                                <p class="text-sm text-gray-400">dias consecutivos</p>
                            </div>
                            <div class="w-14 h-14 bg-gradient-to-br from-purple-600 to-purple-800 rounded-xl flex items-center justify-center shadow-lg stat-icon">
                                <i class="fas fa-trophy text-white text-xl"></i>
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
                            <button class="w-full action-btn p-4 rounded-xl text-left flex items-center justify-between group" data-navigate="agendamentos">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-calendar-plus text-xl"></i>
                                    <span class="font-medium">Agendar Treino</span>
                                </div>
                                <i class="fas fa-arrow-right opacity-0 group-hover:opacity-100 transition-opacity"></i>
                            </button>
                            <button class="w-full bg-gray-800 hover:bg-gray-700 p-4 rounded-xl text-left flex items-center justify-between transition-all group border border-gray-700" data-navigate="treinos">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-list text-xl"></i>
                                    <span class="font-medium">Ver Plano de Treino</span>
                                </div>
                                <i class="fas fa-arrow-right opacity-0 group-hover:opacity-100 transition-opacity"></i>
                            </button>
                            <button class="w-full bg-gray-800 hover:bg-gray-700 p-4 rounded-xl text-left flex items-center justify-between transition-all group border border-gray-700" data-navigate="agenda">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-chart-line text-xl"></i>
                                    <span class="font-medium">Acompanhar Progresso</span>
                                </div>
                                <i class="fas fa-arrow-right opacity-0 group-hover:opacity-100 transition-opacity"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card-gradient rounded-xl p-6">
                        <h3 class="text-2xl font-bold mb-6 flex items-center">
                            <i class="fas fa-history text-blue-500 mr-3"></i>
                            Atividade Recente
                        </h3>
                        <div class="space-y-3" id="recent-activities">
                            <div class="flex items-center space-x-3 p-4 bg-gray-800 rounded-xl border border-gray-700 hover:border-green-500 transition-colors">
                                <div class="w-10 h-10 bg-gradient-to-br from-green-600 to-green-800 rounded-full flex items-center justify-center shadow-lg flex-shrink-0">
                                    <i class="fas fa-check text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium">Treino de Peito concluído</p>
                                    <p class="text-sm text-gray-400">Há 2 horas</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3 p-4 bg-gray-800 rounded-xl border border-gray-700 hover:border-blue-500 transition-colors">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-800 rounded-full flex items-center justify-center shadow-lg flex-shrink-0">
                                    <i class="fas fa-calendar text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium">Treino agendado para amanhã</p>
                                    <p class="text-sm text-gray-400">Há 1 dia</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3 p-4 bg-gray-800 rounded-xl border border-gray-700 hover:border-purple-500 transition-colors">
                                <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-purple-800 rounded-full flex items-center justify-center shadow-lg flex-shrink-0">
                                    <i class="fas fa-trophy text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium">Meta semanal atingida!</p>
                                    <p class="text-sm text-gray-400">Há 2 dias</p>
                                </div>
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
                        <button class="action-btn px-5 py-3 rounded-xl font-medium shadow-lg" data-navigate="horarios">
                            <i class="fas fa-plus mr-2"></i>Novo Agendamento
                        </button>
                    </div>
                    <div class="space-y-4" id="appointments-list">
                        <!-- Serão carregados via JavaScript -->
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
                        <!-- Serão carregados via JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Agenda Section -->
            <div id="agenda" class="content-section">
                <div class="card-gradient rounded-xl p-6">
                    <div class="flex justify-between items-center mb-6">
                        <button id="prev-month" class="px-4 py-2 bg-gray-800 rounded-lg hover:bg-red-600 transition-all border border-gray-700 hover:border-red-500">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <h3 id="month-year" class="text-2xl font-bold"></h3>
                        <button id="next-month" class="px-4 py-2 bg-gray-800 rounded-lg hover:bg-red-600 transition-all border border-gray-700 hover:border-red-500">
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
            <div id="perfil" class="content-section">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="card-gradient rounded-xl p-6">
                        <h3 class="text-2xl font-bold mb-6 flex items-center">
                            <i class="fas fa-user-edit text-blue-500 mr-3"></i>
                            Informações Pessoais
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-400 mb-2 font-medium">Nome Completo</label>
                                <input type="text" id="input-fullname" value="<?php echo $userData['name']; ?>" class="w-full bg-gray-800 p-3 rounded-lg text-white border border-gray-700 focus:border-red-500 focus:outline-none transition-colors">
                            </div>
                            <div>
                                <label class="block text-gray-400 mb-2 font-medium">Email</label>
                                <input type="email" id="input-email" value="<?php echo $userData['email']; ?>" class="w-full bg-gray-800 p-3 rounded-lg text-white border border-gray-700 focus:border-red-500 focus:outline-none transition-colors">
                            </div>
                            <div>
                                <label class="block text-gray-400 mb-2 font-medium">Telefone</label>
                                <input type="tel" id="input-phone" value="<?php echo $userData['phone']; ?>" class="w-full bg-gray-800 p-3 rounded-lg text-white border border-gray-700 focus:border-red-500 focus:outline-none transition-colors">
                            </div>
                            <button class="action-btn w-full py-3 rounded-xl font-medium shadow-lg">
                                <i class="fas fa-save mr-2"></i>Salvar Alterações
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-gradient rounded-xl p-6">
                        <h3 class="text-2xl font-bold mb-6 flex items-center">
                            <i class="fas fa-chart-bar text-purple-500 mr-3"></i>
                            Estatísticas
                        </h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-3 bg-gray-800 rounded-lg">
                                <span class="text-gray-400"><i class="fas fa-calendar-alt mr-2"></i>Membro desde:</span>
                                <span class="font-bold" id="stat-member-since"><?php echo $userData['member_since']; ?></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-800 rounded-lg">
                                <span class="text-gray-400"><i class="fas fa-dumbbell mr-2"></i>Total de treinos:</span>
                                <span class="font-bold" id="stat-total-workouts"><?php echo $dashboardStats['total_workouts']; ?></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-800 rounded-lg">
                                <span class="text-gray-400"><i class="fas fa-chart-line mr-2"></i>Frequência semanal:</span>
                                <span class="font-bold" id="stat-weekly-frequency"><?php echo $dashboardStats['weekly_frequency']; ?> dias</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-800 rounded-lg">
                                <span class="text-gray-400"><i class="fas fa-star mr-2"></i>Plano atual:</span>
                                <span class="font-bold text-red-500" id="stat-current-plan"><?php echo $userData['plan']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
        <!-- Script de Navegação Inline -->
        <script>
        // Títulos das seções
        const sectionTitles = {
            'inicio': 'Dashboard',
            'horarios': 'Horários',
            'agendamentos': 'Agendamentos',
            'treinos': 'Meus Treinos',
            'agenda': 'Calendário',
            'perfil': 'Perfil'
        };

        // Função de navegação
        function navigateTo(sectionId) {
            // Remove active de todas as seções
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Remove active de todos os itens do menu
            document.querySelectorAll('.sidebar-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Ativa a seção selecionada
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.classList.add('active');
                
                // Atualiza o título da página
                const pageTitle = document.getElementById('page-title');
                if (pageTitle) {
                    pageTitle.textContent = sectionTitles[sectionId] || 'Dashboard';
                }
                
                // Ativa o item do menu correspondente
                const menuItem = document.querySelector(`.sidebar-item[data-section="${sectionId}"]`);
                if (menuItem) {
                    menuItem.classList.add('active');
                }
                
                // Scroll para o topo
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        // Event listeners para os itens do menu
        document.addEventListener('DOMContentLoaded', function() {
            // Menu lateral
            document.querySelectorAll('.sidebar-item').forEach(item => {
                item.addEventListener('click', function() {
                    const section = this.getAttribute('data-section');
                    navigateTo(section);
                });
            });
            
            // Botões com data-navigate
            document.querySelectorAll('[data-navigate]').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const section = this.getAttribute('data-navigate');
                    navigateTo(section);
                });
            });
        });
    </script>    
    <script src="js/dashboard.js"></script>
</body>
</html>