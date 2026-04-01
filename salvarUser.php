<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'config.php';

// Pegar nos dados enviados via URLSearchParams ou JSON
$username   = $_POST['username'] ?? null;
$password   = $_POST['password'] ?? null;
$nome       = $_POST['nome'] ?? null;
$idcategoria = $_POST['idcategoria'] ?? null;
$pin        = $_POST['pin'] ?? null;
$activo     = $_POST['activo'] ?? 1;

if (!$username || !$password || !$nome) {
    echo json_encode(["status" => "erro", "mensagem" => "Campos obrigatórios em falta."]);
    exit;
}

try {
    $sql = "INSERT INTO tbl_users (username, password, nome, idcategoria, activo, pin) 
            VALUES (:username, :password, :nome, :idcategoria, :activo, :pin)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':username'    => $username,
        ':password'    => $password, // Nota: Idealmente usarias password_hash
        ':nome'        => $nome,
        ':idcategoria' => $idcategoria,
        ':activo'      => $activo,
        ':pin'         => $pin
    ]);

    echo json_encode(["status" => "sucesso", "mensagem" => "Utilizador criado com sucesso!"]);

} catch (PDOException $e) {
    echo json_encode(["status" => "erro", "mensagem" => "Erro na BD: " . $e->getMessage()]);
}
?>