<?php
/**
 * Controller: Autenticação
 * Gerencia login, registro e sessões de usuários
 */

require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {
    private $clienteModel;
    private $usuarioModel;
    
    public function __construct() {
        $this->clienteModel = new Cliente();
        $this->usuarioModel = new Usuario();
        
        // Iniciar sessão se não estiver iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Login de cliente
     */
    public function loginCliente($cpfOuTelefone, $senha) {
        try {
            $cliente = $this->clienteModel->findByCpfOrTelefone($cpfOuTelefone);

            if (!$cliente) {
                return ['success' => false, 'message' => 'CPF ou telefone não cadastrado'];
            }

            if (!password_verify($senha, $cliente['senha'])) {
                return ['success' => false, 'message' => 'Senha incorreta'];
            }

            // Criar sessão
            $_SESSION['cliente_id'] = $cliente['id'];
            $_SESSION['cliente_nome'] = $cliente['nome'];
            $_SESSION['cliente_email'] = $cliente['email'] ?? null;
            $_SESSION['cliente_cpf'] = $cliente['cpf'] ?? null;
            $_SESSION['cliente_telefone'] = $cliente['telefone'] ?? null;
            $_SESSION['tipo_usuario'] = 'cliente';

            return ['success' => true, 'message' => 'Login realizado com sucesso'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Não foi possível fazer login no momento.'];
        }
    }
    
    /**
     * Login de administrador
     */
    public function loginAdmin($email, $senha) {
        $usuario = $this->usuarioModel->findByEmail($email);
        
        if (!$usuario) {
            return ['success' => false, 'message' => 'Email não cadastrado'];
        }
        
        if (!$usuario['ativo']) {
            return ['success' => false, 'message' => 'Usuário inativo'];
        }
        
        if (!password_verify($senha, $usuario['senha'])) {
            return ['success' => false, 'message' => 'Senha incorreta'];
        }
        
        // Criar sessão
        $_SESSION['admin_id'] = $usuario['id'];
        $_SESSION['admin_nome'] = $usuario['nome'];
        $_SESSION['admin_email'] = $usuario['email'];
        $_SESSION['tipo_usuario'] = 'admin';
        
        return ['success' => true, 'message' => 'Login realizado com sucesso'];
    }
    
    /**
     * Registrar novo cliente
     */
    public function registrarCliente($dados) {
        // Validar dados básicos
        if (empty($dados['nome']) || empty($dados['senha'])) {
            return ['success' => false, 'message' => 'Preencha nome e senha'];
        }

        // Validar que pelo menos CPF OU telefone foi informado
        if (empty($dados['cpf']) && empty($dados['telefone'])) {
            return ['success' => false, 'message' => 'Informe CPF ou telefone para cadastro'];
        }

        // Normalizar campos numéricos
        $cpfNormalizado = preg_replace('/\D/', '', (string) ($dados['cpf'] ?? ''));
        $telefoneNormalizado = preg_replace('/\D/', '', (string) ($dados['telefone'] ?? ''));

        $dados['cpf'] = $cpfNormalizado !== '' ? $cpfNormalizado : null;
        $dados['telefone'] = $telefoneNormalizado !== '' ? $telefoneNormalizado : null;
        $dados['email'] = null;

        // Endereço agora é opcional
        $dados['endereco_rua'] = !empty($dados['endereco_rua']) ? $dados['endereco_rua'] : null;
        $dados['endereco_numero'] = !empty($dados['endereco_numero']) ? $dados['endereco_numero'] : null;
        $dados['endereco_complemento'] = !empty($dados['endereco_complemento']) ? $dados['endereco_complemento'] : null;
        $dados['endereco_bairro'] = !empty($dados['endereco_bairro']) ? $dados['endereco_bairro'] : null;
        $dados['endereco_cidade'] = !empty($dados['endereco_cidade']) ? $dados['endereco_cidade'] : null;
        $dados['endereco_estado'] = !empty($dados['endereco_estado']) ? $dados['endereco_estado'] : null;
        $dados['endereco_cep'] = !empty($dados['endereco_cep']) ? preg_replace('/\D/', '', $dados['endereco_cep']) : null;

        // Evitar duplicidade de CPF
        if (!empty($dados['cpf']) && $this->clienteModel->findByCPF($dados['cpf'])) {
            return ['success' => false, 'message' => 'Este CPF já está cadastrado'];
        }

        // Evitar duplicidade de telefone
        if (!empty($dados['telefone']) && $this->clienteModel->findByTelefone($dados['telefone'])) {
            return ['success' => false, 'message' => 'Este telefone já está cadastrado'];
        }

        // Hash da senha
        $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);

        // Criar cliente
        try {
            $cliente_id = $this->clienteModel->create($dados);

            // Auto-login
            $cliente = $this->clienteModel->findById($cliente_id);
            $_SESSION['cliente_id'] = $cliente['id'];
            $_SESSION['cliente_nome'] = $cliente['nome'];
            $_SESSION['cliente_email'] = $cliente['email'] ?? null;
            $_SESSION['cliente_cpf'] = $cliente['cpf'] ?? null;
            $_SESSION['cliente_telefone'] = $cliente['telefone'] ?? null;
            $_SESSION['tipo_usuario'] = 'cliente';

            return ['success' => true, 'message' => 'Cadastro realizado com sucesso'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao cadastrar cliente. Verifique os dados e tente novamente.'];
        }
    }
    
    /**
     * Logout
     */
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Logout realizado com sucesso'];
    }
    
    /**
     * Verificar se está logado como cliente
     */
    public static function isCliente() {
        return isset($_SESSION['cliente_id']) && $_SESSION['tipo_usuario'] === 'cliente';
    }
    
    /**
     * Verificar se está logado como admin
     */
    public static function isAdmin() {
        return isset($_SESSION['admin_id']) && $_SESSION['tipo_usuario'] === 'admin';
    }
    
    /**
     * Requerer login de cliente
     */
    public static function requireCliente() {
        if (!self::isCliente()) {
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }
    }
    
    /**
     * Requerer login de admin
     */
    public static function requireAdmin() {
        if (!self::isAdmin()) {
            header('Location: ' . SITE_URL . '/admin/login.php');
            exit;
        }
    }
}
