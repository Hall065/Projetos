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
            // Usa '??' para evitar erro se o campo não vier
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // 1. Validação Básica: Ver se tem campos vazios
            if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
                return ['type' => 'error', 'text' => 'Por favor, preencha todos os campos!'];
            }

            // 2. A MUDANÇA PRINCIPAL: Verificar se as senhas batem
            if ($password !== $confirm_password) {
                return ['type' => 'error', 'text' => 'As senhas não coincidem!'];
            }

            // 3. Verifica se já existe
            if ($this->userModel->exists($email)) {
                return ['type' => 'error', 'text' => 'Email já cadastrado!'];
            }

            // 4. Tenta registrar
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
                    'nome' => $user['nome'],
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

    public function forgotPassword($email)
    {
        // 1. Verifica se o e-mail existe no banco
        // (Reutilizando seu método exists que já tem no User.php)
        if (!$this->userModel->exists($email)) {
            return ['success' => false, 'error' => 'E-mail não encontrado.'];
        }

        // 2. Gera o Token e a Validade (1 hora)
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // 3. Chama o Model para salvar
        if ($this->userModel->salvarTokenRecuperacao($email, $token, $expires)) {

            // 4. Cria o Link (Simulação Local)
            $link = "http://localhost/TECHFIT_ACADEMIA/View/reset_password.php?token=" . $token;

            return [
                'success' => true,
                'link' => $link // Retorna o link para o JS mostrar
            ];
        } else {
            return ['success' => false, 'error' => 'Erro ao gerar token no banco.'];
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
