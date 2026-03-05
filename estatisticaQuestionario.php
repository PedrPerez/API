<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config.php';

// Receber filtros via GET
$inicio = $_GET['inicio'] ?? null;
$fim = $_GET['fim'] ?? null;
$cod_unidade = $_GET['unidade'] ?? '';

try {
    $params = [];
    $sql = "SELECT 
                q.descricao as indicador,
                SUM(CAST(r.muito_bom AS INT)) as mb,
                SUM(CAST(r.bom AS INT)) as b,
                SUM(CAST(r.aceitavel AS INT)) as a,
                SUM(CAST(r.mau AS INT)) as m
            FROM tbl_questionarios_registos r
            JOIN tbl_questionarios h ON r.id_questionario = h.id_questionario
            JOIN tbl_questoes q ON r.id_indicador = q.id
            WHERE 1=1";

    if ($inicio && $fim) {
        $sql .= " AND h.data BETWEEN ? AND ?";
        $params[] = $inicio;
        $params[] = $fim;
    }

    if ($cod_unidade !== '') {
        $sql .= " AND h.cod_unidade = ?";
        $params[] = $cod_unidade;
    }

    $sql .= " GROUP BY q.descricao";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($resultados);
} catch (Exception $e) {
    echo json_encode(["erro" => $e->getMessage()]);
}
?>