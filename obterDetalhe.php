<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["erro" => "ID não fornecido"]);
    exit;
}

try {
    // 1. Buscar dados mestres (Unidade, Data e Sugestões)
    $stmtS = $pdo->prepare("SELECT cod_unidade, data, sugestoes_comentarios FROM tbl_questionarios WHERE id_questionario = ?");
    $stmtS->execute([$id]);
    $mestre = $stmtS->fetch(PDO::FETCH_ASSOC);

    if (!$mestre) {
        echo json_encode(["erro" => "Questionário não encontrado"]);
        exit;
    }

    // 2. Buscar indicadores e as respostas dadas
    $sql = "SELECT 
                r.id_indicador,
                r.muito_bom, r.bom, r.aceitavel, r.mau,
                i.descricao AS indicador_texto, 
                q.id AS id_pergunta,
                q.descricao AS pergunta_titulo
            FROM tbl_questionarios_registos r
            JOIN tbl_indicadores i ON r.id_indicador = i.id
            JOIN tbl_questoes q ON i.id_questao = q.id
            WHERE r.id_questionario = ?
            ORDER BY q.id ASC, i.id ASC";
    
    $stmtR = $pdo->prepare($sql);
    $stmtR->execute([$id]);
    $dadosRaw = $stmtR->fetchAll(PDO::FETCH_ASSOC);

    // 3. Agrupar os dados para o React
    $respostasAgrupadas = [];
    foreach ($dadosRaw as $row) {
        $pId = $row['id_pergunta'];
        
        if (!isset($respostasAgrupadas[$pId])) {
            $respostasAgrupadas[$pId] = [
                "id" => $pId,
                "titulo" => $row['pergunta_titulo'],
                "indicadores" => []
            ];
        }

        $respostasAgrupadas[$pId]['indicadores'][] = [
            "id_indicador" => $row['id_indicador'], // Importante para o React mapear a resposta
            "texto" => $row['indicador_texto'],
            "muito_bom" => (int)$row['muito_bom'],
            "bom" => (int)$row['bom'],
            "aceitavel" => (int)$row['aceitavel'],
            "mau" => (int)$row['mau']
        ];
    }

    // 4. Retorno estruturado
    echo json_encode([
        "cod_unidade" => $mestre['cod_unidade'],
        "data" => $mestre['data'],
        "sugestoes" => $mestre['sugestoes_comentarios'],
        "respostas_agrupadas" => array_values($respostasAgrupadas)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erro" => $e->getMessage()]);
}
?>