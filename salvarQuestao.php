<?php
// Adiciona estas linhas ANTES de qualquer outro código ou require
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Se o browser enviar um pedido de verificação (OPTIONS), respondemos OK e paramos
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config.php';

// Captura o input bruto
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Verificação de segurança
if (!$data) {
    echo json_encode(["status" => "erro", "mensagem" => "Não foi recebido nenhum JSON válido. Recebido: " . $json]);
    exit;
}

if (empty($data['titulo'])) {
    echo json_encode(["status" => "erro", "mensagem" => "O título da questão está vazio."]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Inserir Questão (verifica se a coluna é 'descricao' ou 'titulo' na tua DB)
    // De acordo com o teu print anterior, a coluna é 'descricao'
    $stmtQ = $pdo->prepare("INSERT INTO tbl_questoes (descricao, activo) VALUES (?, 1)");
    $stmtQ->execute([$data['titulo']]);
    $idQuestao = $pdo->lastInsertId();

    // 2. Inserir Indicadores
    if (!empty($data['indicadores']) && is_array($data['indicadores'])) {
        $stmtI = $pdo->prepare("INSERT INTO tbl_indicadores (id_questao, descricao, muito_bom, bom, aceitavel, mau, activo) VALUES (?, ?, 1, 1, 1, 1, 1)");
        
        foreach ($data['indicadores'] as $texto) {
            $limpo = trim($texto);
            if ($limpo !== "") {
                $stmtI->execute([$idQuestao, $limpo]);
            }
        }
    }

    $pdo->commit();
    echo json_encode(["status" => "sucesso", "id_gerado" => $idQuestao]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
}
?>