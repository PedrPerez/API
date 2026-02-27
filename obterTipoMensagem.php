<?php
// Permite que o React (localhost:3000) aceda a este script
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config.php';

try {
    // 1. Query para selecionar os tipos de serviço
    // Nota: Usamos 'id' e 'descricao' conforme a estrutura da imagem 032
    $sql = "SELECT id, descricao FROM tbl_tipo_menssagem WHERE activo = '1' ORDER BY descricao";
    
    $stmt = $pdo->query($sql);
    
    // 2. Buscar todos os resultados como um array associativo
    $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Retornar os dados em formato JSON
    echo json_encode($tipos);

} catch (Exception $e) {
    // Caso haja erro, retorna o código 500 e a mensagem de erro
    http_response_code(500);
    echo json_encode([
        "erro" => "Erro ao obter tipos de serviço",
        "mensagem" => $e->getMessage()
    ]);
}
?>