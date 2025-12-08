<?php
header('Content-Type: application/json');

// Ajuste os caminhos conforme sua estrutura
require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

// Verificação de segurança
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) { 
    echo json_encode(['error' => 'Usuário não logado']); 
    exit; 
}

try {
    $conn = Conexao::getConexao();
    $uid = $_SESSION['user']['id'];

    // 1. TREINOS ESTE MÊS (Inclui futuros para planejamento visual, ou apenas passados se preferir)
    // Aqui mantivemos geral para ele ver o volume do mês
    $stmtMonth = $conn->prepare("SELECT COUNT(*) as total FROM agendamentos 
        WHERE usuario_id = :uid 
        AND status != 'cancelado' 
        AND MONTH(data_treino) = MONTH(CURRENT_DATE()) 
        AND YEAR(data_treino) = YEAR(CURRENT_DATE())");
    $stmtMonth->bindValue(':uid', $uid);
    $stmtMonth->execute();
    $monthCount = $stmtMonth->fetch(PDO::FETCH_ASSOC)['total'];

    // 2. PRÓXIMO TREINO
    $stmtNext = $conn->prepare("SELECT data_treino, hora_inicio, tipo_treino FROM agendamentos 
        WHERE usuario_id = :uid 
        AND status != 'cancelado'
        AND CONCAT(data_treino, ' ', hora_inicio) >= NOW() 
        ORDER BY data_treino ASC, hora_inicio ASC 
        LIMIT 1");
    $stmtNext->bindValue(':uid', $uid);
    $stmtNext->execute();
    $nextWorkout = $stmtNext->fetch(PDO::FETCH_ASSOC);

    // 3. TOTAL GERAL E CALORIAS (CORREÇÃO: APENAS TREINOS REALIZADOS/PASSADOS)
    $stmtTotal = $conn->prepare("SELECT COUNT(*) as total FROM agendamentos 
        WHERE usuario_id = :uid 
        AND status != 'cancelado'
        AND data_treino <= CURRENT_DATE()"); // <--- Só conta o que já aconteceu
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

        // Verifica se treinou hoje ou ontem para manter a chama acesa
        if ($latestDate->format('Y-m-d') == $today->format('Y-m-d') || $latestDate->format('Y-m-d') == $yesterday->format('Y-m-d')) {
            $streak = 1;
            $prevDate = $latestDate;
            
            // Loop para contar dias consecutivos anteriores
            for ($i = 1; $i < count($dates); $i++) {
                $currDate = new DateTime($dates[$i]);
                $interval = $prevDate->diff($currDate); // Diferença entre as datas
                
                if ($interval->days == 1) {
                    $streak++;
                    $prevDate = $currDate;
                } else {
                    break; // Quebrou a sequência
                }
            }
        }
    }

    // 5. FORMATAÇÃO DO TEXTO "PRÓXIMO TREINO"
    $nextText = "Sem agendamentos";
    $nextType = "---";
    
    if ($nextWorkout) {
        $dataTreino = new DateTime($nextWorkout['data_treino']);
        $hoje = new DateTime();
        $amanha = (new DateTime())->modify('+1 day');

        $dataStr = $dataTreino->format('Y-m-d');
        $hojeStr = $hoje->format('Y-m-d');
        $amanhaStr = $amanha->format('Y-m-d');
        
        if ($dataStr === $hojeStr) {
            $prefix = "Hoje";
        } elseif ($dataStr === $amanhaStr) {
            $prefix = "Amanhã";
        } else {
            $prefix = $dataTreino->format('d/m');
        }
        
        $hora = substr($nextWorkout['hora_inicio'], 0, 5);
        $nextText = "$prefix às $hora";
        $nextType = $nextWorkout['tipo_treino'];
    }

    echo json_encode([
        'monthlyWorkouts' => $monthCount,
        'nextWorkout' => $nextText,
        'nextWorkoutType' => $nextType,
        'calories' => number_format($calories, 0, ',', '.'),
        'totalWorkouts' => $totalCount, // Usado na aba Perfil
        'streak' => $streak
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>