<?php
require_once 'clases/mysql.inc.php';

$db = new mod_db();
$pdo = $db->getConexion();

echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Privilegios de la Base de Datos</title>';
echo '<link rel="stylesheet" href="css/cmxform.css" type="text/css" />';
echo '<link rel="stylesheet" href="Estilos/general.css" type="text/css" />';
echo '</head><body><div class="container" style="max-width:720px;margin:40px auto;">';
echo '<h2>Privilegios del Usuario de Base de Datos</h2>';

try {
    $stmt = $pdo->query('SHOW GRANTS FOR CURRENT_USER');
    echo '<pre style="background:#f8f8f8;padding:16px;border:1px solid #ddd;border-radius:6px;">';
    while ($fila = $stmt->fetch(PDO::FETCH_NUM)) {
        echo htmlspecialchars($fila[0]) . "\n\n";
    }
    echo '</pre>';
    echo '<p>Comando sugerido para verificar privilegios de un usuario específico:</p>';
    echo '<code>SHOW GRANTS FOR \"app_user\"@\"localhost\";</code>';
} catch (PDOException $e) {
    echo '<p style="color:red;">No fue posible obtener los privilegios: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

echo '</div></body></html>';

