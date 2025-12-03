<?php
header('Content-Type: application/json');

// 1. Chama o gerente de sessão
require_once __DIR__ . '/../Config/Sessao.php';

// 2. Chama a conexão
require_once __DIR__ . '/../Database/Conexao.php';

// Segurança: Se não estiver logado, para aqui
if (!isset($_SESSION['user'])) { 
    echo json_encode(['success'=>false, 'message' => 'Erro de sessão']); 
    exit; 
}

// Lê os dados
$data = json_decode(file_get_contents('php://input'), true);

// Validação: Verifica se o ID foi enviado
if (!isset($data['id']) || empty($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do treino não fornecido']);
    exit;
}

try {
    $conn = Conexao::getConexao();
    
    // Pega ID do usuário logado (Segurança: só deleta se for seu)
    $stmtUser = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmtUser->bindValue(':email', $_SESSION['user']);
    $stmtUser->execute();
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // Deleta o treino APENAS se pertencer a este usuário
    $stmt = $conn->prepare("DELETE FROM planos_treino WHERE id = :id AND usuario_id = :uid");
    $stmt->bindValue(':id', $data['id']);
    $stmt->bindValue(':uid', $user['id']);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>