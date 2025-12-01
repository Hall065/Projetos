<?php
require_once __DIR__ . '/../Model/User.php';
session_start();

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function register() {
        if(isset($_POST['name'], $_POST['email'], $_POST['phone'], $_POST['password'])) {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $password = $_POST['password'];

            if($this->userModel->exists($email)) {
                return "Email já cadastrado!";
            }

            if($this->userModel->register($name, $email, $phone, $password)) {
                $_SESSION['user'] = $email;
                header("Location: DashBoard.php");
                exit;
            } else {
                return "Erro ao cadastrar usuário!";
            }
        }
    }

    public function login() {
        if(isset($_POST['email'], $_POST['password'])) {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = $this->userModel->login($email, $password);
            if($user) {
                $_SESSION['user'] = $user['email'];
                header("Location: DashBoard.php");
                exit;
            } else {
                return "Email ou senha incorretos!";
            }
        }
    }

    public function logout() {
        session_destroy();
        header("Location: Login.php");
        exit;
    }
}

$auth = new AuthController();

// Processa formulários
$message = "";
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['register'])) {
        $message = $auth->register();
    } elseif(isset($_POST['login'])) {
        $message = $auth->login();
    }
}
?>
