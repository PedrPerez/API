<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["erro" => "ID não fornecido"]);
    exit;
}

try {
    // 1. Buscar sugestões
    $stmtS = $pdo->prepare("SELECT sugestoes_comentarios FROM tbl_questionarios WHERE id_questionario = ?");
    $stmtS->execute([$id]);
    $sugestoes = $stmtS->fetchColumn();

    // 2. Buscar respostas e descrições das perguntas
    $sql = "SELECT r.*, q.descricao 
            FROM tbl_questionarios_registos r
            JOIN tbl_questoes q ON r.id_indicador = q.id
            WHERE r.id_questionario = ?";
    
    $stmtR = $pdo->prepare($sql);
    $stmtR->execute([$id]);
    $respostas = $stmtR->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "sugestoes" => $sugestoes,
        "respostas" => $respostas
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erro" => $e->getMessage()]);
}