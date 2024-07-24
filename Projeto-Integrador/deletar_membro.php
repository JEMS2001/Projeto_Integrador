<?php
session_start();
include_once('config.php');

if (!isset($_GET['id'])) {
    header("Location: home.php");
    exit;
}

$id_membro = $_GET['id'];

$sql = "DELETE FROM membro WHERE id_membro = :id_membro";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id_membro', $id_membro);

if ($stmt->execute()) {
    header("Location: home.php");
    exit();
} else {
    $mensagem = "Erro ao deletar membro: " . $stmt->errorInfo()[2];
}
?>

<?php include 'layout/header.php'; ?>

<div class="container mt-4">
    <div class="alert alert-danger" role="alert">
        <?php echo $mensagem; ?>
    </div>
</div>

<?php include 'layout/footer.php'; ?>