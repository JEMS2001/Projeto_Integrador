<?php
session_start();

if(isset($_SESSION['email'])) {
    
    header("Location: dashboard.php");
    exit; 
}
?>

<?php include 'header.php'; ?>

<div class="content">
    <div class="container my-5">
        <div class="text-center">
            <h1>Bem-vindo à JFHK Platform</h1>
            <p class="lead">Gerencie, organize e otimize a administração de sua equipe de forma rápida e eficiente com nossa plataforma de gerenciamento de funcionários intuitiva e poderosa.</p>
            <hr>
        </div>

        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title">Gerenciamento de Membros</h3>
                        <p class="card-text">Cadastre e gerencie membros de sua equipe de forma fácil e organizada.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title">Gerenciamento de Empresas</h3>
                        <p class="card-text">Registre e gerencie informações de empresas parceiras de maneira eficiente.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title">Relatórios e Estatísticas</h3>
                        <p class="card-text">Visualize relatórios detalhados e estatísticas sobre sua equipe e operações.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>