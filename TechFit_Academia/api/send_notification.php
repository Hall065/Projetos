<?php
ob_start();
require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Config/conexao.php';

header('Content-Type: application/json');

// Validação de Admin
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

if (empty($data['usuario_id']) || empty($data['mensagem'])) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit;
}

try {
    $conn = Conexao::getConexao();

    $sql = "INSERT INTO notificacoes (usuario_id, mensagem, tipo, lida, criado_em) 
            VALUES (:uid, :msg, :tipo, 0, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':uid', $data['usuario_id']);
    $stmt->bindValue(':msg', $data['mensagem']);
    $stmt->bindValue(':tipo', $data['tipo'] ?? 'financeiro'); // Padrão 'financeiro'
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao criar notificação']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
ob_end_flush();
?>