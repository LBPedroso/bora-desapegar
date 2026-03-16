<?php
// Verificar admin cadastrado
require_once '../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<h2>Administradores cadastrados:</h2>";
    
    $stmt = $db->query("SELECT id, nome, email, ativo FROM usuarios_admin");
    $admins = $stmt->fetchAll();
    
    if (count($admins) > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Ativo</th></tr>";
        
        foreach($admins as $admin) {
            echo "<tr>";
            echo "<td>{$admin['id']}</td>";
            echo "<td>{$admin['nome']}</td>";
            echo "<td>{$admin['email']}</td>";
            echo "<td>" . ($admin['ativo'] ? 'Sim' : 'Não') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "Nenhum admin cadastrado.";
    }
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage();
}
?>
