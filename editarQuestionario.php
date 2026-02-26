<?php
require 'config.php';

$id = $_GET['id'];

if ($_POST) {
    $sql = "UPDATE tbl_questionarios
            SET cod_unidade = ?, 
                data = ?, 
                sugestoes_comentarios = ?, 
                utilizador_registo = ?
            WHERE id_questionario = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $_POST['cod_unidade'],
        $_POST['data'],
        $_POST['sugestoes'],
        $_POST['utilizador'],
        $id
    ]);

    header("Location: index.php");
}

$stmt = $conn->prepare("SELECT * FROM tbl_questionarios WHERE id_questionario = ?");
$stmt->execute([$id]);
$q = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<h2>Editar Questionário</h2>

<form method="POST">
    Unidade: <input type="number" name="cod_unidade" value="<?= $q['cod_unidade'] ?>"><br><br>
    Data: <input type="datetime-local" name="data" value="<?= date('Y-m-d\TH:i', strtotime($q['data'])) ?>"><br><br>
    Sugestões: <textarea name="sugestoes"><?= $q['sugestoes_comentarios'] ?></textarea><br><br>
    Utilizador: <input type="text" name="utilizador" value="<?= $q['utilizador_registo'] ?>"><br><br>

    <button type="submit">Atualizar</button>
</form>
?>