<?php
/**
 * Model: Usuario (Administrador)
 * Gerencia os usuários do painel administrativo
 */

require_once __DIR__ . '/Model.php';

class Usuario extends Model {
    protected $table = 'usuarios_admin';

    public function __construct() {
        parent::__construct();
        $this->garantirEstruturaUsuariosAdmin();
        $this->garantirAdminPadrao();
    }

    /**
     * Garante a estrutura mínima da tabela de administradores.
     */
    private function garantirEstruturaUsuariosAdmin() {
        $sql = "CREATE TABLE IF NOT EXISTS usuarios_admin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            senha VARCHAR(255) NOT NULL,
            ativo BOOLEAN DEFAULT TRUE,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ultimo_acesso TIMESTAMP NULL,
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->exec($sql);
    }

    /**
     * Em banco novo, cria um admin inicial para liberar acesso ao painel.
     */
    private function garantirAdminPadrao() {
        $sqlCount = "SELECT COUNT(*) AS total FROM {$this->table}";
        $stmtCount = $this->db->query($sqlCount);
        $total = (int) ($stmtCount->fetch()['total'] ?? 0);

        if ($total > 0) {
            return;
        }

        $sqlInsert = "INSERT INTO {$this->table} (nome, email, senha, ativo) VALUES (?, ?, ?, 1)";
        $stmtInsert = $this->db->prepare($sqlInsert);
        $stmtInsert->execute([
            'Administrador',
            'admin@boradesapegar.com',
            password_hash('admin123', PASSWORD_DEFAULT)
        ]);
    }
    
    /**
     * Buscar usuário por email
     */
    public function findByEmail($email) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Verificar login
     */
    public function verificarLogin($email, $senha) {
        $usuario = $this->findByEmail($email);
        
        if ($usuario && password_verify($senha, $usuario['senha']) && $usuario['ativo']) {
            // Atualizar último acesso
            $this->atualizarUltimoAcesso($usuario['id']);
            return $usuario;
        }
        
        return false;
    }
    
    /**
     * Criar novo usuário
     */
    public function criar($dados) {
        // Hash da senha
        $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        
        return $this->create($dados);
    }
    
    /**
     * Atualizar último acesso
     */
    private function atualizarUltimoAcesso($id) {
        $sql = "UPDATE {$this->table} SET ultimo_acesso = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Alterar senha
     */
    public function alterarSenha($id, $senha_nova) {
        $senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
        return $this->update($id, ['senha' => $senha_hash]);
    }
}
