<?php require 'includes/header.php'; ?>

  <header class="flex justify-between items-center p-6 bg-gray-800 shadow-lg">
    <h1 class="text-2xl font-bold flex items-center"><i class="fas fa-dumbbell text-red-600 mr-2"></i>TechFit</h1>
    <nav class="space-x-6">
      <a href="#servicos" class="hover:text-red-500">Serviços</a>
      <a href="#planos" class="hover:text-red-500">Planos</a>
      <a href="#contato" class="hover:text-red-500">Contato</a>
      
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?php echo $_SESSION['user_tipo'] == 'admin' ? 'admin.php' : 'home.php'; ?>" class="action-btn px-4 py-2 rounded-lg">Meu Painel</a>
        <a href="logout.php" class="hover:text-red-500">Sair</a>
      <?php else: ?>
        <a href="login.php" class="action-btn px-4 py-2 rounded-lg">Login</a>
      <?php endif; ?>
    </nav>
  </header>

  <section class="text-center py-20 px-6 bg-gray-800">
    <h2 class="text-5xl font-bold mb-4">Alcance seus objetivos na <span class="text-red-600">TechFit</span></h2>
    <p class="text-xl text-gray-400 mb-8">Treinamento, tecnologia e resultados.</p>
    <a href="<?php echo isset($_SESSION['user_id']) ? 'home.php' : 'cadastro.php'; ?>" class="action-btn text-lg px-8 py-3 rounded-lg font-bold">Comece Agora</a>
  </section>

  <?php require 'includes/footer.php'; ?>