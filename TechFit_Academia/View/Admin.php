<?php
// Inclui o Controller
require_once __DIR__ . '/../Controller/AuthController.php';

// Garante sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- BLOCO ANTI-CACHE ---
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 1. Proteção de Login
if (!isset($_SESSION['user'])) {
    header("Location: Login.php");
    exit;
}

// 2. Proteção de NÍVEL (Admin)
// Verifica se o nível NÃO é admin.
if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] !== 'admin') {
    // Fallback: Se for email admin também deixa passar
    if (strpos($_SESSION['user']['email'] ?? '', '@techfit.adm.br') === false) {
        header("Location: DashBoard.php");
        exit;
    }
}

$usuario_logado = $_SESSION['user'];
$nome_display = explode(' ', $usuario_logado['nome'] ?? 'Admin')[0];
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Painel Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../Assets/Css/Admin.css">

    <style>
        .hidden-section {
            display: none;
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
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
    </style>
</head>

<body class="bg-gray-900 text-white min-h-screen antialiased selection:bg-red-600 selection:text-white">

    <aside class="fixed left-0 top-0 h-full w-64 bg-gradient-to-b from-gray-900 to-gray-800 border-r border-gray-700 shadow-2xl z-50 flex flex-col justify-between">
        <div>
            <div class="p-6 border-b border-gray-700">
                <a href="Principal.php" class="flex items-center space-x-3 hover:opacity-80 transition-opacity">
                    <div class="w-10 h-10 bg-gradient-to-br from-red-600 to-red-800 rounded-lg flex items-center justify-center shadow-lg">
                        <i class="fas fa-dumbbell text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-wide">TechFit</h1>
                        <p class="text-[10px] text-red-500 font-bold tracking-widest uppercase">Admin Panel</p>
                    </div>
                </a>
            </div>

            <nav class="mt-8 px-3 space-y-2">
                <a href="#" data-target="section-dashboard" class="sidebar-item active border-l-4 border-red-500 flex items-center space-x-3 px-4 py-3 rounded-lg text-white bg-gradient-to-r from-red-900/20 to-transparent cursor-pointer group">
                    <i class="fas fa-chart-pie text-xl w-6 text-center group-hover:text-red-500"></i>
                    <span class="font-medium">Visão Geral</span>
                </a>
                <a href="#" data-target="section-alunos" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white cursor-pointer group">
                    <i class="fas fa-users text-xl w-6 text-center group-hover:text-red-500"></i>
                    <span class="font-medium">Alunos</span>
                </a>
                <a href="#" data-target="section-treinos" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white cursor-pointer group">
                    <i class="fas fa-dumbbell text-xl w-6 text-center group-hover:text-red-500"></i>
                    <span class="font-medium">Gerenciar Treinos</span>
                </a>
                <a href="#" data-target="section-financeiro" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white cursor-pointer group">
                    <i class="fas fa-file-invoice-dollar text-xl w-6 text-center group-hover:text-red-500"></i>
                    <span class="font-medium">Financeiro</span>
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-gray-700 bg-gray-900/50">
            <div class="flex items-center space-x-3 mb-4 px-2">
                <div class="w-8 h-8 rounded-full bg-red-600 flex items-center justify-center font-bold text-sm">
                    <?= strtoupper(substr($nome_display, 0, 1)) ?>
                </div>
                <div class="overflow-hidden">
                    <p class="text-sm font-bold truncate"><?= $nome_display ?></p>
                    <p class="text-xs text-green-500 flex items-center"><span class="w-2 h-2 rounded-full bg-green-500 mr-1 animate-pulse"></span> Online</p>
                </div>
            </div>
            <a href="logout.php" class="w-full flex items-center justify-center space-x-2 bg-gray-800 hover:bg-red-600 text-gray-400 hover:text-white py-2 rounded-lg transition-all text-sm font-medium border border-gray-700 hover:border-red-500">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sair</span>
            </a>
        </div>
    </aside>

    <main class="ml-64 min-h-screen bg-gray-900">

        <header class="sticky top-0 z-40 bg-gray-900/80 backdrop-blur-md border-b border-gray-800 px-8 py-5 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-white">Painel Administrativo</h2>
                <p class="text-gray-400 text-sm">Bem-vindo ao centro de controle da TechFit.</p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="px-3 py-1 rounded-full bg-red-500/10 text-red-500 border border-red-500/20 text-xs font-bold tracking-wide">
                    ADMINISTRADOR
                </span>
            </div>
        </header>

        <div class="p-8">

            <div id="section-dashboard" class="admin-section fade-in">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="card-gradient p-6 rounded-xl relative overflow-hidden group">
                        <div class="absolute right-0 top-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                            <i class="fas fa-users text-6xl text-blue-500"></i>
                        </div>
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-blue-500/20 flex items-center justify-center text-blue-500">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                            <h3 class="text-gray-400 font-medium">Total de Alunos</h3>
                        </div>
                        <div class="flex items-end space-x-2">
                            <span id="total-alunos-display" class="text-4xl font-bold text-white">...</span>
                        </div>
                    </div>

                    <div class="card-gradient p-6 rounded-xl relative overflow-hidden group">
                        <div class="absolute right-0 top-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                            <i class="fas fa-calendar-check text-6xl text-red-500"></i>
                        </div>
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-red-500/20 flex items-center justify-center text-red-500">
                                <i class="fas fa-dumbbell text-xl"></i>
                            </div>
                            <h3 class="text-gray-400 font-medium">Treinos Hoje</h3>
                        </div>
                        <div class="flex items-end space-x-2">
                            <span id="treinos-hoje-display" class="text-4xl font-bold text-white">...</span>
                            <span class="text-gray-500 text-sm mb-1">Agendados</span>
                        </div>
                    </div>

                    <div class="card-gradient p-6 rounded-xl relative overflow-hidden group">
                        <div class="absolute right-0 top-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                            <i class="fas fa-wallet text-6xl text-green-500"></i>
                        </div>
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-green-500/20 flex items-center justify-center text-green-500">
                                <i class="fas fa-dollar-sign text-xl"></i>
                            </div>
                            <h3 class="text-gray-400 font-medium">Faturamento (Est.)</h3>
                        </div>
                        <div class="flex items-end space-x-2">
                            <span id="faturamento-display" class="text-4xl font-bold text-white">...</span>
                            <span class="text-sm text-gray-500 mb-1">Mensal</span>
                        </div>
                    </div>
                </div>

                <div class="card-gradient rounded-xl overflow-hidden border border-gray-700">
                    <div class="p-6 border-b border-gray-700 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-user-plus text-red-500 mr-2"></i> Cadastros Recentes
                        </h3>
                    </div>

                    <div class="p-6">
                        <div id="lista-recentes" class="space-y-4">
                            <p class="text-gray-500 text-center animate-pulse">Carregando dados...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="section-alunos" class="admin-section hidden fade-in">
                <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold text-white">Gestão de Alunos</h2>
                    <div class="flex space-x-3 mt-4 md:mt-0">
                        <div class="relative">
                            <input type="text" id="busca-aluno" placeholder="Buscar por nome..."
                                class="bg-gray-800 text-gray-300 border border-gray-700 rounded-lg px-4 py-2 pl-10 focus:outline-none focus:border-red-500 w-64 transition-all">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-500"></i>
                        </div>
                        <button onclick="abrirModalNovo()" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg font-bold shadow-lg transition-all flex items-center transform hover:scale-105">
                            <i class="fas fa-plus mr-2"></i> Novo Aluno
                        </button>
                    </div>
                </div>

                <div class="card-gradient rounded-xl overflow-hidden border border-gray-700 shadow-2xl">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-800/50 text-gray-400 text-sm uppercase tracking-wider">
                                    <th class="p-4 border-b border-gray-700">Aluno</th>
                                    <th class="p-4 border-b border-gray-700">Contato</th>
                                    <th class="p-4 border-b border-gray-700">Plano</th>
                                    <th class="p-4 border-b border-gray-700">Status</th>
                                    <th class="p-4 border-b border-gray-700 text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabela-alunos-body" class="divide-y divide-gray-700">
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-gray-500">
                                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Carregando alunos...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-gray-700 flex justify-between items-center text-sm text-gray-400">
                        <span id="contador-alunos">Mostrando 0 alunos</span>
                        <div class="flex space-x-2">
                            <button class="px-3 py-1 bg-gray-800 rounded hover:bg-gray-700">&lt;</button>
                            <button class="px-3 py-1 bg-gray-800 rounded hover:bg-gray-700">&gt;</button>
                        </div>
                    </div>
                </div>
            </div>


            <div id="section-treinos" class="admin-section hidden fade-in">

                <div id="treino-busca-container" class="flex flex-col items-center justify-center py-20 transition-all duration-300">
                    <h2 class="text-3xl font-bold text-white mb-2">Gerenciar Treinos</h2>
                    <p class="text-gray-400 mb-8">Selecione um aluno para visualizar ou criar fichas de treino.</p>

                    <div class="relative w-full max-w-lg">
                        <input type="text" id="input-busca-treino" placeholder="Digite o nome do aluno..."
                            class="w-full bg-gray-800 border border-gray-700 text-white px-6 py-4 rounded-full shadow-2xl focus:outline-none focus:border-red-500 text-lg transition-all"
                            autocomplete="off">
                        <i class="fas fa-search absolute right-6 top-5 text-gray-500"></i>

                        <div id="dropdown-alunos" class="absolute top-full left-0 w-full bg-gray-800 border border-gray-700 rounded-xl mt-2 shadow-2xl overflow-hidden hidden z-20">
                        </div>
                    </div>
                </div>

                <div id="treino-area-aluno" class="hidden">
                    <div class="flex justify-between items-center mb-8 border-b border-gray-700 pb-4">
                        <div class="flex items-center space-x-4">
                            <button onclick="voltarBuscaTreino()" class="text-gray-400 hover:text-white transition-colors">
                                <i class="fas fa-arrow-left text-xl"></i>
                            </button>
                            <div>
                                <h3 id="treino-nome-aluno" class="text-2xl font-bold text-white">Nome do Aluno</h3>
                                <p id="treino-email-aluno" class="text-sm text-gray-400">email@email.com</p>
                                <input type="hidden" id="treino-id-aluno-selecionado">
                            </div>
                        </div>
                        <button onclick="abrirModalTreino()" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg font-bold shadow-lg transition-all flex items-center">
                            <i class="fas fa-plus mr-2"></i> Adicionar Treino
                        </button>
                    </div>

                    <div id="grid-treinos" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    </div>
                </div>
            </div>

            <div id="modal-treino" class="fixed inset-0 bg-black/80 hidden z-[60] flex items-center justify-center backdrop-blur-sm">
                <div class="bg-gray-900 border border-gray-700 rounded-xl p-6 w-full max-w-md shadow-2xl">
                    <div class="flex justify-between items-center mb-6">
                        <h3 id="titulo-modal-treino" class="text-xl font-bold text-white">Novo Treino</h3>
                        <button onclick="document.getElementById('modal-treino').classList.add('hidden')" class="text-gray-400 hover:text-white">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <form id="form-treino" class="space-y-4">
                        <input type="hidden" id="treino-id">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Nome do Treino (ex: Treino A - Peito)</label>
                            <input type="text" id="treino-nome" required class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-red-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Descrição / Exercícios</label>
                            <textarea id="treino-descricao" rows="5" required class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-red-500 focus:outline-none placeholder-gray-600" placeholder="Ex:&#10;Supino Reto: 4x12&#10;Crucifixo: 3x15..."></textarea>
                        </div>

                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg mt-4 transition-all shadow-lg hover:shadow-red-500/20">
                            Salvar Treino
                        </button>
                    </form>
                </div>
            </div>


            <div id="section-financeiro" class="admin-section hidden fade-in">
                <h2 class="text-3xl font-bold text-white mb-6">Controle Financeiro</h2>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

                    <div class="card-gradient p-5 rounded-xl border border-blue-500/30 relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 text-blue-500/10 text-8xl"><i class="fas fa-tag"></i></div>
                        <p class="text-blue-400 font-bold uppercase text-xs tracking-widest mb-1">Standard (R$ 89)</p>
                        <h3 id="fin-qtd-standard" class="text-2xl font-bold text-white">0 Alunos</h3>
                        <p id="fin-total-standard" class="text-gray-400 text-sm mt-1">R$ 0,00</p>
                    </div>

                    <div class="card-gradient p-5 rounded-xl border border-purple-500/30 relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 text-purple-500/10 text-8xl"><i class="fas fa-crown"></i></div>
                        <p class="text-purple-400 font-bold uppercase text-xs tracking-widest mb-1">Premium (R$ 129)</p>
                        <h3 id="fin-qtd-premium" class="text-2xl font-bold text-white">0 Alunos</h3>
                        <p id="fin-total-premium" class="text-gray-400 text-sm mt-1">R$ 0,00</p>
                    </div>

                    <div class="card-gradient p-5 rounded-xl border border-yellow-500/30 relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 text-yellow-500/10 text-8xl"><i class="fas fa-gem"></i></div>
                        <p class="text-yellow-400 font-bold uppercase text-xs tracking-widest mb-1">VIP (R$ 299)</p>
                        <h3 id="fin-qtd-vip" class="text-2xl font-bold text-white">0 Alunos</h3>
                        <p id="fin-total-vip" class="text-gray-400 text-sm mt-1">R$ 0,00</p>
                    </div>

                    <div class="bg-gradient-to-br from-green-900 to-green-800 p-5 rounded-xl border border-green-500 shadow-lg shadow-green-900/50 relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 text-white/10 text-8xl"><i class="fas fa-chart-line"></i></div>
                        <p class="text-green-200 font-bold uppercase text-xs tracking-widest mb-1">Receita Mensal Estimada</p>
                        <h3 id="fin-total-geral" class="text-3xl font-extrabold text-white mt-2">R$ 0,00</h3>
                        <p class="text-green-300 text-xs mt-2"><i class="fas fa-check-circle mr-1"></i> Atualizado agora</p>
                    </div>
                </div>

                <div class="card-gradient rounded-xl overflow-hidden border border-gray-700 shadow-2xl">
                    <div class="p-6 border-b border-gray-700 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-white">Status de Pagamentos</h3>
                        <div class="flex space-x-2 text-xs">
                            <span class="px-2 py-1 bg-green-500/20 text-green-400 rounded border border-green-500/30">Em Dia</span>
                            <span class="px-2 py-1 bg-yellow-500/20 text-yellow-400 rounded border border-yellow-500/30">Pendente</span>
                            <span class="px-2 py-1 bg-red-500/20 text-red-400 rounded border border-red-500/30">Atrasado</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-800/50 text-gray-400 text-sm uppercase tracking-wider">
                                    <th class="p-4 border-b border-gray-700">Aluno</th>
                                    <th class="p-4 border-b border-gray-700">Plano</th>
                                    <th class="p-4 border-b border-gray-700">Status</th>
                                    <th class="p-4 border-b border-gray-700 text-right">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="tabela-financeiro-body" class="divide-y divide-gray-700">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="section-financeiro" class="admin-section hidden fade-in">
                <h2 class="text-3xl font-bold mb-6">Financeiro</h2>
                <div class="card-gradient p-10 text-center rounded-xl border border-gray-700 border-dashed">
                    <i class="fas fa-file-invoice-dollar text-4xl text-gray-600 mb-4"></i>
                    <p class="text-gray-400 text-xl">Área Financeira</p>
                </div>
            </div>

        </div>
    </main>

    <div id="modal-editar" class="fixed inset-0 bg-black/80 hidden z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-gray-900 border border-gray-700 rounded-xl p-6 w-full max-w-md shadow-2xl transform transition-all scale-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-white">Editar Aluno</h3>
                <button onclick="fecharModal()" class="text-gray-400 hover:text-white transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <form id="form-editar-aluno" class="space-y-4">
                <input type="hidden" id="edit-id">

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Nome Completo</label>
                    <input type="text" id="edit-nome" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Email</label>
                    <input type="email" id="edit-email" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Telefone</label>
                    <input type="text" id="edit-telefone" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-red-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Plano</label>
                        <select id="edit-plano" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-red-500 focus:outline-none">
                            <option value="Standard">Standard</option>
                            <option value="Premium">Premium</option>
                            <option value="VIP">VIP</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Status</label>
                        <select id="edit-status" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-red-500 focus:outline-none">
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                            <option value="pendente">Pendente</option>
                            <option value="bloqueado">Bloqueado</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg mt-4 transition-all shadow-lg hover:shadow-red-500/20">
                    Salvar Alterações
                </button>
            </form>
        </div>
    </div>

    <div id="modal-sucesso" class="fixed inset-0 bg-black/90 hidden z-[60] flex items-center justify-center backdrop-blur-sm transition-opacity opacity-0">
        <div class="bg-gray-800 border border-gray-700 rounded-2xl p-8 w-full max-w-sm shadow-2xl text-center transform scale-90 transition-transform duration-300" id="modal-sucesso-content">
            <div class="w-20 h-20 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check text-4xl text-green-500 animate-bounce"></i>
            </div>

            <h3 class="text-2xl font-bold text-white mb-2">Sucesso!</h3>
            <p id="msg-sucesso-texto" class="text-gray-400 mb-6">Ação realizada com sucesso.</p>

            <button onclick="fecharModalSucesso()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg hover:shadow-green-500/20">
                Entendido
            </button>
        </div>
    </div>

    <div id="modal-novo" class="fixed inset-0 bg-black/80 hidden z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-gray-900 border border-gray-700 rounded-xl p-6 w-full max-w-md shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-white">Novo Aluno</h3>
                <button onclick="document.getElementById('modal-novo').classList.add('hidden')" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <form id="form-novo-aluno" class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Nome Completo</label>
                    <input type="text" id="novo-nome" required class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Email</label>
                    <input type="email" id="novo-email" required class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Senha Inicial</label>
                    <div class="relative">
                        <input type="password" id="novo-senha" required class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-red-500 focus:outline-none">
                        <i class="fas fa-lock absolute right-3 top-3 text-gray-500"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Telefone</label>
                    <input type="text" id="novo-telefone" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-red-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Plano</label>
                        <select id="novo-plano" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-red-500 focus:outline-none">
                            <option value="Standard">Standard</option>
                            <option value="Premium">Premium</option>
                            <option value="VIP">VIP</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Status</label>
                        <select id="novo-status" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-red-500 focus:outline-none">
                            <option value="ativo">Ativo</option>
                            <option value="pendente">Pendente</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg mt-4 transition-all shadow-lg hover:shadow-red-500/20">
                    Cadastrar Aluno
                </button>
            </form>
        </div>
    </div>

    <div id="modal-confirmar-exclusao" class="fixed inset-0 bg-black/90 hidden z-[80] flex items-center justify-center backdrop-blur-sm transition-opacity opacity-0">
        <div class="bg-gray-800 border border-gray-700 rounded-2xl p-8 w-full max-w-sm shadow-2xl text-center transform scale-90 transition-transform duration-300" id="modal-exclusao-content">

            <div class="w-20 h-20 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-6 border border-red-500/20">
                <i class="fas fa-trash-alt text-3xl text-red-500 animate-pulse"></i>
            </div>

            <h3 class="text-2xl font-bold text-white mb-2">Excluir Aluno?</h3>
            <p class="text-gray-400 mb-8 text-sm">Essa ação não pode ser desfeita. O aluno será removido permanentemente.</p>

            <input type="hidden" id="id-para-excluir">

            <div class="grid grid-cols-2 gap-3">
                <button onclick="fecharModalExclusao()" class="w-full bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 rounded-xl transition-all">
                    Cancelar
                </button>

                <button onclick="confirmarExclusaoDefinitiva()" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg hover:shadow-red-500/20">
                    Sim, Excluir
                </button>
            </div>
        </div>
    </div>

    <div id="modal-exclusao-treino" class="fixed inset-0 bg-black/90 hidden z-[90] flex items-center justify-center backdrop-blur-sm transition-opacity opacity-0">
        <div class="bg-gray-800 border border-gray-700 rounded-2xl p-8 w-full max-w-sm shadow-2xl text-center transform scale-90 transition-transform duration-300" id="modal-exclusao-treino-content">

            <div class="w-20 h-20 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-6 border border-red-500/20">
                <i class="fas fa-trash-alt text-3xl text-red-500 animate-pulse"></i>
            </div>

            <h3 class="text-2xl font-bold text-white mb-2">Excluir Treino?</h3>
            <p class="text-gray-400 mb-8 text-sm">Essa ficha de treino será removida permanentemente.</p>

            <input type="hidden" id="id-treino-para-excluir">

            <div class="grid grid-cols-2 gap-3">
                <button onclick="document.getElementById('modal-exclusao-treino').classList.add('hidden')" class="w-full bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 rounded-xl transition-all">
                    Cancelar
                </button>
                <button onclick="confirmarExclusaoTreino()" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg hover:shadow-red-500/20">
                    Sim, Excluir
                </button>
            </div>
        </div>
    </div>

    <div id="modal-sucesso" class="fixed inset-0 bg-black/90 hidden z-[100] flex items-center justify-center backdrop-blur-sm transition-opacity opacity-0">
        <div class="bg-gray-800 border border-gray-700 rounded-2xl p-8 w-full max-w-sm shadow-2xl text-center transform scale-90 transition-transform duration-300" id="modal-sucesso-content">
            <div class="w-20 h-20 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check text-4xl text-green-500 animate-bounce"></i>
            </div>
            <h3 class="text-2xl font-bold text-white mb-2">Sucesso!</h3>
            <p id="msg-sucesso-texto" class="text-gray-400 mb-6">Ação realizada com sucesso.</p>
            <button onclick="document.getElementById('modal-sucesso').classList.add('hidden')" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg hover:shadow-green-500/20">
                Entendido
            </button>
        </div>
    </div>

    <div id="modal-erro" class="fixed inset-0 bg-black/90 hidden z-[100] flex items-center justify-center backdrop-blur-sm transition-opacity opacity-0">
        <div class="bg-gray-800 border border-gray-700 rounded-2xl p-8 w-full max-w-sm shadow-2xl text-center transform scale-90 transition-transform duration-300" id="modal-erro-content">
            <div class="w-20 h-20 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-times text-4xl text-red-500"></i>
            </div>
            <h3 class="text-2xl font-bold text-white mb-2">Ops!</h3>
            <p id="msg-erro-texto" class="text-gray-400 mb-6">Ocorreu um erro inesperado.</p>
            <button onclick="document.getElementById('modal-erro').classList.add('hidden')" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg hover:shadow-red-500/20">
                Fechar
            </button>
        </div>
    </div>

    <script src="../Assets/Js/Admin.js"></script>
</body>

</html>