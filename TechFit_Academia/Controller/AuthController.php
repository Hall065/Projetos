<?php
// ATIVA EXIBIÇÃO DE ERROS (MANTENHA ISSO ATIVO NO SENAI!)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ==========================================================
// 🚨 SOLUÇÃO DE SESSÃO LOCAL (CRÍTICO NO AMBIENTE SENAI) 🚨
// ==========================================================
// Define o caminho absoluto para a pasta 'sessions_data' (CERTIFIQUE-SE DE CRIAR esta pasta na raiz do projeto)
$session_dir = __DIR__ . '/../sessions_data';

if (!is_dir($session_dir)) {
    if (!mkdir($session_dir, 0777, true)) {
        die("Erro fatal: Não foi possível criar a pasta de sessão: " . $session_dir);
    }
}
session_save_path($session_dir);
// ==========================================================


// Caminho absoluto para o Model/User.php 
require_once __DIR__ . '/../Database/Model/User.php';

// CRÍTICO: INICIA A SESSÃO APENAS UMA VEZ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//... O restante do seu AuthController segue daqui...
class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function register()
    {
        // CORREÇÃO: Usando os nomes em INGLÊS para bater com o formulário (name, phone, password)
        if (isset($_POST['name'], $_POST['email'], $_POST['phone'], $_POST['password'])) {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone']; // O formulário envia 'phone'
            $password = $_POST['password']; // O formulário envia 'password'

            if ($this->userModel->exists($email)) {
                return ['type' => 'error', 'text' => 'Email já cadastrado!'];
            }

            // O Model/User.php vai se encarregar de traduzir para 'nome' e 'telefone' do banco
            if ($this->userModel->register($name, $email, $phone, $password)) {
                return ['type' => 'success', 'text' => 'Cadastro realizado com sucesso! Faça seu login.'];
            } else {
                return ['type' => 'error', 'text' => 'Erro ao cadastrar usuário! Tente novamente.'];
            }
        }
    }

    public function login()
    {

        if (isset($_POST['email'], $_POST['password'])) {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = $this->userModel->login($email, $password);

            if ($user) {
                $_SESSION['user'] = $user['email'];
                $_SESSION['nivel'] = $user['nivel_acesso'];

                // Redirecionamento RELATIVO (voltando ao que estava funcionando em casa)
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
        // 1. Zera a variável de sessão na memória agora
        $_SESSION = array();

        // 2. Apaga o Cookie de Sessão do navegador (O Passo Mais Importante!)
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

        // 3. Destrói a sessão no servidor
        session_destroy();

        // 4. Redireciona para o login
        header("Location: Login.php");
        exit;
    }
}

$auth = new AuthController();
$message = null;

// Processamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        $message = $auth->register();
    } elseif (isset($_POST['login'])) {
        $message = $auth->login();
    } elseif (isset($_POST['logout'])) {
        $auth->logout();
    }
}
