<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config.php';

try {
    // Selecionamos os campos necessários
    $sql = "SELECT iduser, username, nome, idcategoria, activo, pin FROM tbl_users ORDER BY iduser DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($users);

} catch (PDOException $e) {
    echo json_encode(["erro" => $e->getMessage()]);
}
?>