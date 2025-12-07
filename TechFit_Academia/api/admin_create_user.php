<?php
ob_start();
require_once __DIR__ . '/../Config/Sessao.php';
require_once __DIR__ . '/../Config/conexao.php';

header('Content-Type: application/json');

// 1. Verificação de Admin
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

// 2. Recebe os dados do JS
$data = json_decode(file_get_contents("php://input"), true);

// Validação básica
if (empty($data['nome']) || empty($data['email']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'error' => 'Preencha nome, email e senha!']);
    exit;
}

try {
    $conn = Conexao::getConexao();

    // 3. Verifica se email já existe
    $check = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
    $check->bindValue(':email', $data['email']);
    $check->execute();
    if ($check->rowCount() > 0) {
        echo json_encode(['success' => false, 'error' => 'Este email já está cadastrado!']);
        exit;
    }

    // 4. CRIPTOGRAFIA (A mesma do cadastro original)
    $senhaHash = password_hash($data['password'], PASSWORD_DEFAULT);

    // 5. Insere no Banco
    $sql = "INSERT INTO usuarios (nome, email, telefone, senha, plano, nivel_acesso, status, criado_em) 
            VALUES (:nome, :email, :telefone, :senha, :plano, 'comum', :status, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':nome', $data['nome']);
    $stmt->bindValue(':email', $data['email']);
    $stmt->bindValue(':telefone', $data['telefone']);
    $stmt->bindValue(':senha', $senhaHash); // Senha segura
    $stmt->bindValue(':plano', $data['plano']);
    $stmt->bindValue(':status', $data['status']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao inserir no banco']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
ob_end_flush();
?>