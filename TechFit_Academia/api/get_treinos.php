<?php
header('Content-Type: application/json');

// 1. Chama o gerente de sessão
require_once __DIR__ . '/../Config/Sessao.php';

// 2. Chama a conexão
require_once __DIR__ . '/../Database/Conexao.php';

// Se não tiver sessão, retorna lista vazia [] para não quebrar o JS
if (!isset($_SESSION['user'])) { 
    echo json_encode([]); 
    exit; 
}

try {
    $conn = Conexao::getConexao();
    
    // Pega ID do usuário logado
    $stmtUser = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmtUser->bindValue(':email', $_SESSION['user']);
    $stmtUser->execute();
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // Busca os treinos ordenados pelos mais novos
    $stmt = $conn->prepare("SELECT * FROM planos_treino WHERE usuario_id = :uid ORDER BY criado_em DESC");
    $stmt->bindValue(':uid', $user['id']);
    $stmt->execute();
    
    // Retorna a lista em JSON
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>