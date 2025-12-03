<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

if (!isset($_SESSION['user'])) { echo json_encode([]); exit; }

// Pega a data da URL (ex: get_horarios.php?date=2023-12-05)
// Se não vier data, usa HOJE.
$filtroData = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

try {
    $conn = Conexao::getConexao();
    
    // 1. Pega ID do usuário
    $stmtUser = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmtUser->bindValue(':email', $_SESSION['user']);
    $stmtUser->execute();
    $uid = $stmtUser->fetch(PDO::FETCH_ASSOC)['id'];

    // 2. Busca agendamentos DO DIA ESPECÍFICO
    $stmt = $conn->prepare("SELECT hora_inicio, tipo_treino FROM agendamentos 
                            WHERE usuario_id = :uid 
                            AND data_treino = :data 
                            AND status != 'cancelado'");
    $stmt->bindValue(':uid', $uid);
    $stmt->bindValue(':data', $filtroData);
    $stmt->execute();
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Transforma em um array fácil de buscar (ex: ['18:00:00' => 'Treino A'])
    $ocupados = [];
    foreach($agendamentos as $ag) {
        // Pega só a hora cheia (06:00)
        $hora = substr($ag['hora_inicio'], 0, 5); 
        $ocupados[$hora] = $ag['tipo_treino'];
    }

    // 3. Gera a lista de horários fixos da academia (06:00 as 21:00)
    $listaHorarios = [];
    for ($i = 6; $i <= 21; $i++) {
        $horaFormatada = sprintf("%02d:00", $i); // 06:00, 07:00...
        $horaFim = sprintf("%02d:00", $i + 1);
        
        $item = [
            'id' => $i,
            'time' => $horaFormatada,
            'label' => "$horaFormatada - $horaFim",
            'available' => true,
            'treino' => null
        ];

        // Verifica se está ocupado
        if (isset($ocupados[$horaFormatada])) {
            $item['available'] = false;
            $item['treino'] = $ocupados[$horaFormatada];
        }

        $listaHorarios[] = $item;
    }
    
    echo json_encode($listaHorarios);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>