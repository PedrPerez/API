<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

$dataInicio = $_GET['inicio'] ?? '';
$dataFim = $_GET['fim'] ?? '';

try {
    // 1. Buscar os tipos de mensagens (categorias)
    $sqlTipos = "SELECT id, descricao FROM tbl_tipo_menssagem WHERE activo = '1' ORDER BY descricao";
    $stmtTipos = $pdo->query($sqlTipos);
    $categorias = $stmtTipos->fetchAll(PDO::FETCH_ASSOC);

    // 2. Construir a query dinâmica para somar cada tipo
    $selectParts = [];
    foreach ($categorias as $cat) {
        $idCat = $cat['id'];
        $selectParts[] = "SUM(CASE WHEN r.cod_tipo = $idCat THEN 1 ELSE 0 END) AS total_cat_$idCat";
    }
    
    $extraFields = !empty($selectParts) ? implode(", ", $selectParts) . "," : "";

    // Ajustado para tbl_registos_emails e agrupado por utilizador_registo
    $sql = "SELECT 
                r.utilizador_registo AS agrupamento_nome,
                $extraFields
                COUNT(r.id_email) as total_geral
            FROM tbl_registos_emails r
            WHERE 1=1";

    $params = [];
    if ($dataInicio && $dataFim) {
        $sql .= " AND DATE(r.data) BETWEEN ? AND ?";
        $params[] = $dataInicio;
        $params[] = $dataFim;
    }

    $sql .= " GROUP BY r.utilizador_registo ORDER BY total_geral DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $dadosRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Totais para o gráfico de Pizza
    $totaisPizza = [];
    foreach ($categorias as $cat) {
        $idCat = $cat['id'];
        $soma = array_sum(array_column($dadosRaw, "total_cat_$idCat"));
        if ($soma > 0) { // Só adiciona se houver dados
            $totaisPizza[] = [
                "name" => $cat['descricao'],
                "value" => (int)$soma
            ];
        }
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