<?php
header('Content-Type: application/json');

// 1. Configurações
require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

// Segurança
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID da notificação não fornecido']);
    exit;
}

try {
    $conn = Conexao::getConexao();

    // Pega ID do usuário
    $stmtUser = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmtUser->bindValue(':email', $_SESSION['user']);
    $stmtUser->execute();
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // Deleta a notificação (apenas se pertencer ao usuário)
    $stmt = $conn->prepare("DELETE FROM notificacoes WHERE id = :id AND usuario_id = :uid");
    $stmt->bindValue(':id', $data['id']);
    $stmt->bindValue(':uid', $user['id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao apagar.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
?>