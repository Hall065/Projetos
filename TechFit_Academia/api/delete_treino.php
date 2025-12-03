<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

// Segurança
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) { 
    echo json_encode(['success'=>false, 'message' => 'Erro de sessão']); 
    exit; 
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || empty($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do treino não fornecido']);
    exit;
}

try {
    $conn = Conexao::getConexao();
    
    // 1. OTIMIZAÇÃO: Pega ID direto da sessão
    $userId = $_SESSION['user']['id'];

    // 2. Deleta o treino APENAS se pertencer a este usuário
    $stmt = $conn->prepare("DELETE FROM planos_treino WHERE id = :id AND usuario_id = :uid");
    $stmt->bindValue(':id', $data['id']);
    $stmt->bindValue(':uid', $userId);

    if ($stmt->execute()) {
        // Verifica se algo foi realmente deletado
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Treino não encontrado ou permissão negada.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>