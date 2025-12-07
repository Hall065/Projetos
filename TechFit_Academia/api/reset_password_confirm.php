<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../Database/Conexao.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['token']) || empty($data['senha'])) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos.']);
    exit;
}

try {
    $conn = Conexao::getConexao();
    
    // 1. Verifica se o token existe e se NÃO expirou (reset_expires > NOW())
    $sql = "SELECT id FROM usuarios WHERE reset_token = :token AND reset_expires > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':token', $data['token']);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'error' => 'Link inválido ou expirado.']);
        exit;
    }

    // 2. Atualiza a senha e limpa o token (para não ser usado de novo)
    $novaSenhaHash = password_hash($data['senha'], PASSWORD_DEFAULT);
    
    $update = $conn->prepare("UPDATE usuarios SET senha = :senha, reset_token = NULL, reset_expires = NULL WHERE reset_token = :token");
    $update->bindValue(':senha', $novaSenhaHash);
    $update->bindValue(':token', $data['token']);
    
    if ($update->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao salvar nova senha.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
}
?>