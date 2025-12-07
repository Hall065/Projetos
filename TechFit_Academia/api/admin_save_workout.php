<?php
require_once __DIR__ . '/../Database/Conexao.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

try {
    $conn = Conexao::getConexao();

    if (empty($data['id'])) {
        // CRIAR NOVO
        $sql = "INSERT INTO planos_treino (usuario_id, nome_treino, descricao, criado_em) 
                VALUES (:uid, :nome, :desc, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':uid', $data['usuario_id']);
    } else {
        // EDITAR EXISTENTE
        $sql = "UPDATE planos_treino SET nome_treino = :nome, descricao = :desc WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $data['id']);
    }

    $stmt->bindValue(':nome', $data['nome_treino']);
    $stmt->bindValue(':desc', $data['descricao']);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao salvar treino']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>