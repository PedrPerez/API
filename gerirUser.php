<?php
require_once 'config.php';

// Recebe os dados enviados pelo React (JSON)
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(["status" => "erro", "mensagem" => "Dados não recebidos"]);
    exit;
}

try {
    // Se vier com ID, é para EDITAR. Se não vier, é para ADICIONAR.
    if (isset($input['iduser']) && !empty($input['iduser'])) {
        // UPDATE
        $sql = "UPDATE users SET username=?, nome=?, idcategoria=?, activo=?, pin=? WHERE iduser=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $input['username'],
            $input['nome'],
            $input['idcategoria'],
            $input['activo'],
            $input['pin'] ?? null,
            $input['iduser']
        ]);
        echo json_encode(["status" => "sucesso", "mensagem" => "Utilizador atualizado!"]);
    } else {
        // INSERT (Adicionar Novo)
        $sql = "INSERT INTO users (username, password, nome, idcategoria, activo, pin) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $input['username'],
            $input['password'], // Em produção usa password_hash()
            $input['nome'],
            $input['idcategoria'],
            1, // Activo por padrão
            $input['pin'] ?? null
        ]);
        echo json_encode(["status" => "sucesso", "mensagem" => "Utilizador criado!"]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
}