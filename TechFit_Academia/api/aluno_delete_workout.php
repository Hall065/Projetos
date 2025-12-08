<?php
// Arquivo: api/aluno_delete_workout.php
header('Content-Type: application/json');
require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

// 1. Verifica se está logado (Qualquer usuário)
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'error' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID do treino não fornecido.']);
    exit;
}

try {
    $conn = Conexao::getConexao();
    $userId = $_SESSION['user']['id']; // Pega o ID do aluno logado

    // 2. A MÁGICA DE SEGURANÇA:
    // Deleta SOMENTE se o id do treino bater E se o usuario_id for o dono da sessão
    // Assim um aluno não consegue apagar o treino de outro.
    
    // Obs: Verifiquei que sua tabela é 'planos_treino' no seu código anterior
    $stmt = $conn->prepare("DELETE FROM planos_treino WHERE id = :id AND usuario_id = :uid");
    
    $stmt->bindValue(':id', $data['id']);
    $stmt->bindValue(':uid', $userId);
    
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            // Se não apagou nada, é porque o treino não existe OU não pertence a esse aluno
            echo json_encode(['success' => false, 'error' => 'Erro: Este treino não pertence a você ou já foi apagado.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro no banco de dados.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
}
?>