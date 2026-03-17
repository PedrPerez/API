<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once 'config.php';

    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    if (!$dados || !isset($dados['id_questionario'])) {
        throw new Exception("ID do questionário não fornecido.");
    }

    $pdo->beginTransaction();

    $sqlPrincipal = "UPDATE tbl_questionarios 
                     SET cod_unidade = :unidade, 
                         data = :data, 
                         sugestoes_comentarios = :sugestoes
                     WHERE id_questionario = :id";

    $stmt = $pdo->prepare($sqlPrincipal);
    $stmt->execute([
        ':unidade'   => $dados['unidade'], 
        ':data'      => $dados['data'],
        ':sugestoes' => $dados['sugestoes'] ?? '',
        ':id'        => $dados['id_questionario']
    ]);

    $stmtDelete = $pdo->prepare("DELETE FROM tbl_questionarios_registos WHERE id_questionario = ?");
    $stmtDelete->execute([$dados['id_questionario']]);

    $sqlRegisto = "INSERT INTO tbl_questionarios_registos 
                   (id_questionario, id_indicador, muito_bom, bom, aceitavel, mau)
                   VALUES (:id_q, :id_ind, :mb, :b, :a, :m)";
    
    $stmtRegisto = $pdo->prepare($sqlRegisto);

    if (isset($dados['respostas']) && is_array($dados['respostas'])) {
        foreach ($dados['respostas'] as $resp) {
            $valor = $resp['valor'];
            
            $stmtRegisto->execute([
                ':id_q'   => $dados['id_questionario'],
                ':id_ind' => $resp['id_indicador'],
                ':mb'     => ($valor === 'muito_bom') ? 1 : 0,
                ':b'      => ($valor === 'bom') ? 1 : 0,
                ':a'      => ($valor === 'aceitavel') ? 1 : 0,
                ':m'      => ($valor === 'mau') ? 1 : 0
            ]);
        }
    }

    $pdo->commit();

    echo json_encode([
        "status" => "sucesso",
        "mensagem" => "Questionário #" . $dados['id_questionario'] . " atualizado com sucesso!"
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Erro SQL: " . $e->getMessage()
    ]);
}
?>