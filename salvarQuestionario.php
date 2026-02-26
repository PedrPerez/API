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

    if (!$dados) {
        throw new Exception("Nenhum dado recebido.");
    }

    $pdo->beginTransaction();

    $sqlPrincipal = "INSERT INTO tbl_questionarios 
                    (cod_unidade, data, sugestoes_comentarios, utilizador_registo, data_registo)
                    VALUES (:unidade, :data, :sugestoes, :utilizador, NOW())";

    $stmt = $pdo->prepare($sqlPrincipal);
    $stmt->execute([
        ':unidade'    => $dados['unidade'] ?? null,
        ':data'       => $dados['data'] ?? null,
        ':sugestoes'  => $dados['conteudo'] ?? '',
        ':utilizador' => $dados['utilizador'] ?? 'Admin_HVR'
    ]);

    $idQuestionario = $pdo->lastInsertId();

    if (isset($dados['respostas']) && is_array($dados['respostas'])) {
        
        $sqlRegisto = "INSERT INTO tbl_questionarios_registos 
                       (id_questionario, id_indicador, muito_bom, bom, aceitavel, mau)
                       VALUES (:id_q, :id_ind, :mb, :b, :a, :m)";
        
        $stmtRegisto = $pdo->prepare($sqlRegisto);

        $sqlValidarQuestao = "SELECT id FROM tbl_questoes WHERE id = :id AND activo = 1";
        $stmtValidar = $pdo->prepare($sqlValidarQuestao);

        foreach ($dados['respostas'] as $resp) {
            // Validação de segurança: verifica se o ID da pergunta é válido na tabela tbl_questoes
            $stmtValidar->execute([':id' => $resp['id_indicador']]);
            if (!$stmtValidar->fetch()) {
                throw new Exception("Pergunta com ID " . $resp['id_indicador'] . " inválida ou inativa.");
            }

            // Mapeamento binário (0 ou 1) para a tabela de registos
            $stmtRegisto->execute([
                ':id_q'   => $idQuestionario,
                ':id_ind' => $resp['id_indicador'],
                ':mb'     => ($resp['valor'] === 'muito_bom') ? 1 : 0,
                ':b'      => ($resp['valor'] === 'bom') ? 1 : 0,
                ':a'      => ($resp['valor'] === 'aceitavel') ? 1 : 0,
                ':m'      => ($resp['valor'] === 'mau') ? 1 : 0
            ]);
        }
    }

    // Confirmar todas as operações
    $pdo->commit();

    echo json_encode([
        "status" => "sucesso",
        "mensagem" => "Dados gravados e validados com base na tabela de questões!"
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        "status" => "erro",
        "mensagem" => $e->getMessage()
    ]);
}
?>