<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Teste de erros PHP<br><br>";

try {
    require_once '../config/config.php';
    echo "✅ config.php carregado<br>";
    
    require_once '../config/database.php';
    echo "✅ database.php carregado<br>";
    
    require_once '../controllers/AuthController.php';
    echo "✅ AuthController.php carregado<br>";
    
    $authController = new AuthController();
    echo "✅ AuthController instanciado<br>";
    
    echo "<br>Tudo OK! O erro deve ser específico do login.php";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage();
    echo "<br>Arquivo: " . $e->getFile();
    echo "<br>Linha: " . $e->getLine();
}
?>
