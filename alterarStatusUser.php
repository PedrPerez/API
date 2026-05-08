<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

$iduser = $_POST['iduser'] ?? null;
$activo = $_POST['activo'] ?? null; // 0 ou 1

if ($iduser !== null && $activo !== null) {
    try {
        // --- SEGURANÇA: Verificar se é Admin ---
        $stmtCheck = $pdo->prepare("SELECT idcategoria FROM tbl_users WHERE iduser = ?");
        $stmtCheck->execute([$iduser]);
        $user = $stmtCheck->fetch();

        // Se for Admin e estiverem a tentar colocar como Inativo (0)
        if ($user && (int)$user['idcategoria'] === 1 && (int)$activo === 0) {
            echo json_encode(["status" => "erro", "mensagem" => "Segurança: Um Administrador não pode ser desativado."]);
            exit;
        }
        // ---------------------------------------

        $stmt = $pdo->prepare("UPDATE tblUsers SET activo = ? WHERE iduser = ?");
        $stmt->execute([$activo, $iduser]);

        echo json_encode(["status" => "sucesso"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Parâmetros inválidos"]);
}
?>