<?php
require 'config.php';

$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM tbl_questionarios WHERE id_questionario = ?");
$stmt->execute([$id]);

header("Location: index.php");
?>