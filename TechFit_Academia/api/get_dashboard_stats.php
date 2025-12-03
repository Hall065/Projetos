<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

if (!isset($_SESSION['user'])) { echo json_encode([]); exit; }

try {
    $conn = Conexao::getConexao();
    
    // Pega ID
    $stmtUser = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmtUser->bindValue(':email', $_SESSION['user']);
    $stmtUser->execute();
    $uid = $stmtUser->fetch(PDO::FETCH_ASSOC)['id'];

    // 1. Treinos este mês
    $stmtMonth = $conn->prepare("SELECT COUNT(*) as total FROM agendamentos 
        WHERE usuario_id = :uid 
        AND status != 'cancelado' 
        AND MONTH(data_treino) = MONTH(CURRENT_DATE()) 
        AND YEAR(data_treino) = YEAR(CURRENT_DATE())");
    $stmtMonth->bindValue(':uid', $uid);
    $stmtMonth->execute();
    $monthCount = $stmtMonth->fetch(PDO::FETCH_ASSOC)['total'];

    // 2. Próximo Treino
    $stmtNext = $conn->prepare("SELECT data_treino, hora_inicio, tipo_treino FROM agendamentos 
        WHERE usuario_id = :uid 
        AND status != 'cancelado'
        AND data_treino >= CURRENT_DATE() 
        ORDER BY data_treino ASC, hora_inicio ASC 
        LIMIT 1");
    $stmtNext->bindValue(':uid', $uid);
    $stmtNext->execute();
    $nextWorkout = $stmtNext->fetch(PDO::FETCH_ASSOC);

    // 3. Total Geral e Calorias (Mantido como você pediu)
    $stmtTotal = $conn->prepare("SELECT COUNT(*) as total FROM agendamentos WHERE usuario_id = :uid AND status != 'cancelado'");
    $stmtTotal->bindValue(':uid', $uid);
    $stmtTotal->execute();
    $totalCount = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Estimativa: 350 calorias por treino realizado
    $calories = $totalCount * 350; 

    // 4. CÁLCULO DA SEQUÊNCIA (STREAK)
    $stmtStreak = $conn->prepare("SELECT DISTINCT data_treino FROM agendamentos 
        WHERE usuario_id = :uid 
        AND status != 'cancelado' 
        AND data_treino <= CURRENT_DATE() 
        ORDER BY data_treino DESC");
    $stmtStreak->bindValue(':uid', $uid);
    $stmtStreak->execute();
    $dates = $stmtStreak->fetchAll(PDO::FETCH_COLUMN);

    $streak = 0;
    if (!empty($dates)) {
        $today = new DateTime();
        $yesterday = (new DateTime())->modify('-1 day');
        $latestDate = new DateTime($dates[0]);

        if ($latestDate->format('Y-m-d') == $today->format('Y-m-d') || $latestDate->format('Y-m-d') == $yesterday->format('Y-m-d')) {
            $streak = 1;
            $prevDate = $latestDate;
            for ($i = 1; $i < count($dates); $i++) {
                $currDate = new DateTime($dates[$i]);
                $interval = $prevDate->diff($currDate);
                if ($interval->days == 1) {
                    $streak++;
                    $prevDate = $currDate;
                } else { break; }
            }
        }
    }

    // Formatação Próximo Treino
    $nextText = "Sem agendamentos";
    $nextType = "---";
    if ($nextWorkout) {
        $data = new DateTime($nextWorkout['data_treino']);
        $hoje = new DateTime();
        $diff = $hoje->diff($data)->days;
        
        if ($data->format('Y-m-d') == $hoje->format('Y-m-d')) { $prefix = "Hoje"; }
        elseif ($diff == 1 && $hoje < $data) { $prefix = "Amanhã"; }
        else { $prefix = $data->format('d/m'); }
        
        $hora = substr($nextWorkout['hora_inicio'], 0, 5);
        $nextText = "$prefix às $hora";
        $nextType = $nextWorkout['tipo_treino'];
    }

    echo json_encode([
        'monthlyWorkouts' => $monthCount,
        'nextWorkout' => $nextText,
        'nextWorkoutType' => $nextType,
        'calories' => number_format($calories, 0, ',', '.'), // MANTIDO
        'totalWorkouts' => $totalCount,
        'streak' => $streak
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>