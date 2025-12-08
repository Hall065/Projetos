<?php
// Arquivo: api/get_user.php (Perfil do Aluno Logado)
header('Content-Type: application/json');
require_once __DIR__ . '/../Database/Conexao.php';
require_once __DIR__ . '/../Config/Sessao.php';

// 1. Verifica login
if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'Não autenticado']);
    exit;
}

try {
    $conn = Conexao::getConexao();
    $email = is_array($_SESSION['user']) ? $_SESSION['user']['email'] : $_SESSION['user'];

    // 2. Busca os dados do usuário logado
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Remove dados sensíveis
        unset($user['senha']);
        unset($user['reset_token']);
        unset($user['reset_expires']);

        // --- LÓGICA DO QR CODE (NOVIDADE) ---
        // Se o usuário ainda não tem token, cria um agora
        if (empty($user['access_token'])) {
            // Gera Token: TF + ID + CodigoAleatorio (Ex: TF-15-A1B2)
            $token = 'TF-' . $user['id'] . '-' . strtoupper(bin2hex(random_bytes(4)));
            
            // Salva no banco
            $update = $conn->prepare("UPDATE usuarios SET access_token = :token WHERE id = :id");
            $update->bindValue(':token', $token);
            $update->bindValue(':id', $user['id']);
            $update->execute();
            
            // Atualiza a variável para devolver pro JS
            $user['access_token'] = $token;
        }
        // ------------------------------------

        echo json_encode($user);
    } else {
        echo json_encode(['error' => 'Usuário não encontrado']);
    }

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>