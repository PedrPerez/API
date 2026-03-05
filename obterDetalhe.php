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
    // 1. Buscar sugestões do questionário mestre
    $stmtS = $pdo->prepare("SELECT sugestoes_comentarios FROM tbl_questionarios WHERE id_questionario = ?");
    $stmtS->execute([$id]);
    $sugestoes = $stmtS->fetchColumn();

    // 2. Buscar indicadores, respostas e o título da pergunta (tbl_questoes)
    // Fazemos o JOIN com tbl_indicadores para o texto do item e tbl_questoes para o título do grupo
    $sql = "SELECT 
                r.*, 
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

    // 3. Agrupar os dados por Pergunta para o Estilo Acordião
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

        // Determinar qual escala foi marcada para facilitar o React (opcional, mas ajuda)
        $valorMarcado = '';
        if ($row['muito_bom']) $valorMarcado = 'muito_bom';
        else if ($row['bom']) $valorMarcado = 'bom';
        else if ($row['aceitavel']) $valorMarcado = 'aceitavel';
        else if ($row['mau']) $valorMarcado = 'mau';

        $respostasAgrupadas[$pId]['indicadores'][] = [
            "texto" => $row['indicador_texto'],
            "valor" => $valorMarcado,
            // Mantemos os originais caso prefiras usar as classes diretamente
            "muito_bom" => $row['muito_bom'],
            "bom" => $row['bom'],
            "aceitavel" => $row['aceitavel'],
            "mau" => $row['mau']
        ];
    }

    echo json_encode([
        "sugestoes" => $sugestoes,
        "respostas_agrupadas" => array_values($respostasAgrupadas) // Reset das chaves para array simples
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erro" => $e->getMessage()]);
}
?>