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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

  <div class="login-container">

    <div class="login-header">
      <a href="Principal.php" style="text-decoration: none; color: inherit; display: flex; flex-direction: row; align-items: center; justify-content: center; gap: 15px;">
        <div class="login-logo"><i class="fas fa-dumbbell"></i></div>
        <h1 class="login-title" style="margin: 0;">TechFit</h1>
      </a>
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
      <input type="email" name="email" class="login-input" placeholder="Email" required>

      <label class="login-label">Senha</label>
      <div style="position: relative; width: 100%;">
        <input type="password"
          name="password"
          id="senha-login"
          class="login-input"
          placeholder="••••••••"
          required
          style="padding-right: 40px;">
        <i class="fas fa-eye"
          onclick="togglePassword('senha-login', this)"
          style="position: absolute; right: 15px; top: 40%; transform: translateY(-60%); cursor: pointer; color: #888;">
        </i>
      </div>

      <button type="submit" name="login" class="action-btn">Entrar</button>
    </form>

    <div class="login-footer">
      <p>Não tem uma conta? <a href="Cadastro.php">Cadastre-se</a></p>
    </div>
    <div class="login-footer">
      <a href="#" onclick="abrirRecuperacaoSenha(event)" class="text-sm text-gray-400 hover:text-red-500 transition-colors">
        Esqueceu a senha?
      </a>
    </div>

  </div>

  <script>
    async function abrirRecuperacaoSenha(event) {
      if (event) event.preventDefault();

      // Pede o e-mail
      const {
        value: email
      } = await Swal.fire({
        title: 'Recuperar Senha',
        input: 'email',
        inputLabel: 'Digite seu e-mail cadastrado',
        inputPlaceholder: 'seu@email.com',
        background: '#1f2937',
        color: '#fff',
        confirmButtonText: 'Enviar Link',
        confirmButtonColor: '#dc2626',
        showCancelButton: true,
        cancelButtonText: 'Cancelar'
      });

      if (email) {
        Swal.fire({
          title: 'Processando...',
          didOpen: () => Swal.showLoading(),
          background: '#1f2937',
          color: '#fff'
        });

        try {
          // Chama a API que criamos
          const response = await fetch('../api/forgot_password.php', {
            method: 'POST',
            body: JSON.stringify({
              email: email
            })
          });
          const result = await response.json();

          if (result.success) {
            // Mostra o link (Simulação Local)
            await Swal.fire({
              icon: 'success',
              title: 'Link Gerado!',
              html: `
                  <p style="color: #d1d5db; margin-bottom: 20px;">Simulação de envio (clique abaixo):</p>
                  
                  <div style="text-align: center;">
                      <a href="${result.link}" 
                        style="
                            display: inline-block; 
                            background-color: #dc2626 !important; 
                            color: white !important; 
                            font-weight: bold; 
                            padding: 10px 24px;       /* MUDANÇA: Mais fino (10px) e largura ajustada ao texto */
                            border-radius: 6px;       /* Bordas um pouco mais discretas */
                            text-decoration: none; 
                            font-size: 14px;          /* Texto num tamanho mais elegante */
                            box-shadow: 0 4px 6px rgba(220, 38, 38, 0.4);
                        "
                        onmouseover="this.style.backgroundColor='#b91c1c'" 
                        onmouseout="this.style.backgroundColor='#dc2626'">
                        REDEFINIR SENHA
                      </a>
                  </div>
                  
                  <p style="color: #6b7280; font-size: 12px; margin-top: 20px;">Link válido por 1 hora</p>
              `,
              background: '#1f2937',
              color: '#fff',
              showConfirmButton: false,
              showCloseButton: true
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Erro',
              text: result.error,
              background: '#1f2937',
              color: '#fff'
            });
          }
        } catch (error) {
          Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Falha na conexão.',
            background: '#1f2937',
            color: '#fff'
          });
        }
      }
    }

    // Função genérica para mostrar/ocultar senha
    function togglePassword(inputId, icon) {
      const input = document.getElementById(inputId);

      if (input.type === 'password') {
        // Vira texto (mostra a senha)
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash'); // Muda ícone para "olho cortado"
        icon.classList.add('text-red-500'); // (Opcional) Deixa vermelho quando visível
      } else {
        // Vira password (esconde a senha)
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        icon.classList.remove('text-red-500');
      }
    }
  </script>

</body>

</html>