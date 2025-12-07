<?php
require_once __DIR__ . '/../Config/conexao.php';
require_once __DIR__ . '/../Config/Sessao.php'; // Adicione validação de admin aqui se quiser blindar

header('Content-Type: application/json');

if (!isset($_GET['termo'])) exit(json_encode([]));

$conn = Conexao::getConexao();
$termo = "%" . $_GET['termo'] . "%";

$sql = "SELECT id, nome, email FROM usuarios 
        WHERE (nome LIKE :termo OR email LIKE :termo) 
        AND nivel_acesso != 'admin' 
        LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':termo', $termo);
$stmt->execute();

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>