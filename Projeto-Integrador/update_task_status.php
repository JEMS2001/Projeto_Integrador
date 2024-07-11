<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    exit('Não autorizado');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_tarefa']) && isset($_POST['status'])) {
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("UPDATE tarefa SET status = :status WHERE id_tarefa = :id_tarefa");
        $stmt->execute([
            ':status' => $_POST['status'],
            ':id_tarefa' => $_POST['id_tarefa']
        ]);

        echo "Sucesso";
    } catch(PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}
?>