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
  <title>TechFit - Cadastro</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../Assets/Css/Cadastro.css">
</head>

<body>

  <div class="register-container">

    <div class="register-header">
      <a href="Principal.php" style="text-decoration: none; color: inherit; display: flex; flex-direction: row; align-items: center; justify-content: center; gap: 15px;">
        <div class="register-logo"><i class="fas fa-dumbbell"></i></div>
        <h1 class="register-title" style="margin: 0;">TechFit</h1>
      </a>
    </div>

    <h2 class="register-subtitle">Crie sua conta</h2>

    <?php
    if (isset($message) && is_array($message) && !empty($message['text'])) :
      $alert_class = ($message['type'] === 'success')
        ? 'bg-green-500 border-green-700'
        : 'bg-red-500 border-red-700';
    ?>
      <div class="alert <?= $alert_class ?> text-white p-3 mb-4 rounded-lg border-l-4 font-medium" style="color: white; padding: 10px; margin-bottom: 15px; border-radius: 5px; border-left: 5px solid;">
        <?= htmlspecialchars($message['text']) ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <label class="register-label">Nome Completo</label>
      <input type="text" name="name" class="register-input" placeholder="Seu nome" required>

      <label class="register-label">Email</label>
      <input type="email" name="email" class="register-input" placeholder="seu@email.com" required>

      <label class="register-label">Telefone</label>
      <input type="tel" id="novo-telefone" name="phone" class="register-input" placeholder="(99) 99999-9999" required>

      <label class="register-label">Senha</label>
      <div style="position: relative; width: 100%; margin-bottom: 15px;">
        <input type="password"
          name="password"
          id="senha-cadastro"
          class="register-input"
          placeholder="Sua Senha"
          required
          style="padding-right: 40px;">

        <i class="fas fa-eye"
          onclick="togglePassword('senha-cadastro', this)"
          style="position: absolute; right: 15px; top: 40%; transform: translateY(-60%); cursor: pointer; color: #888;">
        </i>
      </div>

      <label class="register-label">Confirmar Senha</label>
      <div style="position: relative; width: 100%; margin-bottom: 15px;">
        <input type="password"
          name="confirm_password"
          id="confirma-senha"
          class="register-input"
          placeholder="Digite novamente"
          required
          style="padding-right: 40px;">

        <i class="fas fa-eye"
          onclick="togglePassword('confirma-senha', this)"
          style="position: absolute; right: 15px; top: 40%; transform: translateY(-60%); cursor: pointer; color: #888;">
        </i>
      </div>

      <button type="submit" name="register" class="action-btn">Cadastrar</button>
    </form>

    <div class="register-footer">
      <p>Já tem uma conta? <a href="Login.php">Fazer Login</a></p>
    </div>
  </div>

  <script>
    // Função para mostrar/ocultar senha (Reutilizável)
    function togglePassword(inputId, icon) {
      const input = document.getElementById(inputId);

      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        icon.style.color = '#dc2626'; // Opcional: fica vermelho quando visível
      } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        icon.style.color = '#888'; // Volta a ser cinza
      }
    }

    // Máscara de Telefone (formata enquanto digita)
    document.getElementById('novo-telefone').addEventListener('input', function(e) {
      var x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
      e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
    });
  </script>

</body>

</html>