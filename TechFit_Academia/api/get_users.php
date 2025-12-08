<?php
// Arquivo: api/get_users.php (Lista de Alunos para o Admin)
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
        
        $email = '';
        if (is_array($_SESSION['user'])) {
            $email = $_SESSION['user']['email'] ?? '';
        } else {
            $email = $_SESSION['user'];
        }
        
        if ($nivel === 'admin' || strpos($email, '@techfit.adm.br') !== false) {
            $eh_admin = true;
        }
    }

    if (!$eh_admin) {
        throw new Exception("Acesso restrito.");
    }

    // 2. BUSCAR ALUNOS
    $conn = Conexao::getConexao();
    
    // Adicionei 'access_token' e 'status' (caso precise reativar)
    $sql = "SELECT id, nome, email, telefone, plano, access_token, status, criado_em 
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