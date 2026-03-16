<?php
/**
 * Configurações Gerais do Sistema
 * Bora Desapegar
 */

// Configurações do Site
define('SITE_NAME', 'Bora Desapegar');
define('SITE_SLOGAN', 'Brecho infantil online com carinho e preco justo');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocal = in_array($host, ['localhost', 'localhost:8080', '127.0.0.1', '127.0.0.1:8080']);
define('SITE_URL', $isLocal ? $protocol . '://' . $host . '/desapega' : $protocol . '://' . $host);

// Informações de Contato
define('SITE_TELEFONE', '(44) 99857-1669');
define('SITE_INSTAGRAM', 'https://www.instagram.com/desapegoinfantil.menino/');
define('SITE_CIDADE', 'Campo Mourão');
define('SITE_ESTADO', 'PR');

// Configurações de Sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Diretórios
define('BASE_PATH', dirname(__DIR__));
define('ASSETS_PATH', SITE_URL . '/public/assets');

// Configurações de Horário de Funcionamento
define('DIAS_FUNCIONAMENTO', ['Segunda', 'Terca', 'Quarta', 'Quinta', 'Sexta', 'Sabado', 'Domingo']);
define('HORARIO_INICIO', '09:00');
define('HORARIO_FIM', '19:00');

// Configurações de Entrega
define('TAXA_ENTREGA', 5.00);
define('PEDIDO_MINIMO', 30.00);

// Cores da Identidade Visual
define('COR_PRIMARIA', '#A8D8FF');
define('COR_SECUNDARIA', '#4A90E2');
define('COR_TERCIARIA', '#EAF6FF');

// Auto-load de classes
spl_autoload_register(function($class) {
    $paths = [
        BASE_PATH . '/models/' . $class . '.php',
        BASE_PATH . '/controllers/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Incluir conexão com banco
require_once BASE_PATH . '/config/database.php';
