

<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="home.php">
                <i class="fas fa-code me-2"></i>JFHK
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="projeto.php">
                            <i class="fas fa-project-diagram me-1"></i>Projetos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="perfil.php">
                            <i class="fas fa-user me-1"></i>Perfil
                        </a>
                    </li>
                    <?php if ($tabela == 'empresa') { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="membro_empresa.php">
                            <i class="fas fa-users me-1"></i>Membros
                        </a>
                    </li>
                    <?php } ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-chart-bar me-1"></i>Relatórios
                        </a>
                    </li>
                </ul>
            </div>
            <div class="navbar-nav">
                <a href="sair.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt me-1"></i>Sair
                </a>
            </div>
        </div>
    </nav>
</header>

