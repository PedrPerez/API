<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

// Recebe os dados
$iduser = $_POST['iduser'] ?? null;
$idmenu = $_POST['idmenu'] ?? null;
$activo = $_POST['activo'] ?? 0;

if ($iduser && $idmenu) {
    try {
        // --- SEGURANÇA: Verificar se o alvo é Admin ---
        $stmtCheck = $pdo->prepare("SELECT idcategoria FROM tbl_users WHERE iduser = ?");
        $stmtCheck->execute([$iduser]);
        $user = $stmtCheck->fetch();

        if ($user && (int)$user['idcategoria'] === 1) {
            echo json_encode(["status" => "erro", "mensagem" => "Proibido: Não pode alterar permissões de um Administrador."]);
            exit; // Interrompe a execução aqui
        }
        // ----------------------------------------------

        // Se não for Admin, procede com a gravação
        $sql = "INSERT INTO tblPermissoes (iduser, idmenu, activo) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE activo = VALUES(activo)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$iduser, $idmenu, $activo]);

        echo json_encode(["status" => "sucesso"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Dados incompletos"]);
}
?>