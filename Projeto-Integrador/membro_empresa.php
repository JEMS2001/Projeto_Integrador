<?php
session_start();
include_once('config.php');

if(!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    unset($_SESSION['email']);
    unset($_SESSION['senha']);
    header('Location: login.php');
    exit;
}

$tabela = $_SESSION['tipo_usuario'];
$logado = $_SESSION['email'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$id_empresa = $_SESSION['id_empresa'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cpf_membro'])) {
    $cpf_membro = $_POST['cpf_membro'];

    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT id_membro FROM membro WHERE cpf = :cpf";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':cpf', $cpf_membro);
        $stmt->execute();
        $membro = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($membro) {
            $sql_update = "UPDATE membro SET id_empresa = :id_empresa WHERE id_membro = :id_membro";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->bindParam(':id_empresa', $id_empresa);
            $stmt_update->bindParam(':id_membro', $membro['id_membro']);
            $stmt_update->execute();

            $message = '<div class="alert alert-success">Membro adicionado à empresa com sucesso.</div>';
        } else {
            $message = '<div class="alert alert-danger">CPF de membro não encontrado.</div>';
        }

    } catch(PDOException $e) {
        die("Erro na conexão: " . $e->getMessage());
    }
}

// Fetch existing members
$members = [];
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT * FROM membro WHERE id_empresa = :id_empresa";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_empresa', $id_empresa);
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="estilo.css">
    <title>Gerenciar Membros da Empresa</title>
    <style>
    .sidebar {
        width: 250px;
        background: var(--primary-color);
        color: var(--text-color);
        position: fixed;
        height: 100%;
        padding-top: 60px;
        left: 0;
        transition: 0.3s;
        z-index: 1000;
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
    .content {
        margin-left: 250px;
        transition: 0.3s;
    }
    .card {
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    .card:hover {
        transform: scale(1.05);
    }
    #sidebar-toggle {
        position: fixed;
        left: 10px;
        top: 10px;
        z-index: 1001;
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 10px;
        border-radius: 5px;
        display: none; /* Esconder o botão por padrão */
    }
    @media (max-width: 768px) {
        .sidebar {
            left: -250px;
        }
        .sidebar.active {
            left: 0;
        }
        .content {
            margin-left: 0;
        }
        .content.active {
            margin-left: 250px;
        }
        #sidebar-toggle {
            display: block; /* Mostrar o botão em telas menores */
        }
    }
    .member-list {
        max-height: 400px;
        overflow-y: auto;
    }
</style>

</head>
<body>
    
<div class="sidebar" id="sidebar">
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

<button id="sidebar-toggle" class="btn btn-primary">
    <i class="fas fa-bars"></i>
</button>

<div class="content" id="content">
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-body">
                        <h2 class="card-title text-center"><i class="fas fa-user-plus me-2"></i>Adicionar Membro à Empresa</h2>
                        <form method="POST" action="membro_empresa.php">
                            <div class="form-group">
                                <label for="cpf_membro"><i class="fas fa-id-card me-2"></i>CPF do Membro:</label>
                                <input type="text" class="form-control" id="cpf_membro" name="cpf_membro" placeholder="000.000.000-00" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block mt-3">
                                <i class="fas fa-user-check me-2"></i>Adicionar Membro
                            </button>
                        </form>
                        <?php if ($message): ?>
                            <div class="mt-3">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mb-4">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-body">
                        <h2 class="card-title text-center"><i class="fas fa-users me-2"></i>Membros Atuais</h2>
                        <ul class="list-group member-list">
                            <?php foreach ($members as $member): ?>
                                <li class="list-group-item">
                                    <img src=<?= $member['imagem'] ?> alt="Foto membro">
                                    <?php echo htmlspecialchars($member['nome']); ?> (<?php echo htmlspecialchars($member['cpf']); ?>)
                                </li>
                            <?php
                        
                         endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        // Adicionar a classe active à sidebar por padrão em telas maiores
        function checkWindowSize() {
            if ($(window).width() > 768) {
                $("#sidebar").addClass("active");
                $("#content").addClass("active");
                $("#sidebar-toggle").hide();
            } else {
                $("#sidebar").removeClass("active");
                $("#content").removeClass("active");
                $("#sidebar-toggle").show();
            }
        }

        // Verificar o tamanho da janela ao carregar a página
        checkWindowSize();

        // Verificar o tamanho da janela ao redimensionar a janela
        $(window).resize(checkWindowSize);

        // Alternar a sidebar ao clicar no botão
        $("#sidebar-toggle").click(function(e) {
            e.preventDefault();
            $("#sidebar").toggleClass("active");
            $("#content").toggleClass("active");
        });

        // Aplicar máscara ao campo CPF
        $('#cpf_membro').mask('000.000.000-00');
    });
</script>

</body>
</html>

<?php
$pdo = null;
?>
