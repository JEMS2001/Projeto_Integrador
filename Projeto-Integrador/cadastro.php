<?php
session_start();
include('config.php');
if(isset($_SESSION['email'])) {
    
    header("Location: dashboard.php");
    exit; 
}
?>

<?php include 'layout/header.php'; ?>

<link href="layout/css/cadastro.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

<div class="container my-5">
    <h1 class="text-center mb-5 animate-fade-in">Escolha o tipo de cadastro</h1>
    <div class="row justify-content-center align-items-center">
        <div class="col-md-6 mb-4 animate-fade-in" style="animation-delay: 0.4s;">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-building card-icon mb-3"></i>
                    <h5 class="card-title">Cadastro de Empresa</h5>
                    <p class="card-text">Registre sua empresa e expanda seus negócios.</p>
                    <a href="cadastro_empresa.php" class="btn btn-custom btn-lg mt-3">
                        Cadastrar Empresa
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4 animate-fade-in order-md-1" style="animation-delay: 0.8s;">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user card-icon mb-3"></i>
                    <h5 class="card-title">Cadastro de Membro</h5>
                    <p class="card-text">Junte-se à nossa comunidade como membro individual.</p>
                    <a href="cadastro_membro.php" class="btn btn-custom btn-lg mt-3">
                        Cadastrar Membro
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center align-items-center mt-5">
    
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.03)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    });
</script>
<?php include 'layout/footer.php'; ?>
