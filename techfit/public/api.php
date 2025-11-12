<?php
// public/api.php

require_once '../controllers/DashboardController.php';

$controller = new DashboardController();

// Pegar a ação da URL
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Rotear para o método correto
switch ($action) {
    case 'getUserData':
        $data = $controller->getUserData();
        header('Content-Type: application/json');
        echo json_encode($data);
        break;
        
    case 'getDashboardStats':
        $stats = $controller->getDashboardStats();
        header('Content-Type: application/json');
        echo json_encode($stats);
        break;
        
    case 'getSchedules':
        $controller->getSchedules();
        break;
        
    case 'getAppointments':
        $controller->getAppointments();
        break;
        
    case 'getWorkoutPlans':
        $controller->getWorkoutPlans();
        break;
        
    case 'createAppointment':
        $controller->createAppointment();
        break;
        
    case 'confirmAppointment':
        $controller->confirmAppointment();
        break;
        
    case 'cancelAppointment':
        $controller->cancelAppointment();
        break;
        
    case 'updateProfile':
        $controller->updateProfile();
        break;
        
    default:
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Ação não encontrada']);
        break;
}
?>