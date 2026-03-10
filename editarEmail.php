<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

require_once 'config.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['id_email'])) {
        throw new Exception("ID do email não fornecido.");
    }

    $sql = "UPDATE tbl_registos_emails SET 
                data = :data,
                cod_tipo = :cod_tipo,
                nome = :nome,
                email = :email,
                assunto = :assunto,
                conteudo = :conteudo
            WHERE id_email = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':data'     => $input['data'],
        ':cod_tipo' => $input['tipo'],
        ':nome'     => $input['nome'],
        ':email'    => $input['email'],
        ':assunto'  => $input['assunto'],
        ':conteudo' => $input['conteudo'],
        ':id'       => $input['id_email']
    ]);

    echo json_encode(["status" => "sucesso", "mensagem" => "Email atualizado com sucesso!"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
}
?>