<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

$unidade = $_GET['unidade'] ?? '';
$dataInicio = $_GET['inicio'] ?? '';
$dataFim = $_GET['fim'] ?? '';

try {
    // 1. Buscar as categorias dinamicamente (baseado no teu obterTipoMensagem.php)
    $sqlTipos = "SELECT id, descricao FROM tbl_tipo_menssagem WHERE activo = '1' ORDER BY descricao";
    $stmtTipos = $pdo->query($sqlTipos);
    $categorias = $stmtTipos->fetchAll(PDO::FETCH_ASSOC);

    // 2. Construir a query de contagem baseada nas categorias encontradas
    $selectParts = [];
    foreach ($categorias as $cat) {
        // Criamos um alias limpo para o JSON (ex: "cat_1")
        $idCat = $cat['id'];
        $selectParts[] = "SUM(CASE WHEN r.cod_tipo = $idCat THEN 1 ELSE 0 END) AS total_cat_$idCat";
    }
    
    $extraFields = !empty($selectParts) ? implode(", ", $selectParts) . "," : "";

    $sql = "SELECT 
                u.descricao AS unidade_nome,
                u.cod_unidade AS unidade_id,
                $extraFields
                COUNT(r.id_impresso) as total_geral
            FROM tbl_registos_impressos r
            JOIN tbl_unidades u ON r.cod_servico = u.cod_unidade
            WHERE 1=1";

    $params = [];
    if ($unidade) {
        $sql .= " AND u.descricao = ?";
        $params[] = $unidade;
    }
    if ($dataInicio && $dataFim) {
        $sql .= " AND DATE(r.data) BETWEEN ? AND ?";
        $params[] = $dataInicio;
        $params[] = $dataFim;
    }

    $sql .= " GROUP BY u.cod_unidade ORDER BY total_geral DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $dadosRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Preparar dados para o gráfico circular (Total acumulado de cada categoria)
    $totaisPizza = [];
    foreach ($categorias as $cat) {
        $idCat = $cat['id'];
        $soma = array_sum(array_column($dadosRaw, "total_cat_$idCat"));
        $totaisPizza[] = [
            "name" => $cat['descricao'],
            "value" => (int)$soma
        ];
    }

    echo json_encode([
        "categorias" => $categorias,
        "lista" => $dadosRaw,
        "totais_pizza" => $totaisPizza
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erro" => $e->getMessage()]);
}
?>