<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

$unidade = $_GET['unidade'] ?? '';
$dataInicio = $_GET['inicio'] ?? '';
$dataFim = $_GET['fim'] ?? '';

try {
    $sql = "SELECT sugestoes_comentarios 
            FROM tbl_questionarios 
            WHERE sugestoes_comentarios IS NOT NULL 
              AND sugestoes_comentarios != ''";  // Adicionar o filtro aqui também

    $params = [];
    if ($unidade) {
        $sql .= " AND cod_unidade = (SELECT cod_unidade FROM tbl_unidades WHERE descricao = ? LIMIT 1)";
        $params[] = $unidade;
    }
    if ($dataInicio && $dataFim) {
        $sql .= " AND DATE(data) BETWEEN ? AND ?";
        $params[] = $dataInicio;
        $params[] = $dataFim;
    }

    $sql .= " ORDER BY data DESC";  // Ordenar por data

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log para debug
    error_log("Comentários encontrados: " . count($resultados));
    error_log("Dados: " . print_r($resultados, true));

    echo json_encode($resultados);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erro" => $e->getMessage()]);
    error_log("Erro obterComentario: " . $e->getMessage());
}
?>