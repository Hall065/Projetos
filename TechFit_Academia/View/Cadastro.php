<?php
// Aqui você pode colocar a lógica de cadastro
$erro = $erro ?? '';
$message = $message ?? '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TechFit - Cadastro</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="Assets/Css/Cadastro.css">
</head>
<body>

  <div class="register-container">

    <div class="register-header">
      <div class="register-logo"><i class="fas fa-dumbbell"></i></div>
      <h1 class="register-title">TechFit</h1>
    </div>

    <h2 class="register-subtitle">Crie sua conta</h2>

    <?php if (!empty($erro)) : ?>
      <p class="error-msg"><?= $erro ?></p>
    <?php endif; ?>

    <form method="POST">
      <label class="register-label">Nome Completo</label>
      <input type="text" name="name" class="register-input" placeholder="Seu nome">

      <label class="register-label">Email</label>
      <input type="email" name="email" class="register-input" placeholder="seu@email.com">

      <label class="register-label">Telefone</label>
      <input type="tel" name="phone" class="register-input" placeholder="(99) 99999-9999">

      <label class="register-label">Senha</label>
      <input type="password" name="password" class="register-input" placeholder="••••••••">

      <button type="submit" name="register" class="action-btn">Cadastrar</button>
    </form>

    <?php if(!empty($message)) echo "<p class='alert-msg'>$message</p>"; ?>

    <p class="register-footer">
      Já tem conta? 
      <a href="?rota=login" class="action-link">Entrar</a>
    </p>

  </div>

</body>
</html>
