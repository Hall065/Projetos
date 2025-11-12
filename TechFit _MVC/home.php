<?php
// Controlador do Dashboard do Usuário
require 'models/Database.php';
require 'models/Stats.php';

// --- CONTROLE DE ACESSO ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// -------------------------

// --- LÓGICA DO CONTROLADOR ---
$pdo = Database::connect();
$statsModel = new Stats($pdo);

// Busca dados no Model
$user_id = $_SESSION['user_id'];
$dashboardData = $statsModel->getUserDashboardStats($user_id);

// Prepara dados para a View
$user_nome = $_SESSION['user_nome'];
$user_plano = $_SESSION['user_plano'];
$treinos_mes = $dashboardData['treinos_mes'];
$proximo_treino = $dashboardData['proximo_treino'];
$atividades = $dashboardData['atividades'];
// -----------------------------

// --- CARREGA A VIEW ---
// A view terá acesso a todas as variáveis definidas acima
require 'views/home_view.php';
?>
