<?php
ob_start();
require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

header('Content-Type: application/json');

// Verifica se é Admin
$eh_admin = false;
if (isset($_SESSION['user'])) {
    $nivel = isset($_SESSION['nivel']) ? strtolower(trim($_SESSION['nivel'])) : 'comum';
    $email = is_array($_SESSION['user']) ? $_SESSION['user']['email'] : $_SESSION['user'];
    if ($nivel === 'admin' || strpos($email, '@techfit.adm.br') !== false) $eh_admin = true;
}

if (!$eh_admin || !isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado ou ID faltando']);
    exit;
}

try {
    $conn = Conexao::getConexao();
    $stmt = $conn->prepare("SELECT id, nome, email, telefone, plano, status FROM usuarios WHERE id = :id");
    $stmt->bindValue(':id', $_GET['id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode(['success' => true, 'data' => $user]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Usuário não encontrado']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
ob_end_flush();
?>