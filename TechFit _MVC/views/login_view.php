<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TechFit - Login</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="css/Cadastro.css"> 
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">
  <div class="w-full max-w-md bg-gray-800 rounded-xl shadow-2xl p-8">
    
    <div class="flex items-center justify-center mb-6">
      <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-800 rounded-lg flex items-center justify-center">
        <i class="fas fa-dumbbell text-white text-xl"></i>
      </div>
      <h1 class="ml-3 text-3xl font-bold">TechFit</h1>
    </div>
    
    <h2 class="text-xl font-semibold mb-4 text-center">Acesse sua conta</h2>

    <?php if (isset($login_error)): ?>
      <div class="bg-red-800 border border-red-600 text-white px-4 py-3 rounded-lg mb-4 text-center">
        <?php echo htmlspecialchars($login_error); ?>
      </div>
    <?php endif; ?>
    <?php if (isset($register_success)): ?>
      <div class="bg-green-800 border border-green-600 text-white px-4 py-3 rounded-lg mb-4 text-center">
        <?php echo htmlspecialchars($register_success); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="controllers/AuthController.php?action=login">
      <div class="mb-4">
        <label class="block mb-2 text-gray-400">Email</label>
        <input type="email" name="email" class="w-full p-3 bg-gray-700 rounded-lg text-white" placeholder="seu@email.com" required>
      </div>
      <div class="mb-6">
        <label class="block mb-2 text-gray-400">Senha</label>
        <input type="password" name="senha" class="w-full p-3 bg-gray-700 rounded-lg text-white" placeholder="Sua senha" required>
      </div>
      <button type="submit" class="action-btn w-full p-3 rounded-lg text-lg font-bold">
        Entrar
      </button>
    </form>
    <p class="text-center text-gray-400 mt-6">
      Não tem uma conta? <a href="cadastro.php" class="text-red-500 hover:text-red-400 font-bold">Cadastre-se</a>
    </p>
  </div>
</body>
</html>