<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

try {
    // Seleciona apenas os menus que estão ativos no sistema
    $stmt = $pdo->query("SELECT idmenu, descmenu FROM tblMenus WHERE activo = 1");
    $menus = $stmt->fetchAll();
    
    echo json_encode($menus);
} catch (PDOException $e) {
    echo json_encode(["erro" => $e->getMessage()]);
}