<?php
// 1. Configuração Centralizada
require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

header('Content-Type: application/json');

// Se não tiver ID na sessão, retorna lista vazia
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) { 
    echo json_encode([]); 
    exit; 
}

try {
    $conn = Conexao::getConexao();
    
    // 2. OTIMIZAÇÃO: Pega o ID direto da sessão
    $userId = $_SESSION['user']['id'];

    // 3. Busca os treinos diretamente
    $stmt = $conn->prepare("SELECT * FROM planos_treino WHERE usuario_id = :uid ORDER BY criado_em DESC");
    $stmt->bindValue(':uid', $userId);
    $stmt->execute();
    
    // Retorna a lista em JSON
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>