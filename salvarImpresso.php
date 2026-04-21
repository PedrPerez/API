<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// IMPORTANTE: Certifique-se de ter o PHPMailer instalado via Composer
// Se não tiver, use: composer require phpmailer/phpmailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'config.php';
require 'vendor/autoload.php'; // Caminho para o autoload do Composer

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método de requisição inválido.");
    }

    $input = $_POST;
    if (empty($input)) {
        $input = json_decode(file_get_contents('php://input'), true);
    }

    // Preparar variáveis
    $data           = !empty($input['data']) ? $input['data'] : date('Y-m-d');
    $cod_servico    = $input['unidade'] ?? null;
    $cod_tipo       = $input['tipo'] ?? null;
    $nome           = $input['nome'] ?? '';
    $morada         = $input['morada'] ?? '';
    $telefone       = $input['tel'] ?? '';
    $telemovel      = $input['tel'] ?? '';
    $email          = $input['email'] ?? '';
    $descritivo     = $input['descritivo'] ?? '';
    $resolucao      = $input['resolucao'] ?? '';
    $utilizador     = $input['utilizador_registo'] ?? 'WebUser';
    $data_registo   = date('Y-m-d H:i:s');

    // Query SQL
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

    $novoID = $pdo->lastInsertId();

    // --- LÓGICA DE ENVIO DE EMAIL ---
    $emailEnviado = false;
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mail = new PHPMailer(true);

        try {
            // Configurações do Servidor SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'pikachodad@gmail.com';
            $mail->Password   = 'tolf dlxn njbh wigp';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // Remetente e Destinatário
            $mail->setFrom('pikachodad@gmail.com', 'Hospital de Esposende');
            $mail->addAddress($email, $nome);

            // Conteúdo do Email
            $mail->isHTML(true);
            $mail->Subject = 'Confirmação de Registo - Hospital de Esposende';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h2>Olá, $nome.</h2>
                    <p>Confirmamos que o seu impresso foi registado com sucesso no nosso sistema.</p>
                    <p><b>Detalhes do Registo:</b></p>
                    <ul>
                        <li><b>ID:</b> #$novoID</li>
                        <li><b>Data:</b> $data</li>
                        <li><b>Assunto:</b> $descritivo</li>
                    </ul>
                    <p>Iremos analisar a sua submissão com a maior brevidade possível.</p>
                    <hr>
                    <small>Este é um e-mail automático, por favor não responda.</small>
                </div>
            ";

            $mail->send();
            $emailEnviado = true;
        } catch (Exception $e) {
            $erroEmail = $mail->ErrorInfo;
        }
    }

    echo json_encode([
        "status" => "sucesso",
        "mensagem" => $emailEnviado ? "Registo guardado e e-mail enviado!" : "Registo guardado (e-mail não enviado).",
        "id" => $novoID
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Erro ao processar: " . $e->getMessage()
    ]);
}
?>