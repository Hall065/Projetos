<?php
require_once __DIR__ . '/../Config/conexao.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID faltando']);
    exit;
}

try {
    $conn = Conexao::getConexao();
    
    // Busca info do aluno
    $stmtUser = $conn->prepare("SELECT nome, email FROM usuarios WHERE id = :id");
    $stmtUser->bindValue(':id', $_GET['id']);
    $stmtUser->execute();
    $aluno = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // Busca treinos
    $stmtTreinos = $conn->prepare("SELECT * FROM planos_treino WHERE usuario_id = :id ORDER BY id DESC");
    $stmtTreinos->bindValue(':id', $_GET['id']);
    $stmtTreinos->execute();
    $treinos = $stmtTreinos->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'aluno' => $aluno, 'treinos' => $treinos]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>