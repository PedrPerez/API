<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

$unidade = $_GET['unidade'] ?? '';
$dataInicio = $_GET['inicio'] ?? '';
$dataFim = $_GET['fim'] ?? '';

try {
    // JOIN com tbl_questoes para saber a que "pergunta pai" pertence cada indicador
    $sql = "SELECT 
                ques.descricao AS pergunta_titulo,
                ques.id AS pergunta_id,
                i.descricao AS indicador_texto,
                SUM(r.muito_bom) as mb,
                SUM(r.bom) as b,
                SUM(r.aceitavel) as a,
                SUM(r.mau) as m,
                (SUM(r.muito_bom) + SUM(r.bom) + SUM(r.aceitavel) + SUM(r.mau)) as total
            FROM tbl_questionarios_registos r
            JOIN tbl_indicadores i ON r.id_indicador = i.id
            JOIN tbl_questoes ques ON i.id_questao = ques.id
            JOIN tbl_questionarios q ON r.id_questionario = q.id_questionario
            WHERE 1=1";

    $params = [];
    if ($unidade) {
        $sql .= " AND q.cod_unidade = (SELECT cod_unidade FROM tbl_unidades WHERE descricao = ? LIMIT 1)";
        $params[] = $unidade;
    }
    if ($dataInicio && $dataFim) {
        $sql .= " AND DATE(q.data) BETWEEN ? AND ?";
        $params[] = $dataInicio;
        $params[] = $dataFim;
    }

    $sql .= " GROUP BY ques.id, i.id ORDER BY ques.id, i.id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $dadosRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar os resultados para o Frontend
    $agrupado = [];
    foreach ($dadosRaw as $row) {
        $pId = $row['pergunta_id'];
        if (!isset($agrupado[$pId])) {
            $agrupado[$pId] = [
                "titulo" => $row['pergunta_titulo'],
                "indicadores" => []
            ];
        }

        $t = (int)$row['total'] ?: 1;
        $agrupado[$pId]['indicadores'][] = [
            "texto" => $row['indicador_texto'],
            "mb" => (int)$row['mb'],
            "b" => (int)$row['b'],
            "a" => (int)$row['a'],
            "m" => (int)$row['m'],
            "mb_p" => round(($row['mb'] / $t) * 100, 1),
            "b_p" => round(($row['b'] / $t) * 100, 1),
            "a_p" => round(($row['a'] / $t) * 100, 1),
            "m_p" => round(($row['m'] / $t) * 100, 1)
        ];
    }

    echo json_encode(array_values($agrupado));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erro" => $e->getMessage()]);
}
?>