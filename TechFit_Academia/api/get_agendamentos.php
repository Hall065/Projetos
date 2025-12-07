<?php
// 1. IMPORTA A CONFIGURAÇÃO CENTRALIZADA DE SESSÃO
require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

header('Content-Type: application/json');

// Verifica se o usuário e o ID existem na sessão
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

try {
    $conn = Conexao::getConexao();

    // 2. OTIMIZAÇÃO: Usamos o ID direto da sessão (não precisa buscar no banco de novo)
    $userId = $_SESSION['user']['id'];

    // 3. Busca os agendamentos usando o ID da sessão
    $sql = "SELECT id, data_treino, hora_inicio, hora_fim, tipo_treino, status 
            FROM agendamentos 
            WHERE usuario_id = :uid 
            ORDER BY data_treino DESC, hora_inicio DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':uid', $userId);
    $stmt->execute();
    
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($agendamentos);

} catch (Exception $e) {
    echo json_encode(['error' => 'Erro no servidor: ' . $e->getMessage()]);
}
?>