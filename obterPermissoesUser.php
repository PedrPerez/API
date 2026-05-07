<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

$iduser = isset($_GET['iduser']) ? $_GET['iduser'] : null;

if (!$iduser) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT idmenu, activo FROM tblPermissoes WHERE iduser = ?");
    $stmt->execute([$iduser]);
    $permissoes = $stmt->fetchAll();
    
    echo json_encode($permissoes);
} catch (PDOException $e) {
    echo json_encode(["erro" => $e->getMessage()]);
}