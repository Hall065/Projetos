<?php
header('Content-Type: application/json');

// Configuração de Sessão (Mantendo seu padrão)
$session_dir = __DIR__ . '/../sessions_data';
if (is_dir($session_dir)) {
    session_save_path($session_dir);
}
session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

require_once __DIR__ . '/../Database/Conexao.php';

// Recebe o JSON enviado pelo JavaScript
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name']) || !isset($data['phone'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

try {
    $conn = Conexao::getConexao();

    // 1. CORREÇÃO DE SEGURANÇA BÁSICA (Opcional, mas recomendado)
    $newName = trim($data['name']);
    $newPhone = trim($data['phone']);

    // Atualiza nome e telefone
    $stmt = $conn->prepare("UPDATE usuarios SET nome = :nome, telefone = :phone WHERE email = :email");
    
    $stmt->bindValue(':nome', $newName);
    $stmt->bindValue(':phone', $newPhone);
    
    // 2. CORREÇÃO CRÍTICA AQUI:
    // Antes estava $_SESSION['user'], mas 'user' é um array. Precisamos do campo 'email' dentro dele.
    $stmt->bindValue(':email', $_SESSION['user']['email']); 
    
    if ($stmt->execute()) {
        // 3. ATUALIZA A SESSÃO IMEDIATAMENTE
        // Isso garante que se o usuário der F5, os dados novos já estarão lá
        $_SESSION['user']['nome'] = $newName;
        $_SESSION['user']['telefone'] = $newPhone;

        echo json_encode([
            'success' => true, 
            'message' => 'Perfil atualizado com sucesso!',
            // Opcional: devolver os dados novos para o JS atualizar a tela sem reload se precisar
            'newName' => $newName 
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar banco de dados']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
?>