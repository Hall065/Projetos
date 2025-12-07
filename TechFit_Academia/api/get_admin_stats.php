<?php
// 1. Buffer para evitar erros de cabeçalho
ob_start();

// 2. INCLUI A CONFIGURAÇÃO DE SESSÃO DO SISTEMA
// Isso garante que a API leia a MESMA sessão do Login
require_once __DIR__ . '/../Config/Sessao.php'; 
require_once __DIR__ . '/../Database/Conexao.php';

header('Content-Type: application/json');

$response = [];

try {
    // === INICIO DA VERIFICAÇÃO DE SEGURANÇA ===
    $eh_admin = false;
    $debug_info = ''; 

    // Verifica se a sessão carregou corretamente
    if (isset($_SESSION['user'])) {
        
        // Normaliza o email (aceita string antiga ou array novo)
        $email = '';
        if (is_array($_SESSION['user'])) {
            $email = $_SESSION['user']['email'] ?? '';
        } else {
            $email = $_SESSION['user'];
        }

        // Normaliza o nível (minúsculo e sem espaços)
        $nivel = isset($_SESSION['nivel']) ? strtolower(trim($_SESSION['nivel'])) : 'comum';
        
        // A Regra de Ouro: Libera se for 'admin' OU se for o email mestre
        if ($nivel === 'admin' || strpos($email, '@techfit.adm.br') !== false) {
            $eh_admin = true;
        } else {
            $debug_info = "Nivel detectado: '$nivel' | Email: '$email'";
        }
    } else {
        // Se cair aqui, a SESSÃO não foi carregada pelo Config/Sessao.php
        $debug_info = "Sessão de usuário vazia ou ID da sessão diferente.";
    }

    if (!$eh_admin) {
        throw new Exception("Acesso negado. ($debug_info)");
    }
    // === FIM DA VERIFICAÇÃO ===

    $conn = Conexao::getConexao();

    // --- CONSULTAS DO BANCO (Métricas) ---

    // 1. Total Alunos
    $sqlAlunos = "SELECT COUNT(*) as total FROM usuarios 
                  WHERE email NOT LIKE '%@techfit.adm.br%' 
                  AND nivel_acesso != 'admin'";
    $stmtAlunos = $conn->query($sqlAlunos);
    $totalAlunos = $stmtAlunos->fetch(PDO::FETCH_ASSOC)['total'];

    // 2. Treinos Hoje
    $sqlTreinos = "SELECT COUNT(*) as total FROM agendamentos 
                   WHERE data_treino = CURDATE() 
                   AND status != 'cancelado'";
    $stmtTreinos = $conn->query($sqlTreinos);
    $treinosHoje = $stmtTreinos->fetch(PDO::FETCH_ASSOC)['total'];

    // 3. Faturamento
    $sqlFaturamento = "SELECT plano, COUNT(*) as qtd FROM usuarios 
                       WHERE email NOT LIKE '%@techfit.adm.br%' 
                       GROUP BY plano";
    $stmtFat = $conn->query($sqlFaturamento);
    $faturamento = 0;
    
    while($row = $stmtFat->fetch(PDO::FETCH_ASSOC)) {
        $plano = isset($row['plano']) ? strtolower($row['plano']) : '';
        
        if(strpos($plano, 'premium') !== false) {
            $faturamento += 129 * $row['qtd'];
        } elseif(strpos($plano, 'vip') !== false) {
            $faturamento += 299 * $row['qtd'];
        } else {
            $faturamento += 89 * $row['qtd'];
        }
    }

    // 4. Lista Recentes
    $sqlRecentes = "SELECT nome, email, criado_em, plano FROM usuarios 
                    WHERE email NOT LIKE '%@techfit.adm.br%' 
                    ORDER BY id DESC LIMIT 5";
    $stmtRecentes = $conn->query($sqlRecentes);
    $recentes = $stmtRecentes->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'total_alunos' => $totalAlunos,
        'treinos_hoje' => $treinosHoje,
        'faturamento' => number_format($faturamento, 2, ',', '.'),
        'recentes' => $recentes
    ];

} catch (Exception $e) {
    http_response_code(403); 
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

ob_clean(); 
echo json_encode($response);
exit;
?>