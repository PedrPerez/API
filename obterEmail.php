<?php
// Permite o acesso do React (CORS)
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config.php';

try {
    $sql = "SELECT 
                e.id_email, 
                e.data, 
                e.nome, 
                e.email, 
                e.conteudo, 
                e.assunto, 
                e.utilizador_registo,
                t.descricao as tipo_descricao,
                e.cod_tipo
            FROM tbl_registos_emails e
            LEFT JOIN tbl_tipo_servicos t ON e.cod_tipo = t.id
            ORDER BY e.id_email DESC";

    $stmt = $pdo->query($sql);
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($emails);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Erro ao carregar lista de emails: " . $e->getMessage()
    ]);
}
?>