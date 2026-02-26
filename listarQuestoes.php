<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config.php';

try {
    // Busca as questões ativas da imagem enviada
    $sql = "SELECT id, descricao FROM tbl_questoes WHERE activo = 1 ORDER BY id ASC";
    $stmt = $pdo->query($sql);
    $questoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($questoes);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erro" => $e->getMessage()]);
}
?>