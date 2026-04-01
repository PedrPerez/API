<?php
// Configurações de CORS para o React conseguir aceder
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Importa a tua ligação existente (não alteres o config.php)
require_once 'config.php';

try {
    // 1. Prepara a query baseada na tua tabela tbl_users
    $stmt = $pdo->prepare("SELECT iduser, username, nome, idcategoria, activo, pin FROM tbl_users");
    
    // 2. Executa a query
    $stmt->execute();
    
    // 3. Obtém todos os resultados
    $utilizadores = $stmt->fetchAll();
    
    // 4. Retorna os dados em formato JSON para o React
    echo json_encode($utilizadores);

} catch (PDOException $e) {
    // Em caso de erro na query, envia o erro em formato JSON
    http_response_code(500);
    echo json_encode([
        "erro" => "Erro ao obter utilizadores",
        "mensagem" => $e->getMessage()
    ]);
}
?>