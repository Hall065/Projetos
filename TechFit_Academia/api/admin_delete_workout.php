<?php
ob_start(); // Previne erros de espaço em branco
require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

header('Content-Type: application/json');

// 1. Verifica se é Admin (Segurança Básica)
$eh_admin = false;
if (isset($_SESSION['user'])) {
    $nivel = isset($_SESSION['nivel']) ? strtolower(trim($_SESSION['nivel'])) : 'comum';
    $email = is_array($_SESSION['user']) ? $_SESSION['user']['email'] : $_SESSION['user'];
    if ($nivel === 'admin' || strpos($email, '@techfit.adm.br') !== false) $eh_admin = true;
}

if (!$eh_admin) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado']);
    exit;
}

// 2. Recebe o ID do JSON
$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

try {
    $conn = Conexao::getConexao();
    
    // 3. Deleta o treino
    $stmt = $conn->prepare("DELETE FROM planos_treino WHERE id = :id");
    $stmt->bindValue(':id', $data['id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao excluir do banco']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
ob_end_flush();
?>