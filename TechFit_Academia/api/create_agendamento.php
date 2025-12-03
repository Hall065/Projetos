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

// Validação básica
if (empty($data['date']) || empty($data['time']) || empty($data['type'])) {
    echo json_encode(['success' => false, 'message' => 'Preencha todos os campos!']);
    exit;
}

try {
    $conn = Conexao::getConexao();

    // 1. Pega ID do usuário
    $stmtUser = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmtUser->bindValue(':email', $_SESSION['user']);
    $stmtUser->execute();
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // 2. Cria o agendamento
    // Calculamos hora fim como +1 hora da hora inicio (padrão)
    $horaInicio = $data['time']; // ex: 18:00
    $horaFim = date('H:i', strtotime($horaInicio) + 3600); // +1 hora

    $stmt = $conn->prepare("INSERT INTO agendamentos (usuario_id, data_treino, hora_inicio, hora_fim, tipo_treino, status) VALUES (:uid, :data, :inicio, :fim, :tipo, 'agendado')");
    
    $stmt->bindValue(':uid', $user['id']);
    $stmt->bindValue(':data', $data['date']);
    $stmt->bindValue(':inicio', $horaInicio);
    $stmt->bindValue(':fim', $horaFim);
    $stmt->bindValue(':tipo', $data['type']);
    
    if ($stmt->execute()) {
        // --- CRIA NOTIFICAÇÃO ---
        $msg = "Você agendou um treino de " . $data['type'] . " para " . date('d/m', strtotime($data['date'])) . " às " . $data['time'];
        $stmtNotif = $conn->prepare("INSERT INTO notificacoes (usuario_id, mensagem, tipo) VALUES (:uid, :msg, 'info')");
        $stmtNotif->bindValue(':uid', $user['id']);
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