<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

$iduser = $_POST['iduser'] ?? null;
$idmenu = $_POST['idmenu'] ?? null;
$activo = $_POST['activo'] ?? 0;

if ($iduser && $idmenu) {
    try {
        // 1. Verificar se já existe uma linha para este user e este menu
        $check = $pdo->prepare("SELECT COUNT(*) FROM tblPermissoes WHERE iduser = ? AND idmenu = ?");
        $check->execute([$iduser, $idmenu]);
        $exists = $check->fetchColumn();

        if ($exists) {
            // 2. Se existe, atualiza
            $stmt = $pdo->prepare("UPDATE tblPermissoes SET activo = ? WHERE iduser = ? AND idmenu = ?");
            $stmt->execute([$activo, $iduser, $idmenu]);
        } else {
            // 3. Se não existe, insere
            $stmt = $pdo->prepare("INSERT INTO tblPermissoes (iduser, idmenu, activo) VALUES (?, ?, ?)");
            $stmt->execute([$iduser, $idmenu, $activo]);
        }

        echo json_encode(["status" => "sucesso"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Dados incompletos"]);
}
?>