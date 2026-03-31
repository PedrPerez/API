<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

require_once 'config.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $user = $input['utilizador'] ?? '';
    $pass = $input['password'] ?? '';

    if (empty($user) || empty($pass)) {
        echo json_encode(["status" => "erro", "mensagem" => "Campos obrigatórios."]);
        exit;
    }

    $sql = "SELECT iduser, username, password, nome FROM tbl_users 
            WHERE username = :user AND activo = 1 AND idcategoria = 1 LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user' => $user]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData && $pass === $userData['password']) {
        echo json_encode([
            "status" => "sucesso",
            "user" => [
                "id" => $userData['iduser'],
                "nome" => $userData['nome'],
                "username" => $userData['username']
            ]
        ]);
    } else {
        echo json_encode(["status" => "erro", "mensagem" => "Credenciais inválidas ou conta inativa."]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "erro", "mensagem" => "Erro no servidor."]);
}
?>