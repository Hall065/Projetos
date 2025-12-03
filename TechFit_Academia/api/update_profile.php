<?php
header('Content-Type: application/json');

// Configuração de Sessão
$session_dir = __DIR__ . '/../sessions_data';
if (is_dir($session_dir)) {
    session_save_path($session_dir);
}
session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

require_once __DIR__ . '/../Database/Conexao.php';

// Recebe o JSON enviado pelo JavaScript
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name']) || !isset($data['phone'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

try {
    $conn = Conexao::getConexao();

    // Atualiza nome e telefone baseado no email da sessão
    $stmt = $conn->prepare("UPDATE usuarios SET nome = :nome, telefone = :phone WHERE email = :email");
    $stmt->bindValue(':nome', $data['name']);
    $stmt->bindValue(':phone', $data['phone']);
    $stmt->bindValue(':email', $_SESSION['user']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Perfil atualizado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar banco de dados']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
?>