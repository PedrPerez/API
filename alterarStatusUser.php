<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'config.php';

$iduser = $_POST['iduser'] ?? null;
$novoStatus = isset($_POST['activo']) ? intval($_POST['activo']) : null;

if ($iduser === null || $novoStatus === null) {
    echo json_encode(["status" => "erro", "mensagem" => "Dados insuficientes."]);
    exit;
}

try {
    $sql = "UPDATE tbl_users SET activo = :activo WHERE iduser = :iduser";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':activo' => $novoStatus,
        ':iduser' => $iduser
    ]);

    echo json_encode(["status" => "sucesso", "novoStatus" => $novoStatus]);

} catch (PDOException $e) {
    echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
}
?>