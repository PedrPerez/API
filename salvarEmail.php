<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

require_once 'config.php';

try {
    $input = $_POST;
    if (empty($input)) {
        $input = json_decode(file_get_contents('php://input'), true);
    }

    $sql = "INSERT INTO tbl_registos_emails (
                data, cod_tipo, nome, email, conteudo, utilizador_registo, assunto
            ) VALUES (
                :data, :cod_tipo, :nome, :email, :conteudo, :utilizador, :assunto
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':data'       => $input['data'] ?? date('Y-m-d'),
        ':cod_tipo'   => $input['tipo'],
        ':nome'       => $input['nome'] ?? 'N/A',
        ':email'      => $input['email'],
        ':conteudo'   => $input['conteudo'],
        ':utilizador' => 'Admin', 
        ':assunto'    => $input['assunto'] ?? 'Sem Assunto'
    ]);

    echo json_encode(["status" => "sucesso", "mensagem" => "Email registado!"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
}