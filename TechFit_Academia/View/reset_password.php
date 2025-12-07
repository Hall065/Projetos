<?php
// Verifica se tem o token na URL
$token = $_GET['token'] ?? '';
if (!$token) {
    die("Token inválido ou não fornecido.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - TechFit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen">

    <div class="bg-gray-800 p-8 rounded-xl shadow-2xl w-full max-w-md border border-gray-700">
        <h2 class="text-3xl font-bold text-white mb-6 text-center">Nova Senha</h2>
        <p class="text-gray-400 text-center mb-6 text-sm">Crie uma nova senha segura para sua conta.</p>

        <form id="reset-form" onsubmit="redefinirSenha(event)">
            <input type="hidden" id="token" value="<?= htmlspecialchars($token) ?>">
            
            <div class="mb-4">
                <label class="block text-gray-400 mb-2 text-sm">Nova Senha</label>
                <input type="password" id="senha" required 
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-white focus:border-red-500 outline-none">
            </div>

            <div class="mb-6">
                <label class="block text-gray-400 mb-2 text-sm">Confirmar Nova Senha</label>
                <input type="password" id="confirma_senha" required 
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-white focus:border-red-500 outline-none">
            </div>

            <button type="submit" 
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition-colors shadow-lg shadow-red-900/50">
                Salvar Nova Senha
            </button>
        </form>
    </div>

    <script>
        async function redefinirSenha(e) {
            e.preventDefault();
            
            const token = document.getElementById('token').value;
            const senha = document.getElementById('senha').value;
            const confirma = document.getElementById('confirma_senha').value;

            // Validação visual
            if (senha !== confirma) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'As senhas não coincidem!',
                    background: '#1f2937', color: '#fff'
                });
                return;
            }

            // Envia para API
            try {
                const response = await fetch('../api/reset_password_confirm.php', {
                    method: 'POST',
                    body: JSON.stringify({ token, senha })
                });
                const result = await response.json();

                if (result.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Senha Alterada!',
                        text: 'Você já pode fazer login com a nova senha.',
                        background: '#27272a', color: '#fff'
                    });
                    window.location.href = 'Login.php';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: result.error || 'Token inválido ou expirado.',
                        background: '#27272a', color: '#fff'
                    });
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Erro', text: 'Erro de conexão.', background: '#27272a', color: '#fff' });
            }
        }
    </script>
</body>
</html>