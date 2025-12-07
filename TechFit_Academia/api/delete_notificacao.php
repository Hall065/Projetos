<?php
header('Content-Type: application/json');

// Verifica se os arquivos existem para evitar erro fatal (opcional, mas boa prática)
require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

// Segurança: Garante que tem sessão e ID do usuário
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    // AJUSTE: message -> error
    echo json_encode(['success' => false, 'error' => 'Usuário não autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    // AJUSTE: message -> error
    echo json_encode(['success' => false, 'error' => 'ID da notificação não fornecido']);
    exit;
}

try {
    $conn = Conexao::getConexao();

    $userId = $_SESSION['user']['id'];

    // Deleta garantindo que a notificação pertence ao usuário logado
    $stmt = $conn->prepare("DELETE FROM notificacoes WHERE id = :id AND usuario_id = :uid");
    $stmt->bindValue(':id', $data['id']);
    $stmt->bindValue(':uid', $userId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        // AJUSTE: message -> error
        echo json_encode(['success' => false, 'error' => 'Erro ao apagar notificação no banco.']);
    }

} catch (Exception $e) {
    // AJUSTE: message -> error
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
}
?>