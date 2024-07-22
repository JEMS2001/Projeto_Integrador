<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_tarefa'])) {
        $stmt = $pdo->prepare("DELETE FROM tarefa WHERE id_tarefa = :id_tarefa");
        $stmt->execute([':id_tarefa' => $_POST['id_tarefa']]);
        echo 'success';
    }
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        // Erro de violação de chave estrangeira
        echo 'error: Chave estrangeira violada. Não é possível excluir esta tarefa.';
    } else {
        echo 'error: ' . $e->getMessage();
    }
}
?>
