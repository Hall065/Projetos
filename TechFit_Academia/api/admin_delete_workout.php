<?php
// Arquivo: api/admin_delete_workout.php
ob_start();
require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

header('Content-Type: application/json');

// ==========================================================
// 1. VERIFICAÇÃO DE ADMIN (Crucial para segurança)
// ==========================================================
$eh_admin = false;
if (isset($_SESSION['user'])) {
    $nivel = isset($_SESSION['nivel']) ? strtolower(trim($_SESSION['nivel'])) : 'comum';
    
    $email = '';
    if (is_array($_SESSION['user'])) {
        $email = $_SESSION['user']['email'] ?? '';
    } else {
        $email = $_SESSION['user'];
    }

    // Verifica se é Admin
    if ($nivel === 'admin' || strpos($email, '@techfit.adm.br') !== false) {
        $eh_admin = true;
    }
}

if (!$eh_admin) {
    // Se não for admin, bloqueia
    echo json_encode(['success' => false, 'error' => 'Acesso negado: Apenas administradores.']);
    exit;
}
// ==========================================================

// 2. Recebe o ID do JSON
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID do treino não fornecido']);
    exit;
}

try {
    $conn = Conexao::getConexao();
    
    // 3. Deleta o treino pelo ID (Sem checar usuario_id, pois Admin pode tudo)
    // Note que mantive 'planos_treino' conforme seu código enviado
    $stmt = $conn->prepare("DELETE FROM planos_treino WHERE id = :id");
    $stmt->bindValue(':id', $data['id']);
    
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Treino não encontrado ou já excluído.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao excluir do banco de dados']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
}
ob_end_flush();
?>