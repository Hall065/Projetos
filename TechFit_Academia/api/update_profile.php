<?php
header('Content-Type: application/json');

// ... (Sessão e configs mantidas iguais) ...
$session_dir = __DIR__ . '/../sessions_data';
if (is_dir($session_dir)) {
    session_save_path($session_dir);
}
session_start();

if (!isset($_SESSION['user'])) {
    // MUDANÇA 1: De 'message' para 'error'
    echo json_encode(['success' => false, 'error' => 'Usuário não autenticado']);
    exit;
}

require_once __DIR__ . '/../Database/Conexao.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name']) || !isset($data['phone'])) {
    // MUDANÇA 2: De 'message' para 'error'
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit;
}

try {
    $conn = Conexao::getConexao();

    $newName = trim($data['name']);
    $newPhone = trim($data['phone']);

    $stmt = $conn->prepare("UPDATE usuarios SET nome = :nome, telefone = :phone WHERE email = :email");
    
    $stmt->bindValue(':nome', $newName);
    $stmt->bindValue(':phone', $newPhone);
    $stmt->bindValue(':email', $_SESSION['user']['email']); 
    
    if ($stmt->execute()) {
        // Atualiza Sessão
        $_SESSION['user']['nome'] = $newName;
        $_SESSION['user']['telefone'] = $newPhone;

        // Sucesso pode manter mensagem ou não, o JS foca no 'success: true'
        echo json_encode([
            'success' => true, 
            'message' => 'Perfil atualizado com sucesso!'
        ]);
    } else {
        // MUDANÇA 3: De 'message' para 'error'
        echo json_encode(['success' => false, 'error' => 'Erro ao atualizar banco de dados']);
    }

} catch (Exception $e) {
    // MUDANÇA 4: De 'message' para 'error'
    echo json_encode(['success' => false, 'error' => 'Erro: ' . $e->getMessage()]);
}
?>