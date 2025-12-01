<?php
// Aqui você pode colocar a lógica de login
$erro = $erro ?? '';
$message = $message ?? '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TechFit - Login</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="Assets/Css/Login.css">
</head>
<body>

  <div class="login-container">

    <div class="login-header">
      <div class="login-logo"><i class="fas fa-dumbbell"></i></div>
      <h1 class="login-title">TechFit</h1>
    </div>

    <h2 class="login-subtitle">Entrar na sua conta</h2>

    <?php if (!empty($erro)) : ?>
      <p class="error-msg"><?= $erro ?></p>
    <?php endif; ?>

    <form method="POST">
      <label class="login-label">Email</label>
      <input type="email" name="email" class="login-input" placeholder="seu@email.com">

      <label class="login-label">Senha</label>
      <input type="password" name="password" class="login-input" placeholder="••••••••">

      <button type="submit" name="login" class="action-btn">Entrar</button>
    </form>

    <?php if(!empty($message)) echo "<p class='alert-msg'>$message</p>"; ?>

    <p class="login-footer">
      Não tem conta? 
      <a href="?rota=cadastro" class="action-link">Cadastrar</a>
    </p>

  </div>

</body>
</html>
