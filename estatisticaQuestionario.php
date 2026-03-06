<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

$unidade = $_GET['unidade'] ?? '';
$dataInicio = $_GET['inicio'] ?? '';
$dataFim = $_GET['fim'] ?? '';

try {
    $sql = "SELECT 
                i.descricao AS indicador,
                SUM(r.muito_bom) as mb,
                SUM(r.bom) as b,
                SUM(r.aceitavel) as a,
                SUM(r.mau) as m
            FROM tbl_questionarios_registos r
            JOIN tbl_indicadores i ON r.id_indicador = i.id
            JOIN tbl_questionarios q ON r.id_questionario = q.id_questionario
            WHERE 1=1";

    $params = [];
    if ($unidade) {
        $sql .= " AND q.nome_unidade = ?";
        $params[] = $unidade;
    }
    if ($dataInicio && $dataFim) {
        $sql .= " AND DATE(q.data) BETWEEN ? AND ?";
        $params[] = $dataInicio;
        $params[] = $dataFim;
    }

    $sql .= " GROUP BY i.id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($resultados);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erro" => $e->getMessage()]);
}
?>