<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

if (!isset($_SESSION['user'])) { echo json_encode([]); exit; }

try {
    $conn = Conexao::getConexao();
    
    // Pega ID
    $stmtUser = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmtUser->bindValue(':email', $_SESSION['user']);
    $stmtUser->execute();
    $uid = $stmtUser->fetch(PDO::FETCH_ASSOC)['id'];

    // Busca notificações não lidas (ou as 5 mais recentes)
    $stmt = $conn->prepare("SELECT * FROM notificacoes WHERE usuario_id = :uid ORDER BY criado_em DESC LIMIT 5");
    $stmt->bindValue(':uid', $uid);
    $stmt->execute();
    
    $notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Conta quantas não foram lidas para a bolinha vermelha
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