<?php
// Arquivo: api/update_avatar.php (VERSÃO DEBUG)
header('Content-Type: application/json');
require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

// 1. Verifica Sessão
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Você não está logado. Recarregue a página.']);
    exit;
}

// Tenta pegar o ID de várias formas (compatibilidade)
$userId = null;
if (isset($_SESSION['user']['id'])) {
    $userId = $_SESSION['user']['id'];
} elseif (is_array($_SESSION['user']) && isset($_SESSION['user']['id_usuario'])) {
    $userId = $_SESSION['user']['id_usuario'];
}

if (!$userId) {
    echo json_encode(['success' => false, 'error' => 'ID do usuário não encontrado na sessão. Faça logout e login novamente.']);
    exit;
}

// 2. Recebe dados
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['avatar'])) {
    echo json_encode(['success' => false, 'error' => 'Nenhum avatar enviado pelo Javascript.']);
    exit;
}

try {
    $conn = Conexao::getConexao();

    // 3. Atualiza
    $stmt = $conn->prepare("UPDATE usuarios SET foto = :foto WHERE id = :id");
    $stmt->bindValue(':foto', $data['avatar']);
    $stmt->bindValue(':id', $userId);
    
    if ($stmt->execute()) {
        // Atualiza sessão
        if(is_array($_SESSION['user'])) {
            $_SESSION['user']['foto'] = $data['avatar'];
        }
        echo json_encode(['success' => true]);
    } else {
        // Mostra o erro real do SQL
        $errorInfo = $stmt->errorInfo();
        echo json_encode(['success' => false, 'error' => 'Erro SQL: ' . $errorInfo[2]]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erro Exception: ' . $e->getMessage()]);
}
?>