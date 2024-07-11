<?php
session_start();

if(isset($_SESSION['email'])) {
    
    header("Location: dashboard.php");
    exit; 
}
?>

<?php include 'header.php'; ?>

<div class="container my-5 text-center">
    <div class="mb-3">
        <a href="cadastro_membro.php" class="btn btn-primary btn-lg">Cadastrar Membro</a>
    </div>
    <div class="mb-3">
        <a href="cadastro_empresa.php" class="btn btn-primary btn-lg">Cadastrar Empresa</a>
    </div>
</div>

<?php include 'footer.php'; ?>
