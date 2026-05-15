<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

$iduser = $_POST['iduser'] ?? null;
$activo = $_POST['activo'] ?? null; 

if ($iduser !== null && $activo !== null) {
    try {
        // 1. Verificar categoria antes de mudar o status
        $stmtCheck = $pdo->prepare("SELECT idcategoria FROM tbl_users WHERE iduser = ?");
        $stmtCheck->execute([$iduser]);
        $user = $stmtCheck->fetch();

        // Bloqueia se for Admin (ID 1) e estiverem a tentar desativar (0)
        if ($user && intval($user['idcategoria']) === 1 && intval($activo) === 0) {
            echo json_encode(["status" => "erro", "mensagem" => "Não pode desativar um Administrador."]);
            exit;
        }

        // 2. Executa a atualização
        $stmt = $pdo->prepare("UPDATE tbl_users SET activo = ? WHERE iduser = ?");
        $stmt->execute([$activo, $iduser]);

        echo json_encode(["status" => "sucesso"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Faltam parametros: iduser ou activo"]);
}