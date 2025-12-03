<?php
header('Content-Type: application/json');

// 1. Chama o gerente de sessão
require_once __DIR__ . '/../Config/Sessao.php';

// 2. Chama a conexão
require_once __DIR__ . '/../Database/Conexao.php';

// Segurança
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do agendamento não fornecido']);
    exit;
}

try {
    $conn = Conexao::getConexao();

    // 1. Pega o ID do usuário logado (Segurança: só deleta se o treino for dele)
    $stmtUser = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmtUser->bindValue(':email', $_SESSION['user']);
    $stmtUser->execute();
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // 2. Deleta o agendamento (ou marca como cancelado)
    $stmt = $conn->prepare("DELETE FROM agendamentos WHERE id = :id AND usuario_id = :uid");
    $stmt->bindValue(':id', $data['id']);
    $stmt->bindValue(':uid', $user['id']);
    
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            // --- CRIA NOTIFICAÇÃO ---
            $msg = "Agendamento cancelado com sucesso.";
            $stmtNotif = $conn->prepare("INSERT INTO notificacoes (usuario_id, mensagem, tipo) VALUES (:uid, :msg, 'warning')");
            $stmtNotif->bindValue(':uid', $user['id']);
            $stmtNotif->bindValue(':msg', $msg);
            $stmtNotif->execute();
            // ------------------------

            echo json_encode(['success' => true, 'message' => 'Agendamento cancelado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Agendamento não encontrado ou não pertence a você.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao deletar.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
?>