<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config.php';

try {
    // Seleciona a questão e o seu indicador correspondente
    $sql = "SELECT q.id as questao_id, q.descricao as questao_titulo, 
                   i.id as indicador_id, i.descricao as indicador_texto
            FROM tbl_questoes q
            INNER JOIN tbl_indicadores i ON q.id = i.id_questao
            WHERE q.activo = 1 AND i.activo = 1
            ORDER BY q.id ASC, i.id ASC";
            
    $stmt = $pdo->query($sql);
    $dadosRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupa os indicadores dentro de cada questão
    $questoesAgrupadas = [];
    foreach ($dadosRaw as $linha) {
        $idQ = $linha['questao_id'];
        if (!isset($questoesAgrupadas[$idQ])) {
            $questoesAgrupadas[$idQ] = [
                'id' => $idQ,
                'titulo' => $linha['questao_titulo'],
                'indicadores' => []
            ];
        }
        $questoesAgrupadas[$idQ]['indicadores'][] = [
            'id' => $linha['indicador_id'],
            'texto' => $linha['indicador_texto']
        ];
    }

    echo json_encode(array_values($questoesAgrupadas));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erro" => $e->getMessage()]);
}
?>