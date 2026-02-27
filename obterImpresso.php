<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

try {
    // Fazemos JOIN para trazer o nome da unidade e do tipo de serviço
    $sql = "SELECT 
                r.id_impresso as id, 
                r.data, 
                r.descritivo, 
                r.resolucao,
                u.descricao as unidade_nome,
                t.descricao as tipo_nome,
                r.cod_servico as unidade_id,
                r.cod_tipo as tipo_id
            FROM tbl_registos_impressos r
            LEFT JOIN tbl_unidades u ON r.cod_servico = u.cod_unidade
            LEFT JOIN tbl_tipo_menssagem t ON r.cod_tipo = t.id
            ORDER BY r.data_registo DESC";

    $stmt = $pdo->query($sql);
    $registos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($registos);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erro" => $e->getMessage()]);
}