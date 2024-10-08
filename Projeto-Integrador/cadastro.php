<?php
session_start();
include('config.php');
if(isset($_SESSION['email'])) {
    
    header("Location: dashboard.php");
    exit; 
}
?>

<?php include 'layout/header.php'; ?>


<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<style>
.btn-custom {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
    transition: all 0.3s ease;
}
.btn-custom:hover {
    background-color: #0056b3;
    border-color: #0056b3;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: all 0.3s ease;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}
.card-icon {
    font-size: 4rem;
    color: #007bff;
}
.side-image {
    height: 100%;
    object-fit: cover;
    border-radius: 15px;
}
.animate-fade-in {
    animation: fadeIn 1s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Estilo específico para dispositivos móveis */
@media (max-width: 768px) {
    h1.text-center.mb-5 {
        margin-top: 80px; /* Ajusta o espaço superior do título */
    }
}

/* Estilo específico para dispositivos maiores */
@media (min-width: 769px) {
    h1.text-center.mb-5 {
        margin-top: 100px; /* Ajuste o espaço superior para evitar cobertura pelo cabeçalho */
    }
}
</style>

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
