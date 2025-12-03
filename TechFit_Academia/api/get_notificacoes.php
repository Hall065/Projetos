<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

// Segurança
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) { 
    echo json_encode(['data' => [], 'unread' => 0]); 
    exit; 
}

try {
    $conn = Conexao::getConexao();
    
    // 1. OTIMIZAÇÃO: Pega ID direto da sessão
    $uid = $_SESSION['user']['id'];

    // Busca notificações (Limit 5)
    $stmt = $conn->prepare("SELECT * FROM notificacoes WHERE usuario_id = :uid ORDER BY criado_em DESC LIMIT 5");
    $stmt->bindValue(':uid', $uid);
    $stmt->execute();
    
    $notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Conta não lidas
    $stmtCount = $conn->prepare("SELECT COUNT(*) as total FROM notificacoes WHERE usuario_id = :uid AND lida = 0");
    $stmtCount->bindValue(':uid', $uid);
    $stmtCount->execute();
    $unread = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'data' => $notificacoes,
        'unread' => $unread
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>