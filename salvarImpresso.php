<?php
// Permite que o React (localhost:3000) aceda a este script
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Lida com a requisição de pré-verificação do browser (CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config.php';

try {
    // 1. Verificar se os dados foram enviados
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método de requisição inválido.");
    }

    // 2. Capturar dados (suporta tanto FormData como JSON)
    $input = $_POST;
    if (empty($input)) {
        $input = json_decode(file_get_contents('php://input'), true);
    }

    // 3. Preparar as variáveis para a Query (mapeando os nomes do React para a DB)
    $data           = !empty($input['data']) ? $input['data'] : date('Y-m-d');
    $cod_servico    = $input['unidade'] ?? null;  // Vem do select "unidade"
    $cod_tipo       = $input['tipo'] ?? null;     // Vem do select "tipo"
    $nome           = $input['nome'] ?? '';
    $morada         = $input['morada'] ?? '';
    $telefone       = $input['tel'] ?? '';        // Mapeado de "tel" no React
    $telemovel      = $input['tel'] ?? '';        // Usando o mesmo campo para ambos se necessário
    $email          = $input['email'] ?? '';
    $descritivo     = $input['descritivo'] ?? '';
    $resolucao      = $input['resolucao'] ?? '';
    $utilizador     = $input['utilizador_registo'] ?? 'WebUser';
    $data_registo   = date('Y-m-d H:i:s');

    // 4. Query SQL (id_impresso é auto-incremento, não se inclui)
    $sql = "INSERT INTO tbl_registos_impressos (
                data, cod_servico, cod_tipo, nome, morada, 
                telefone, telemovel, email, descritivo, 
                resolucao, utilizador_registo, data_registo
            ) VALUES (
                :data, :cod_servico, :cod_tipo, :nome, :morada, 
                :telefone, :telemovel, :email, :descritivo, 
                :resolucao, :utilizador, :data_registo
            )";

    $stmt = $pdo->prepare($sql);

    // 5. Executar a inserção
    $stmt->execute([
        ':data'         => $data,
        ':cod_servico'  => $cod_servico,
        ':cod_tipo'     => $cod_tipo,
        ':nome'         => $nome,
        ':morada'       => $morada,
        ':telefone'     => $telefone,
        ':telemovel'    => $telemovel,
        ':email'        => $email,
        ':descritivo'   => $descritivo,
        ':resolucao'    => $resolucao,
        ':utilizador'   => $utilizador,
        ':data_registo' => $data_registo
    ]);

    // 6. Resposta de Sucesso
    echo json_encode([
        "status" => "sucesso",
        "mensagem" => "Registo guardado com sucesso!",
        "id" => $pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    // Resposta de Erro
    http_response_code(500);
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Erro ao processar: " . $e->getMessage()
    ]);
}
?>