<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 0);

try {

    require_once 'config.php';

    $id = $_GET['id'] ?? null;

    if (!$id) {
        echo json_encode([]);
        exit;
    }

    $sql = "SELECT 
                r.id_indicador,
                q.descricao,
                r.muito_bom,
                r.bom,
                r.aceitavel,
                r.mau
            FROM tbl_questionarios_registos r
            LEFT JOIN tbl_questoes q 
                ON q.id = r.id_indicador
            WHERE r.id_questionario = :id
            ORDER BY r.id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    $detalhes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($detalhes ?: []);

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([]);
}