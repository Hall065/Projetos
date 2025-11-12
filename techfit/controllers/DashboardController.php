<?php
// controllers/DashboardController.php

require_once '../config/Database.php';
require_once '../models/User.php';
require_once '../models/Schedule.php';
require_once '../models/Appointment.php';
require_once '../models/Workout.php';

class DashboardController {
    private $db;
    private $user_id;

    public function __construct() {
        session_start();
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Simular usuário logado (em produção virá da sessão)
        $this->user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
    }

    public function index() {
        $userData = $this->getUserData();
        $dashboardStats = $this->getDashboardStats();
        
        // Carregar a view
        include '../views/dashboard.php';
    }

    public function getUserData() {
        $userModel = new User($this->db);
        return $userModel->getUserById($this->user_id);
    }

    public function getDashboardStats() {
        $userModel = new User($this->db);
        $appointmentModel = new Appointment($this->db);
        
        $stats = $userModel->getUserStats($this->user_id);
        $nextAppointment = $appointmentModel->getNextAppointment($this->user_id);
        
        return [
            'monthly_workouts' => $stats['monthly_workouts'] ?? 0,
            'next_workout' => $nextAppointment ? $nextAppointment['datetime'] : 'Nenhum',
            'next_workout_type' => $nextAppointment ? $nextAppointment['title'] : '',
            'calories' => 2450, // Calcular baseado em treinos
            'streak' => 7, // Implementar lógica de sequência
            'total_workouts' => $stats['total_workouts'] ?? 0,
            'weekly_frequency' => round($stats['weekly_frequency'] ?? 0, 1)
        ];
    }

    public function getSchedules() {
        $scheduleModel = new Schedule($this->db);
        $schedules = $scheduleModel->getAllSchedules();
        
        header('Content-Type: application/json');
        echo json_encode($schedules);
    }

    public function getAppointments() {
        $appointmentModel = new Appointment($this->db);
        $appointments = $appointmentModel->getUserAppointments($this->user_id);
        
        header('Content-Type: application/json');
        echo json_encode($appointments);
    }

    public function getWorkoutPlans() {
        $workoutModel = new Workout($this->db);
        $plans = $workoutModel->getUserWorkoutPlans($this->user_id);
        
        header('Content-Type: application/json');
        echo json_encode($plans);
    }

    public function createAppointment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $appointmentModel = new Appointment($this->db);
            $result = $appointmentModel->createAppointment(
                $this->user_id,
                $data['schedule_id'],
                $data['title'],
                $data['date']
            );
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Agendamento criado!' : 'Erro ao criar agendamento'
            ]);
        }
    }

    public function confirmAppointment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $appointmentModel = new Appointment($this->db);
            $result = $appointmentModel->confirmAppointment($data['id']);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Agendamento confirmado!' : 'Erro ao confirmar'
            ]);
        }
    }

    public function cancelAppointment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $appointmentModel = new Appointment($this->db);
            $result = $appointmentModel->cancelAppointment($data['id']);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Agendamento cancelado!' : 'Erro ao cancelar'
            ]);
        }
    }

    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $userModel = new User($this->db);
            $result = $userModel->updateProfile(
                $this->user_id,
                $data['name'],
                $data['email'],
                $data['phone']
            );
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Perfil atualizado!' : 'Erro ao atualizar perfil'
            ]);
        }
    }
}
?>