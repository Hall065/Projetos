<?php
// Controlador do Dashboard Admin
require 'models/Database.php';
require 'models/Stats.php';

// --- CONTROLE DE ACESSO ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['user_tipo'] != 'admin') {
    header("Location: home.php"); // Não é admin, volta pro home
    exit();
}
// -------------------------

// --- LÓGICA DO CONTROLADOR ---
$pdo = Database::connect();
$statsModel = new Stats($pdo);

// Busca dados no Model
$adminStats = $statsModel->getAdminStats();

// Prepara dados para a View
$total_usuarios = $adminStats['total_usuarios'];
$agendamentos_ativos = $adminStats['agendamentos_ativos'];
$planos_premium = $adminStats['planos_premium'];
$ultimos_usuarios = $adminStats['ultimos_usuarios'];
// -----------------------------

// --- CARREGA A VIEW ---
require 'views/admin_view.php';
?>

