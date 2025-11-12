<?php require 'includes/header.php'; ?>

    <div class="flex">
        <div class="fixed left-0 top-0 h-full w-64 bg-gray-800 shadow-2xl z-50">
            <div class="p-6 border-b border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-red-600 to-red-800 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dumbbell text-white text-xl"></i>
                    </div>
                    <span class="text-2xl font-bold">TechFit</span>
                </div>
            </div>
            <nav class="p-6 space-y-3">
                <a href="#" class="sidebar-item active flex items-center space-x-3 px-4 py-3 rounded-lg text-white">
                    <i class="fas fa-home w-6 text-center"></i><span>Início</span>
                </a>
                <a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-400 hover:text-white">
                    <i class="fas fa-calendar-check w-6 text-center"></i><span>Agendamentos</span>
                </a>
                <a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-400 hover:text-white">
                    <i class="fas fa-dumbbell w-6 text-center"></i><span>Meus Treinos</span>
                </a>
                <a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-400 hover:text-white">
                    <i class="fas fa-user w-6 text-center"></i><span>Perfil</span>
                </a>
            </nav>
        </div>

        <div class="ml-64 flex-1">
            <header class="bg-gray-800 shadow-lg p-6 flex justify-between items-center">
                <h2 class="text-3xl font-bold">Bem-vindo de volta, <?php echo htmlspecialchars(explode(' ', $user_nome)[0]); ?>!</h2>
                
                <div class="flex items-center space-x-6">
                    <i class="fas fa-bell text-gray-400 text-xl hover:text-white cursor-pointer"></i>
                    <div class="flex items-center space-x-3">
                        <img class="w-10 h-10 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_nome); ?>&background=dc2626&color=fff" alt="Avatar">
                        <div>
                            <h4 class="font-bold"><?php echo htmlspecialchars($user_nome); ?></h4>
                            <span class="text-sm text-red-500 font-bold"><?php echo htmlspecialchars($user_plano); ?></span>
                        </div>
                        <a href="logout.php" title="Sair" class="ml-2 text-gray-400 hover:text-white">
                            <i class="fas fa-sign-out-alt text-xl"></i>
                        </a>
                    </div>
                </div>
            </header>

            <main class="p-8">
                <div id="inicio">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="card-gradient p-6 rounded-xl flex items-center space-x-4">
                            <i class="fas fa-dumbbell text-red-500 text-3xl"></i>
                            <div>
                                <span class="text-gray-400">Treinos este Mês</span>
                                <p class="text-3xl font-bold"><?php echo $treinos_mes; ?></p>
                            </div>
                        </div>
                        <div class="card-gradient p-6 rounded-xl flex items-center space-x-4">
                            <i class="fas fa-clock text-red-500 text-3xl"></i>
                            <div>
                                <span class="text-gray-400">Próximo Treino</span>
                                <?php if ($proximo_treino): ?>
                                    <p class="text-xl font-bold"><?php echo htmlspecialchars($proximo_treino['titulo']); ?></p>
                                    <span class="text-sm"><?php echo date("d/m H:i", strtotime($proximo_treino['data_inicio'])); ?></span>
                                <?php else: ?>
                                    <p class="text-xl font-bold">Nenhum</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card-gradient rounded-xl p-6">
                        <h3 class="text-2xl font-bold mb-6">Atividade Recente</h3>
                        <div class="space-y-4">
                            <?php if (empty($atividades)): ?>
                                <p class="text-gray-400">Nenhuma atividade recente registrada.</p>
                            <?php else: ?>
                                <?php foreach ($atividades as $atividade): ?>
                                    <div class="flex items-center justify-between bg-gray-700 p-4 rounded-lg">
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-check-circle text-green-500"></i>
                                            <div>
                                                <p class="font-bold"><?php echo htmlspecialchars(ucfirst($atividade['tipo'])); ?></p>
                                                <span class="text-sm text-gray-400"><?php echo htmlspecialchars($atividade['descricao']); ?></span>
                                            </div>
                                        </div>
                                        <span class="text-sm text-gray-400"><?php echo date("d/m/Y", strtotime($atividade['data'])); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
  
<?php require 'includes/footer.php'; ?>