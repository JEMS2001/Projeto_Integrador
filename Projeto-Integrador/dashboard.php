<?php
session_start();

if((!isset($_SESSION['email']) == true) and (!isset($_SESSION['senha']) == true)) {
    unset($_SESSION['email']);
    unset($_SESSION['senha']);
    header('Location: login.php');
    exit;
}
$logado = $_SESSION['email'];
$tabela = $_SESSION['tipo_usuario'];

// Dados de conexão com o banco de dados
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "projeto";

// Cria a conexão
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Conexão falhou: " . $e->getMessage());
}

// Consulta para obter dados específicos de uma empresa
$empresa_id = $_SESSION['id_empresa'] ;// ID da empresa específica
$empresa = $pdo->query("SELECT * FROM empresa WHERE id_empresa = $empresa_id")->fetch(PDO::FETCH_ASSOC);
$membros = $pdo->query("SELECT * FROM membro WHERE id_empresa = $empresa_id")->fetchAll(PDO::FETCH_ASSOC);
$projetos = $pdo->query("SELECT * FROM projeto WHERE id_empresa = $empresa_id")->fetchAll(PDO::FETCH_ASSOC);
$tarefas = $pdo->query("SELECT * FROM tarefa WHERE id_projeto IN (SELECT id_projeto FROM projeto WHERE id_empresa = $empresa_id)")->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="estilo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <title>Dashboard</title>
    <style>

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            background-color: var(--primary-color);
            color: var(--text-color);
            margin-bottom: 20px;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .chart-container {
            position: relative;
            margin: auto;
            height: 400px;
            width: 100%;
        }
        .content {
            flex: 1;
            padding: 20px;
            margin-left: 0;
            transition: margin-left 0.3s;
        }
        .title {
            font-size: 2rem;
            font-weight: bold;
            color: var(--accent-color);
        }
        .subtitle {
            font-size: 1.2rem;
            color: var(--secondary-color);
        }
        .sidebar {
            width: 250px;
            background: var(--primary-color);
            color: var(--text-color);
            position: fixed;
            height: 100%;
            padding-top: 60px;
            left: -250px;
            transition: left 0.3s;
            z-index: 1000;
        }
        .sidebar.active {
            left: 0;
        }
        .sidebar a {
            color: var(--text-color);
            text-decoration: none;
            padding: 15px;
            display: block;
            transition: 0.3s;
        }
        .sidebar a:hover {
            background: var(--secondary-color);
        }
        .navbar-brand {
            color: var(--accent-color) !important;
        }
        #sidebarCollapse {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
        }
        @media (min-width: 768px) {
            .sidebar {
                left: 0;
            }
            .content {
                margin-left: 250px;
            }
            #sidebarCollapse {
                display: none;
            }
        }
        @media (max-width: 767px) {
            .card {
                margin-bottom: 15px;
            }
            .chart-container {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <button id="sidebarCollapse">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar">
        <a class="navbar-brand d-flex align-items-center justify-content-center" href="home.php">
            <i class="fas fa-code me-2"></i>JFHK
        </a>
        <a href="projeto.php">
            <i class="fas fa-project-diagram me-1"></i>Projetos
        </a>
        <a href="perfil.php">
            <i class="fas fa-user me-1"></i>Perfil
        </a>
        <?php if ($tabela == 'empresa') { ?>
        <a href="membro_empresa.php">
            <i class="fas fa-users me-1"></i>Membros
        </a>
        <?php } ?>
        <a href="#">
            <i class="fas fa-chart-bar me-1"></i>Relatórios
        </a>
        <a href="sair.php" class="btn btn-danger mt-auto">
            <i class="fas fa-sign-out-alt me-1"></i>Sair
        </a>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="card animate__animated animate__bounceInLeft">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-building"></i> Empresa</h5>
                            <p class="card-text display-4"><?php echo $empresa['nome']; ?></p>
                            <p class="card-text"><?php echo $empresa['email']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card animate__animated animate__bounceInRight">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-users"></i> Membros</h5>
                            <p class="card-text display-4"><?php echo count($membros); ?></p>
                            <a href="#" class="btn btn-primary">Ver mais</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card animate__animated animate__bounceInLeft">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-project-diagram"></i> Projetos</h5>
                            <p class="card-text display-4"><?php echo count($projetos); ?></p>
                            <a href="#" class="btn btn-primary">Ver mais</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card animate__animated animate__bounceInRight">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-tasks"></i> Tarefas</h5>
                            <p class="card-text display-4"><?php echo count($tarefas); ?></p>
                            <a href="#" class="btn btn-primary">Ver mais</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="chart-container mt-5">
                <canvas id="myChart"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Membros', 'Projetos', 'Tarefas'],
                datasets: [{
                    label: 'Total',
                    data: [<?php echo count($membros); ?>, <?php echo count($projetos); ?>, <?php echo count($tarefas); ?>],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        document.getElementById('sidebarCollapse').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.content').classList.toggle('active');
        });
    </script>
</body>
</html>
