<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

// Verifica sessão e ID
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) { 
    echo json_encode(['success'=>false, 'message'=>'Sessão expirada. Faça login novamente.']); 
    exit; 
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['name'])) {
    echo json_encode(['success' => false, 'message' => 'Nome do treino é obrigatório']);
    exit;
}

try {
    $conn = Conexao::getConexao();
    
    // 1. OTIMIZAÇÃO: Pega ID direto da sessão
    $userId = $_SESSION['user']['id'];

    if (isset($data['id']) && !empty($data['id'])) {
        // --- MODO EDIÇÃO (UPDATE) ---
        // Importante: Mantive o AND usuario_id = :uid para segurança (ninguém edita treino de outro)
        $stmt = $conn->prepare("UPDATE planos_treino SET nome_treino = :nome, descricao = :desc WHERE id = :id AND usuario_id = :uid");
        $stmt->bindValue(':id', $data['id']);
    } else {
        // --- MODO CRIAÇÃO (INSERT) ---
        $stmt = $conn->prepare("INSERT INTO planos_treino (usuario_id, nome_treino, descricao) VALUES (:uid, :nome, :desc)");
    }

    $stmt->bindValue(':uid', $userId);
    $stmt->bindValue(':nome', $data['name']);
    $stmt->bindValue(':desc', $data['exercises'] ?? ''); // O '??' evita erro se exercises vier vazio

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}
?>