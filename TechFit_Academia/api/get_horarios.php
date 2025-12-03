<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

// Verificação mais segura
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) { 
    echo json_encode([]); 
    exit; 
}

// Pega a data da URL ou usa HOJE
$filtroData = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

try {
    $conn = Conexao::getConexao();
    
    // 1. OTIMIZAÇÃO: Pega ID direto da sessão (removemos a consulta desnecessária na tabela usuarios)
    $uid = $_SESSION['user']['id'];

    // 2. Busca agendamentos DO DIA ESPECÍFICO para este usuário
    $stmt = $conn->prepare("SELECT hora_inicio, tipo_treino FROM agendamentos 
                            WHERE usuario_id = :uid 
                            AND data_treino = :data 
                            AND status != 'cancelado'");
    $stmt->bindValue(':uid', $uid);
    $stmt->bindValue(':data', $filtroData);
    $stmt->execute();
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Transforma em um array fácil de buscar (ex: ['18:00' => 'Treino A'])
    $ocupados = [];
    foreach($agendamentos as $ag) {
        // Garante formato 00:00 (pega os 5 primeiros chars)
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

        // Se o usuário já tem treino nessa hora, marca como indisponível
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