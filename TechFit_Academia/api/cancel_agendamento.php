<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

// Verificação de segurança mais robusta
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
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

    // 1. OTIMIZAÇÃO: Pega o ID direto da sessão
    $userId = $_SESSION['user']['id'];

    // 2. Deleta o agendamento (Garante que só deleta se for do usuário logado)
    $stmt = $conn->prepare("DELETE FROM agendamentos WHERE id = :id AND usuario_id = :uid");
    $stmt->bindValue(':id', $data['id']);
    $stmt->bindValue(':uid', $userId);
    
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            // --- CRIA NOTIFICAÇÃO ---
            $msg = "Agendamento cancelado com sucesso.";
            $stmtNotif = $conn->prepare("INSERT INTO notificacoes (usuario_id, mensagem, tipo) VALUES (:uid, :msg, 'warning')");
            $stmtNotif->bindValue(':uid', $userId);
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