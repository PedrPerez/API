<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'config.php';

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->iduser)) {
    echo json_encode(["status" => "erro", "mensagem" => "Dados inválidos."]);
    exit;
}

try {
    // Se a password não estiver vazia, atualizamos com hash. Caso contrário, mantemos a antiga.
    if (!empty($data->password)) {
        $passwordHash = password_hash($data->password, PASSWORD_DEFAULT);
        $sql = "UPDATE tbl_users SET nome = :nome, idcategoria = :idcategoria, password = :password WHERE iduser = :iduser";
        $params = [
            ':nome' => $data->nome,
            ':idcategoria' => $data->idcategoria,
            ':password' => $passwordHash,
            ':iduser' => $data->iduser
        ];
    } else {
        $sql = "UPDATE tbl_users SET nome = :nome, idcategoria = :idcategoria WHERE iduser = :iduser";
        $params = [
            ':nome' => $data->nome,
            ':idcategoria' => $data->idcategoria,
            ':iduser' => $data->iduser
        ];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(["status" => "sucesso", "mensagem" => "Utilizador atualizado!"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
}
?>