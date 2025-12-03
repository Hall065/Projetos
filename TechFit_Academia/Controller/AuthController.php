<?php
// ATIVA EXIBIÇÃO DE ERROS
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../Config/Sessao.php'; 

require_once __DIR__ . '/../Database/Model/User.php';

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $password = $_POST['password'];

            if ($this->userModel->exists($email)) {
                return ['type' => 'error', 'text' => 'Email já cadastrado!'];
            }

            if ($this->userModel->register($name, $email, $phone, $password)) {
                return ['type' => 'success', 'text' => 'Cadastro realizado com sucesso!'];
            } else {
                return ['type' => 'error', 'text' => 'Erro ao cadastrar usuário!'];
            }
        }
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = $this->userModel->login($email, $password);

            if ($user) {
                // 1. Mantemos este para compatibilidade com as APIs antigas
                $_SESSION['user'] = $email;

                // 2. Guardamos o nível
                $_SESSION['nivel'] = $user['nivel_acesso'];

                // 3. NOVO: Guardamos os dados completos para exibição rápida no HTML
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'nome' => $user['nome'],      // Garanta que esta linha existe
                    'email' => $user['email'],
                    'telefone' => $user['telefone'],
                    'plano' => $user['plano'] ?? 'Standard',
                    'criado_em' => $user['criado_em']
                ];

                if ($user['nivel_acesso'] === 'admin') {
                    header("Location: Admin.php");
                } else {
                    header("Location: DashBoard.php");
                }
                exit;
            } else {
                return ['type' => 'error', 'text' => 'Email ou senha incorretos!'];
            }
        }
    }

    public function logout()
    {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
        header("Location: Login.php");
        exit;
    }
}

$auth = new AuthController();
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        $message = $auth->register();
    } elseif (isset($_POST['login'])) {
        $message = $auth->login();
    } elseif (isset($_POST['logout'])) {
        $auth->logout();
    }
}
