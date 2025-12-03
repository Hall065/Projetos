<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

// Segurança: Verifica se o ID existe na sessão
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validação básica
if (empty($data['date']) || empty($data['time']) || empty($data['type'])) {
    echo json_encode(['success' => false, 'message' => 'Preencha todos os campos!']);
    exit;
}

try {
    $conn = Conexao::getConexao();

    // 1. OTIMIZAÇÃO: Pega ID direto da sessão
    $userId = $_SESSION['user']['id'];

    // 2. Cria o agendamento
    // Calculamos hora fim como +1 hora da hora inicio (padrão)
    $horaInicio = $data['time']; // ex: 18:00
    // Garante formato H:i
    $horaFim = date('H:i', strtotime($horaInicio) + 3600); 

    $stmt = $conn->prepare("INSERT INTO agendamentos (usuario_id, data_treino, hora_inicio, hora_fim, tipo_treino, status) VALUES (:uid, :data, :inicio, :fim, :tipo, 'agendado')");
    
    $stmt->bindValue(':uid', $userId);
    $stmt->bindValue(':data', $data['date']);
    $stmt->bindValue(':inicio', $horaInicio);
    $stmt->bindValue(':fim', $horaFim);
    $stmt->bindValue(':tipo', $data['type']);
    
    if ($stmt->execute()) {
        // --- CRIA NOTIFICAÇÃO ---
        $msg = "Você agendou um treino de " . $data['type'] . " para " . date('d/m', strtotime($data['date'])) . " às " . $data['time'];
        
        $stmtNotif = $conn->prepare("INSERT INTO notificacoes (usuario_id, mensagem, tipo) VALUES (:uid, :msg, 'info')");
        $stmtNotif->bindValue(':uid', $userId);
        $stmtNotif->bindValue(':msg', $msg);
        $stmtNotif->execute();
        // ------------------------

        echo json_encode(['success' => true, 'message' => 'Treino agendado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao agendar treino.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
?>