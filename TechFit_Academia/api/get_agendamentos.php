<?php
header('Content-Type: application/json');

// 1. Configuração da Sessão (Igual ao Login)
$session_dir = __DIR__ . '/../sessions_data';
if (is_dir($session_dir)) {
    session_save_path($session_dir);
}
session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

require_once __DIR__ . '/../Database/Conexao.php';

try {
    $conn = Conexao::getConexao();

    // 2. Busca o ID do usuário pelo email da sessão
    $stmtUser = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmtUser->bindValue(':email', $_SESSION['user']);
    $stmtUser->execute();
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['error' => 'Usuário não encontrado']);
        exit;
    }

    // 3. Busca os agendamentos desse ID
    // Ordenado pela data mais recente primeiro
    $sql = "SELECT id, data_treino, hora_inicio, hora_fim, tipo_treino, status 
            FROM agendamentos 
            WHERE usuario_id = :uid 
            ORDER BY data_treino DESC, hora_inicio DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':uid', $user['id']);
    $stmt->execute();
    
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Retorna a lista (mesmo que vazia)
    echo json_encode($agendamentos);

} catch (Exception $e) {
    echo json_encode(['error' => 'Erro no servidor: ' . $e->getMessage()]);
}
?>