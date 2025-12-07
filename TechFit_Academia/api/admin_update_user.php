<?php
ob_start();
require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Config/conexao.php';

header('Content-Type: application/json');

// Verifica Admin
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

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

try {
    $conn = Conexao::getConexao();
    $sql = "UPDATE usuarios SET nome = :nome, email = :email, telefone = :telefone, plano = :plano, status = :status WHERE id = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':nome', $data['nome']);
    $stmt->bindValue(':email', $data['email']);
    $stmt->bindValue(':telefone', $data['telefone']);
    $stmt->bindValue(':plano', $data['plano']);
    $stmt->bindValue(':status', $data['status']);
    $stmt->bindValue(':id', $data['id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao atualizar banco']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
ob_end_flush();
?>