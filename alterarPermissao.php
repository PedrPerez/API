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
        // 1. Verificar se é Admin (Segurança que adicionámos antes)
        $stmtCheck = $pdo->prepare("SELECT idcategoria FROM tbl_users WHERE iduser = ?");
        $stmtCheck->execute([$iduser]);
        $user = $stmtCheck->fetch();

        if ($user && (int)$user['idcategoria'] === 1) {
            echo json_encode(["status" => "erro", "mensagem" => "Não pode alterar permissões de um Admin."]);
            exit;
        }

        // 2. O comando MÁGICO: Tenta inserir, se já existir o par (iduser, idmenu), atualiza o 'activo'
        // IMPORTANTE: Para isto funcionar, tens de ter feito o Passo 1 (UNIQUE KEY)
        $sql = "INSERT INTO tblPermissoes (iduser, idmenu, activo) 
                VALUES (:iduser, :idmenu, :activo) 
                ON DUPLICATE KEY UPDATE activo = :activo_update";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':iduser' => $iduser,
            ':idmenu' => $idmenu,
            ':activo' => $activo,
            ':activo_update' => $activo
        ]);

        echo json_encode(["status" => "sucesso", "mensagem" => "Permissão atualizada!"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "erro", "mensagem" => "Erro na BD: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Dados incompletos."]);
}
?>