<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config.php';

try {
    // Busca os dados da tbl_questionarios e o nome da unidade da tbl_unidades
    $sql = "SELECT q.id_questionario, 
                   q.data, 
                   u.descricao as nome_unidade, 
                   q.utilizador_registo
            FROM tbl_questionarios q
            LEFT JOIN tbl_unidades u ON q.cod_unidade = u.cod_unidade
            ORDER BY q.id_questionario DESC";

    $stmt = $pdo->query($sql);
    $questionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($questionarios);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erro" => $e->getMessage()]);
}
?>