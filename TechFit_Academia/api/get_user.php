<?php
// Define que a resposta será um JSON
header('Content-Type: application/json');

// --- CORREÇÃO IMPORTANTE: Configuração da Sessão Local ---
// Precisamos apontar para a mesma pasta que o AuthController usa
$session_dir = __DIR__ . '/../sessions_data';
if (is_dir($session_dir)) {
    session_save_path($session_dir);
}
// ---------------------------------------------------------

session_start();

// Verifica se está logado
if (!isset($_SESSION['user'])) {
    // Se não achar a sessão, retorna erro para o JS saber
    echo json_encode(['error' => 'Usuário não autenticado ou sessão não encontrada']);
    exit;
}

require_once __DIR__ . '/../Config/conexao.php';

try {
    $conn = Conexao::getConexao();
    
    // Busca os dados do usuário logado
    $stmt = $conn->prepare("SELECT id, nome, email, telefone, plano, criado_em FROM usuarios WHERE email = :email");
    $stmt->bindValue(':email', $_SESSION['user']);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Formata data
        $date = new DateTime($user['criado_em']);
        $meses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
        $memberSince = $meses[(int)$date->format('m')] . ' ' . $date->format('Y');

        // Dados para o Frontend
        $userData = [
            'id' => $user['id'],
            'name' => $user['nome'],
            'email' => $user['email'],
            'phone' => $user['telefone'],
            'plan' => $user['plano'] ?? 'Standard', // Garante que tenha um valor padrão
            'memberSince' => $memberSince
        ];

        echo json_encode($userData);
    } else {
        echo json_encode(['error' => 'Usuário não encontrado no Banco de Dados']);
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Erro no servidor: ' . $e->getMessage()]);
}
?>