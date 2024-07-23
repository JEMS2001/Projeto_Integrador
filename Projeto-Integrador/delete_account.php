<?php

session_start();
include_once('config.php');

if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'];
$tabela = $data['tabela'];

if ($email !== $_SESSION['email']) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check for associated projects
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM projetos WHERE email_usuario = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo json_encode(['success' => false, 'message' => 'Não é possível excluir a conta pois existem projetos associados.']);
        exit;
    }

    // Delete the account
    $stmt = $pdo->prepare("DELETE FROM $tabela WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        session_destroy();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nenhuma conta foi excluída']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>