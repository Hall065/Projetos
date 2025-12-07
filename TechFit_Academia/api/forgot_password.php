<?php
header('Content-Type: application/json');

// 1. Inclui o Controller (ajuste o caminho se necessário)
require_once __DIR__ . '/../Controller/AuthController.php';

// 2. Recebe o JSON do SweetAlert
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['email'])) {
    echo json_encode(['success' => false, 'error' => 'E-mail obrigatório.']);
    exit;
}

// 3. Instancia o Controller e chama a função
// NOTA: Como seu AuthController provavelmente cria o User no construtor, isso vai funcionar.
$auth = new AuthController();
$resultado = $auth->forgotPassword($data['email']);

// 4. Devolve a resposta do Controller para o JavaScript
echo json_encode($resultado);
?>