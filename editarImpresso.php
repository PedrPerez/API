<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['id_impresso'])) {
        throw new Exception("ID do impresso não fornecido.");
    }

    $sql = "UPDATE tbl_registos_impressos SET 
                data = :data,
                cod_servico = :cod_servico,
                cod_tipo = :cod_tipo,
                nome = :nome,
                morada = :morada,
                telefone = :telefone,
                email = :email,
                descritivo = :descritivo,
                resolucao = :resolucao
            WHERE id_impresso = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':data'         => $input['data'],
        ':cod_servico'  => $input['unidade'],
        ':cod_tipo'     => $input['tipo'],
        ':nome'         => $input['nome'],
        ':morada'       => $input['morada'],
        ':telefone'     => $input['tel'],
        ':email'        => $input['email'],
        ':descritivo'   => $input['descritivo'],
        ':resolucao'    => $input['resolucao'],
        ':id'           => $input['id_impresso']
    ]);

    echo json_encode(["status" => "sucesso", "mensagem" => "Registo atualizado!"]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
}
?>