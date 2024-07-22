<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

$tabela = $_SESSION['tipo_usuario'];
$logado = $_SESSION['email'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$id_empresa = null;
$id_membro = null;

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($tipo_usuario == 'empresa') {
        // Buscar id_empresa
        $stmt = $pdo->prepare("SELECT id_empresa FROM empresa WHERE email = :email");
        $stmt->execute([':email' => $logado]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_empresa = $result['id_empresa'];

        // Adicionar projeto (apenas para empresas)
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_project'])) {
            $stmt = $pdo->prepare("INSERT INTO projeto (nome, tipo, data_inicio, data_fim, id_empresa) VALUES (:nome, :tipo, :data_inicio, :data_fim, :id_empresa)");
            $stmt->execute([
                ':nome' => $_POST['nome_projeto'],
                ':tipo' => $_POST['tipo_projeto'],
                ':data_inicio' => $_POST['data_inicio'],
                ':data_fim' => $_POST['data_fim'],
                ':id_empresa' => $id_empresa
            ]);
        }

        // Deletar projeto (apenas para empresas)
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_project'])) {
            $stmt = $pdo->prepare("DELETE FROM projeto WHERE id_projeto = :id_projeto AND id_empresa = :id_empresa");
            $stmt->execute([
                ':id_projeto' => $_POST['projeto_id'],
                ':id_empresa' => $id_empresa
            ]);
        }
    } else {
        // Buscar id_membro
        $stmt = $pdo->prepare("SELECT id_membro FROM membro WHERE email = :email");
        $stmt->execute([':email' => $logado]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_membro = $result['id_membro'];

        // Buscar projetos associados ao membro
        $stmt = $pdo->prepare("
            SELECT p.id_projeto, p.nome, p.tipo, p.data_inicio, p.data_fim 
            FROM projeto p
            INNER JOIN membro_projeto mp ON p.id_projeto = mp.id_projeto
            WHERE mp.id_membro = :id_membro
        ");
        $stmt->execute([':id_membro' => $id_membro]);
        $projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="estilo.css">
    <title>Projetos</title>
    <style>
        .sidebar {
            width: 250px;
            background: var(--primary-color);
            color: var(--text-color);
            position: fixed;
            height: 100%;
            padding-top: 60px;
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
            padding: 20px;
            flex: 1;
        }

        .card {
            margin-bottom: 20px;
            border-radius: 15px;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .btn-primary,
        .btn-success,
        .btn-danger {
            transition: background-color 0.3s, transform 0.3s;
        }

        .btn-primary:hover,
        .btn-success:hover,
        .btn-danger:hover {
            transform: scale(1.1);
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
        <?php } ?>
        <a href="#">
            <i class="fas fa-chart-bar me-1"></i>Relatórios
        </a>
        <a href="sair.php" class="btn btn-danger mt-auto">
            <i class="fas fa-sign-out-alt me-1"></i>Sair
        </a>
    </div>

    <div class="content">
        <div class="container">
            <h2 class="my-4"><i class="fas fa-project-diagram"></i> Projetos</h2>

            <?php if ($tipo_usuario == 'empresa') { ?>
                <!-- Botão para abrir o modal de adicionar projeto -->
                <button class="btn btn-success mb-4" data-toggle="modal" data-target="#addProjectModal"><i class="fas fa-plus"></i> Adicionar Projeto</button>

                <!-- Modal para adicionar projeto -->
                <div class="modal fade" id="addProjectModal" tabindex="-1" role="dialog" aria-labelledby="addProjectModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addProjectModalLabel">Adicionar Projeto</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" action="projeto.php">
                                    <div class="form-group">
                                        <label for="nome_projeto">Nome do Projeto</label>
                                        <input type="text" class="form-control" id="nome_projeto" name="nome_projeto" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="tipo_projeto">Tipo do Projeto</label>
                                        <input type="text" class="form-control" id="tipo_projeto" name="tipo_projeto" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="data_inicio">Data de Início</label>
                                        <input type="date" class="form-control" id="data_inicio" name="data_inicio" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="data_fim">Data de Término</label>
                                        <input type="date" class="form-control" id="data_fim" name="data_fim" required>
                                    </div>
                                    <button type="submit" name="add_project" class="btn btn-success btn-block">Adicionar Projeto</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>


            <?php } ?>

            <!-- Cards de projetos -->
            <div class="row">
                <?php

                $stmt = $pdo->prepare("SELECT id_projeto, nome, tipo, data_inicio, data_fim FROM projeto WHERE id_empresa = :id_empresa");
                $stmt->execute([':id_empresa' => $id_empresa]);
                $projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($projetos as $projeto) { ?>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($projeto['nome']); ?></h5>
                                <p class="card-text">Tipo: <?php echo htmlspecialchars($projeto['tipo']); ?></p>
                                <p class="card-text">Início: <?php echo $projeto['data_inicio']; ?></p>
                                <p class="card-text">Término: <?php echo $projeto['data_fim']; ?></p>
                                <a href="tarefas_projeto.php?id_projeto=<?php echo $projeto['id_projeto']; ?>" class="btn btn-info">Ver Tarefas</a>
                                <?php if ($tipo_usuario == 'empresa') { ?>
                                    <form method="POST" action="projeto.php" class="d-inline">
                                        <input type="hidden" name="projeto_id" value="<?php echo $projeto['id_projeto']; ?>">
                                        <button type="submit" name="delete_project" class="btn btn-danger">Deletar</button>
                                    </form>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>