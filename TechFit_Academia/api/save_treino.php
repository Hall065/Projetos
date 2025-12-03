<?php
header('Content-Type: application/json');

// 1. Chama o gerente de sessão (ISSO CONSERTA O ERRO DE LOGIN)
require_once __DIR__ . '/../Config/Sessao.php';

// 2. Chama a conexão com o banco
require_once __DIR__ . '/../Database/Conexao.php';

// 3. Verifica se o usuário está logado
if (!isset($_SESSION['user'])) { 
    echo json_encode(['success'=>false, 'message'=>'Sessão expirada. Faça login novamente.']); 
    exit; 
}

// Pega os dados enviados pelo JavaScript (JSON)
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['name'])) {
    echo json_encode(['success' => false, 'message' => 'Nome do treino é obrigatório']);
    exit;
}

try {
    $conn = Conexao::getConexao();
    
    // Pega ID do usuário logado
    $stmtUser = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmtUser->bindValue(':email', $_SESSION['user']);
    $stmtUser->execute();
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (isset($data['id']) && !empty($data['id'])) {
        // --- MODO EDIÇÃO (UPDATE) ---
        $stmt = $conn->prepare("UPDATE planos_treino SET nome_treino = :nome, descricao = :desc WHERE id = :id AND usuario_id = :uid");
        $stmt->bindValue(':id', $data['id']);
    } else {
        // --- MODO CRIAÇÃO (INSERT) ---
        $stmt = $conn->prepare("INSERT INTO planos_treino (usuario_id, nome_treino, descricao) VALUES (:uid, :nome, :desc)");
    }

    $stmt->bindValue(':uid', $user['id']);
    $stmt->bindValue(':nome', $data['name']);
    $stmt->bindValue(':desc', $data['exercises']); 

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}
?>