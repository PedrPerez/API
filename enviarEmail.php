<?php
// 1. Configurações de Cabeçalho (CORS e JSON)
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Responde ao preflight do browser
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 2. Importar o Autoload do Composer
// De acordo com a tua imagem, o enviarEmail.php está na mesma pasta que a pasta 'vendor'
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Inicia buffer para evitar que lixo quebre o JSON
ob_start();

try {
    $input = json_decode(file_get_contents('php://input'), true);

    // Debug: Se o email estiver vazio, vamos avisar exatamente o que foi recebido
    if (empty($input['email'])) {
        ob_clean();
        echo json_encode([
            "status" => "erro", 
            "mensagem" => "O PHP não recebeu o endereço de email. Dados recebidos: " . json_encode($input)
        ]);
        exit;
    }

    $destinatario = trim($input['email']);
    $mensagemCorpo = $input['mensagem'];

    $mail = new PHPMailer(true);
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
    $mail->addAddress($destinatario);

    // 6. Conteúdo
    $mail->isHTML(true);
    $mail->Subject = 'Notificacao de Processo - Hospital de Esposende';
    // nl2br converte as quebras de linha do React (\n) em <br> para o email HTML
    $mail->Body    = nl2br($input['mensagem']);

    $mail->send();
    
    ob_clean();
    echo json_encode([
        "status" => "sucesso",
        "mensagem" => "E-mail enviado com sucesso!"
    ]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Erro ao enviar: " . $mail->ErrorInfo
    ]);
}

ob_end_flush();
exit;