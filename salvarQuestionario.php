<?php
// Configuração de Headers para permitir comunicação com o React
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Responde a requisições preflight do navegador (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Desativar exibição de erros HTML para não corromper o JSON de resposta
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once 'config.php';

    // Obter os dados enviados pelo React
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    if (!$dados || !isset($dados['unidade']) || !isset($dados['respostas'])) {
        throw new Exception("Dados incompletos ou formato inválido.");
    }

    // Iniciar Transação - Ou grava tudo com sucesso, ou não grava nada
    $pdo->beginTransaction();

    // 1. Inserir o registo mestre na tbl_questionarios (Imagem 027)
    $sqlPrincipal = "INSERT INTO tbl_questionarios 
                    (cod_unidade, data, sugestoes_comentarios, utilizador_registo, data_registo)
                    VALUES (:unidade, :data, :sugestoes, :utilizador, NOW())";

    $stmt = $pdo->prepare($sqlPrincipal);
    $stmt->execute([
        ':unidade'    => $dados['unidade'],
        ':data'       => $dados['data'],
        ':sugestoes'  => $dados['conteudo'] ?? '',
        ':utilizador' => $dados['utilizador'] ?? 'Admin'
    ]);

    // Obter o ID que acabou de ser gerado para este questionário
    $idQuestionario = $pdo->lastInsertId();

    // 2. Preparar a inserção das respostas na tbl_questionarios_registos (Imagem 028)
    $sqlRegisto = "INSERT INTO tbl_questionarios_registos 
                   (id_questionario, id_indicador, muito_bom, bom, aceitavel, mau)
                   VALUES (:id_q, :id_ind, :mb, :b, :a, :m)";
    
    $stmtRegisto = $pdo->prepare($sqlRegisto);

    // Iterar sobre cada resposta vinda do formulário
    foreach ($dados['respostas'] as $resp) {
        // Mapear o valor de texto (ex: 'muito_bom') para colunas binárias (0 ou 1)
        $valor = $resp['valor'];
        
        $stmtRegisto->execute([
            ':id_q'   => $idQuestionario,
            ':id_ind' => $resp['id_indicador'],
            ':mb'     => ($valor === 'muito_bom') ? 1 : 0,
            ':b'      => ($valor === 'bom') ? 1 : 0,
            ':a'      => ($valor === 'aceitavel') ? 1 : 0,
            ':m'      => ($valor === 'mau') ? 1 : 0
        ]);
    }

    // Se chegou aqui sem erros, confirma as alterações na base de dados
    $pdo->commit();

    echo json_encode([
        "status" => "sucesso",
        "mensagem" => "Questionário gravado com sucesso!",
        "id" => $idQuestionario
    ]);

} catch (Exception $e) {
    // Se algo falhou, desfaz qualquer inserção feita nesta transação
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Erro ao salvar: " . $e->getMessage()
    ]);
}
?>