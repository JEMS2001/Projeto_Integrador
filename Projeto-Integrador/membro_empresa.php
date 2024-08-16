<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
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

function getConnection($servername, $dbname, $username, $password) {
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Erro na conexão: " . $e->getMessage());
    }
}

function getCompanyName($pdo, $id_empresa) {
    $sql_empresa = "SELECT nome FROM empresa WHERE id_empresa = :id_empresa";
    $stmt_empresa = $pdo->prepare($sql_empresa);
    $stmt_empresa->bindParam(':id_empresa', $id_empresa);
    $stmt_empresa->execute();
    return $stmt_empresa->fetch(PDO::FETCH_ASSOC);
}

function checkExistingNotification($pdo, $id_empresa, $cpf_membro) {
    $sql_check_notificacao = "SELECT * FROM notificacao WHERE id_empresa = :id_empresa AND cpf_membro = :cpf_membro";
    $stmt_check_notificacao = $pdo->prepare($sql_check_notificacao);
    $stmt_check_notificacao->bindParam(':id_empresa', $id_empresa);
    $stmt_check_notificacao->bindParam(':cpf_membro', $cpf_membro);
    $stmt_check_notificacao->execute();
    return $stmt_check_notificacao->fetch(PDO::FETCH_ASSOC);
}

function insertNotification($pdo, $id_empresa, $nome_empresa, $cpf_membro) {
    $sql_insert_notificacao = "INSERT INTO notificacao (id_empresa, nome, cpf_membro) VALUES (:id_empresa, :nome, :cpf_membro)";
    $stmt_insert_notificacao = $pdo->prepare($sql_insert_notificacao);
    $stmt_insert_notificacao->bindParam(':id_empresa', $id_empresa);
    $stmt_insert_notificacao->bindParam(':nome', $nome_empresa);
    $stmt_insert_notificacao->bindParam(':cpf_membro', $cpf_membro);
    $stmt_insert_notificacao->execute();
}

function addMemberToCompany($pdo, $cpf_membro, $id_empresa) {
    global $message;
    
    $empresa = getCompanyName($pdo, $id_empresa);
    if (!$empresa) {
        $message = '<div class="alert alert-danger">Empresa não encontrada.</div>';
        return;
    }
    
    $nome_empresa = $empresa['nome'];
    
    $sql_membro = "SELECT id_membro FROM membro WHERE cpf = :cpf";
    $stmt_membro = $pdo->prepare($sql_membro);
    $stmt_membro->bindParam(':cpf', $cpf_membro);
    $stmt_membro->execute();
    $membro = $stmt_membro->fetch(PDO::FETCH_ASSOC);

    if ($membro) {
        $notificacao_existente = checkExistingNotification($pdo, $id_empresa, $cpf_membro);

        if ($notificacao_existente) {
            $message = '<div class="alert alert-warning">Convite já foi feito para este membro.</div>';
        } else {
            insertNotification($pdo, $id_empresa, $nome_empresa, $cpf_membro);
            $message = '<div class="alert alert-success">Notificação adicionada ao membro com sucesso.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">CPF de membro não encontrado.</div>';
    }
}

function getMembers($pdo, $id_empresa) {
    $sql = "SELECT * FROM membro WHERE id_empresa = :id_empresa";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_empresa', $id_empresa);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$pdo = getConnection($servername, $dbname, $username, $password);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cpf_membro'])) {
    $cpf_membro = $_POST['cpf_membro'];
    addMemberToCompany($pdo, $cpf_membro, $id_empresa);
}

$members = getMembers($pdo, $id_empresa);
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
            background-color: var(--secondary-color);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            padding-top: 20px;
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
            display: none;
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
                display: block;
            }
        }
        .member-list {
            max-height: 400px;
            overflow-y: auto;
        }
        .member-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        .fa-user-circle {
            font-size: 40px;
            color: #ccc;
            margin-right: 10px;
        }
    </style>
</head>
<body>

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
            <a href="monitoramento.php">
                <i class="fas fa-chart-bar me-1"></i>Relatórios
            </a>
            <?php }else{ ?>

            <a class="nav-link" href="notificacao.php">
                <i class="fas fa-users me-1"></i>Notificações
            </a>

        <?php } ?>
        <a href="dynamic-full-calendar.html">
            <i class="fas fa-calendar-alt me-1"></i>Calendário
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
                                <li class="list-group-item d-flex align-items-center">
                                    <?php if (!empty($member['imagem'])): ?>
                                        <img src="<?= htmlspecialchars($member['imagem']) ?>" alt="Foto membro" class="member-image">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle member-image"></i>
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($member['nome']); ?> (<?php echo htmlspecialchars($member['cpf']); ?>)</span>
                                </li>
                            <?php endforeach; ?>
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

        checkWindowSize();

        $(window).resize(checkWindowSize);

        $("#sidebar-toggle").click(function(e) {
            e.preventDefault();
            $("#sidebar").toggleClass("active");
            $("#content").toggleClass("active");
        });

        $('#cpf_membro').mask('000.000.000-00');
    });
</script>

</body>
</html>

<?php
$pdo = null;
?>