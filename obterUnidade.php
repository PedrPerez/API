<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config.php';

try {
    // Busca todas as unidades da tabela tbl_unidades
    $sql = "SELECT cod_unidade, descricao FROM tbl_unidades ORDER BY descricao";
    $stmt = $pdo->query($sql);
    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($unidades);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erro" => $e->getMessage()]);
}
?>