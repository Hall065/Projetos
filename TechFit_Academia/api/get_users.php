<?php
// api/get_users.php
ob_start();
require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Database/Conexao.php';

header('Content-Type: application/json');
$response = [];

try {
    // 1. SEGURANÇA: Só Admin passa
    $eh_admin = false;
    if (isset($_SESSION['user'])) {
        $nivel = isset($_SESSION['nivel']) ? strtolower(trim($_SESSION['nivel'])) : 'comum';
        $email = is_array($_SESSION['user']) ? $_SESSION['user']['email'] : $_SESSION['user'];
        
        if ($nivel === 'admin' || strpos($email, '@techfit.adm.br') !== false) {
            $eh_admin = true;
        }
    }

    if (!$eh_admin) {
        throw new Exception("Acesso restrito.");
    }

    // 2. BUSCAR ALUNOS (Ignora admins e equipe)
    $conn = Conexao::getConexao();
    $sql = "SELECT id, nome, email, telefone, plano, criado_em, status 
            FROM usuarios 
            WHERE email NOT LIKE '%@techfit.adm.br%' 
            AND nivel_acesso != 'admin' 
            ORDER BY id DESC";
    
    $stmt = $conn->query($sql);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = ['success' => true, 'data' => $alunos];

} catch (Exception $e) {
    http_response_code(403);
    $response = ['success' => false, 'error' => $e->getMessage()];
}

ob_clean();
echo json_encode($response);
exit;
?>