<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/x-www-form-urlencoded");
require_once 'config.php';

$id = $_POST['id'] ?? null;
$activo = $_POST['activo'] ?? null;

if ($id !== null && $activo !== null) {
    try {
        $stmt = $pdo->prepare("UPDATE tbl_questoes SET activo = ? WHERE id = ?");
        $stmt->execute([$activo, $id]);
        echo json_encode(["status" => "sucesso"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Parametros invalidos"]);
}
?>