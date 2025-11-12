<?php require 'includes/header.php'; ?>

  <div class="flex">
      <aside class="w-64 bg-gray-800 h-screen p-6">
        <h1 class="text-2xl font-bold mb-6 flex items-center"><i class="fas fa-dumbbell text-red-600 mr-2"></i>Admin</h1>
        <nav class="space-y-4">
          <a href="#" class="sidebar-item px-6 py-3 cursor-pointer flex items-center space-x-3 text-gray-300">Usuários</a>
          <a href="#" class="sidebar-item px-6 py-3 cursor-pointer flex items-center space-x-3 text-gray-300">Agendamentos</a>
          <a href="#" class="sidebar-item px-6 py-3 cursor-pointer flex items-center space-x-3 text-gray-300">Planos</a>
        </nav>
        <div class="absolute bottom-6 left-6">
            <a href="logout.php" class="sidebar-item px-6 py-3 cursor-pointer flex items-center space-x-3 text-gray-400 hover:text-white">
                <i class="fas fa-sign-out-alt"></i><span>Sair</span>
            </a>
        </div>
      </aside>
    
    <main class="flex-1 p-8">
      <h2 class="text-3xl font-bold mb-6">Dashboard Administrativo</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card-gradient p-6 rounded-lg">
          <h3 class="text-lg font-bold mb-2">Total Usuários</h3>
          <p class="text-3xl font-bold metric-number"><?php echo $total_usuarios; ?></p>
        </div>
        <div class="card-gradient p-6 rounded-lg">
          <h3 class="text-lg font-bold mb-2">Agendamentos Ativos</h3>
          <p class="text-3xl font-bold metric-number"><?php echo $agendamentos_ativos; ?></p>
        </div>
        <div class="card-gradient p-6 rounded-lg">
          <h3 class="text-lg font-bold mb-2">Planos Premium</h3>
          <p class="text-3xl font-bold metric-number"><?php echo $planos_premium; ?></p>
        </div>
      </div>
      
      <div class="card-gradient p-6 rounded-lg">
        <h3 class="text-2xl font-bold mb-4">Últimos Usuários Cadastrados</h3>
        <ul class="space-y-3">
          <?php if (empty($ultimos_usuarios)): ?>
              <li class="bg-gray-700 p-3 rounded-lg text-gray-400">Nenhum usuário cadastrado.</li>
          <?php else: ?>
              <?php foreach ($ultimos_usuarios as $usuario): ?>
                  <li class="flex justify-between bg-gray-700 p-3 rounded-lg">
                    <span><?php echo htmlspecialchars($usuario['nome']); ?></span>
                    <span class="text-gray-400"><?php echo date("d/m/Y", strtotime($usuario['data_cadastro'])); ?></span>
                  </li>
              <?php endforeach; ?>
          <?php endif; ?>
        </ul>
      </div>
    </main>
  </div>

<?php require 'includes/footer.php'; ?>