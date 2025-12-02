<?php
// CRÍTICO: Inclui o Controller para processar o formulário
require_once __DIR__ . '/../Controller/AuthController.php';

// A variável $message é definida no AuthController.php
// Se o AuthController não retornar nada, $message será null.
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TechFit - Login</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../Assets/Css/Login.css"> 
</head>
<body>

  <div class="login-container">

    <div class="login-header">
      <div class="login-logo"><i class="fas fa-dumbbell"></i></div>
      <h1 class="login-title">TechFit</h1>
    </div>

    <h2 class="login-subtitle">Entrar na sua conta</h2>

    <?php 
    // Verifica se $message existe, é um array e tem texto para exibir
    if (isset($message) && is_array($message) && !empty($message['text'])) : 
        // Define a classe CSS (Tailwind) baseada no 'type' retornado
        // Assumindo que você está usando classes Tailwind no seu CSS local para 'bg-red-500', etc.
        $alert_class = ($message['type'] === 'success') 
            ? 'bg-green-500 border-green-700' 
            : 'bg-red-500 border-red-700';
    ?>
        <div class="alert <?= $alert_class ?> text-white p-3 mb-4 rounded-lg border-l-4 font-medium" style="color: white; padding: 10px; margin-bottom: 15px; border-radius: 5px; border-left: 5px solid;">
            <?= htmlspecialchars($message['text']) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
      <label class="login-label">Email</label>
      <input type="email" name="email" class="login-input" placeholder="seu@email.com" required>

      <label class="login-label">Senha</label>
      <input type="password" name="password" class="login-input" placeholder="••••••••" required>

      <button type="submit" name="login" class="action-btn">Entrar</button>
    </form>

    <div class="login-footer">
      <p>Não tem uma conta? <a href="Cadastro.php">Cadastre-se</a></p>
      <a href="#">Esqueci minha senha</a>
    </div>

  </div>

</body>
</html>