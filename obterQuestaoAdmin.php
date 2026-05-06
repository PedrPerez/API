<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

try {
    // LEFT JOIN para garantir que aparecem questões mesmo que ainda não tenham indicadores
    $sql = "SELECT q.id as questao_id, q.descricao as questao_titulo, q.activo as questao_status,
                   i.id as indicador_id, i.descricao as indicador_texto, i.activo as indicador_status
            FROM tbl_questoes q
            LEFT JOIN tbl_indicadores i ON q.id = i.id_questao
            ORDER BY q.id DESC, i.id ASC";
            
    $stmt = $pdo->query($sql);
    $dadosRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $questoesAgrupadas = [];
    foreach ($dadosRaw as $linha) {
        $idQ = $linha['questao_id'];
        if (!isset($questoesAgrupadas[$idQ])) {
            $questoesAgrupadas[$idQ] = [
                'id' => $idQ,
                'titulo' => $linha['questao_titulo'],
                'activo' => $linha['questao_status'],
                'indicadores' => []
            ];
        }
        if ($linha['indicador_id']) {
            $questoesAgrupadas[$idQ]['indicadores'][] = [
                'id' => $linha['indicador_id'],
                'texto' => $linha['indicador_texto'],
                'activo' => $linha['indicador_status']
            ];
        }
    }

    echo json_encode(array_values($questoesAgrupadas));
} catch (Exception $e) {
    echo json_encode(["erro" => $e->getMessage()]);
}
?>