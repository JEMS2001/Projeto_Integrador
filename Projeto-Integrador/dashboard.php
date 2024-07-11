<?php
session_start();
    
    if((!isset($_SESSION['email']) == true) and (!isset($_SESSION['senha']) == true))
    {
        unset($_SESSION['email']);
        unset($_SESSION['senha']);
        header('Location: login.php');
        exit;
    }
    $logado=$_SESSION['email'];
    $tabela=$_SESSION['tipo_usuario'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="estilo.css">
    <title>Document</title>
</head>
<body>
    
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="home.php">JFHK</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="projeto.php">Projetos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="perfil.php">Perfil</a>
                    </li>
                    <?php if ($tabela == 'empresa') { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="membro_empresa.php">Membros</a>
                    </li>
                    <?php } ?>
                    <li class="nav-item">
                        <a href="sair.php" class="btn btn-danger">Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>



    
</body>
</html>

