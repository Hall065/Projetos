<?php
ob_start();
require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

header('Content-Type: application/json');

// Validação de Admin (Padrão)
$eh_admin = false;
if (isset($_SESSION['user'])) {
    $nivel = isset($_SESSION['nivel']) ? strtolower(trim($_SESSION['nivel'])) : 'comum';
    $email = is_array($_SESSION['user']) ? $_SESSION['user']['email'] : $_SESSION['user'];
    if ($nivel === 'admin' || strpos($email, '@techfit.adm.br') !== false) $eh_admin = true;
}

if (!$eh_admin) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado']);
    exit;
}

try {
    $conn = Conexao::getConexao();

    // 1. CÁLCULO DA RECEITA ("Nota Fiscal")
    // Agrupa por plano para sabermos quantos de cada existem
    $sqlStats = "SELECT plano, COUNT(*) as qtd FROM usuarios 
                 WHERE email NOT LIKE '%@techfit.adm.br%' 
                 AND nivel_acesso != 'admin'
                 GROUP BY plano";
    $stmtStats = $conn->query($sqlStats);
    
    $resumo = [
        'standard' => ['qtd' => 0, 'valor' => 89, 'total' => 0],
        'premium' => ['qtd' => 0, 'valor' => 129, 'total' => 0],
        'vip' => ['qtd' => 0, 'valor' => 299, 'total' => 0],
        'total_geral' => 0
    ];

    while($row = $stmtStats->fetch(PDO::FETCH_ASSOC)) {
        $plano = strtolower($row['plano'] ?? '');
        $qtd = (int)$row['qtd'];

        if (strpos($plano, 'vip') !== false) {
            $resumo['vip']['qtd'] += $qtd;
            $resumo['vip']['total'] += $qtd * 299;
        } elseif (strpos($plano, 'premium') !== false) {
            $resumo['premium']['qtd'] += $qtd;
            $resumo['premium']['total'] += $qtd * 129;
        } else {
            $resumo['standard']['qtd'] += $qtd;
            $resumo['standard']['total'] += $qtd * 89;
        }
    }
    
    $resumo['total_geral'] = $resumo['standard']['total'] + $resumo['premium']['total'] + $resumo['vip']['total'];

    // 2. LISTA DE ALUNOS PARA COBRANÇA
    // Trazemos status para saber quem cobrar
    $sqlLista = "SELECT id, nome, email, plano, status 
                 FROM usuarios 
                 WHERE email NOT LIKE '%@techfit.adm.br%' 
                 AND nivel_acesso != 'admin' 
                 ORDER BY status ASC, nome ASC"; // Pendentes aparecem primeiro se status for alfabetico, mas ajustaremos no JS
    
    $stmtLista = $conn->query($sqlLista);
    $lista = $stmtLista->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'resumo' => $resumo, 'lista' => $lista]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
ob_end_flush();
?>