<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

$logado = $_SESSION['email'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$tabela = $_SESSION['tipo_usuario'];

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_task'])) {
        $stmt = $pdo->prepare("
            UPDATE tarefa 
            SET nome = :nome, descricao = :descricao, status = :status, id_membro = :id_membro 
            WHERE id_tarefa = :id_tarefa
        ");
        $stmt->execute([
            ':nome' => $_POST['nome_tarefa'],
            ':descricao' => $_POST['descricao'],
            ':status' => $_POST['status'],
            ':id_membro' => $_POST['id_membro'],
            ':id_tarefa' => $_POST['id_tarefa']
        ]);

        header("Location: tarefas_projeto.php?id_projeto=" . $_POST['id_projeto']);
        exit;
    }
} catch(Exception $e) {
    die("Erro: " . $e->getMessage());
}
?>
