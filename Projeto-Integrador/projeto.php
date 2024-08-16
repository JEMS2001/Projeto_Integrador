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
            $banner_path = null;
            if (isset($_FILES['banner']) && $_FILES['banner']['error'] == UPLOAD_ERR_OK) {
                $banner_path = 'uploads/' . uniqid() . '_' . basename($_FILES['banner']['name']);
                move_uploaded_file($_FILES['banner']['tmp_name'], $banner_path);
            }
            
            $stmt = $pdo->prepare("INSERT INTO projeto (nome, tipo, data_inicio, data_fim, id_empresa, status, banner_path) VALUES (:nome, :tipo, :data_inicio, :data_fim, :id_empresa, :status, :banner_path)");
            $stmt->execute([
                ':nome' => $_POST['nome_projeto'],
                ':tipo' => $_POST['tipo_projeto'],
                ':data_inicio' => $_POST['data_inicio'],
                ':data_fim' => $_POST['data_fim'],
                ':id_empresa' => $id_empresa,
                ':status' => $_POST['status'],
                ':banner_path' => $banner_path
            ]);
        }

        // Editar projeto (apenas para empresas)
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_project'])) {
            $banner_path = $_POST['current_banner'];
            if (isset($_FILES['banner']) && $_FILES['banner']['error'] == UPLOAD_ERR_OK) {
                $banner_path = 'uploads/' . uniqid() . '_' . basename($_FILES['banner']['name']);
                move_uploaded_file($_FILES['banner']['tmp_name'], $banner_path);
            }

            $stmt = $pdo->prepare("UPDATE projeto SET nome = :nome, tipo = :tipo, data_inicio = :data_inicio, data_fim = :data_fim, status = :status, banner_path = :banner_path WHERE id_projeto = :id_projeto AND id_empresa = :id_empresa");
            $stmt->execute([
                ':nome' => $_POST['nome_projeto'],
                ':tipo' => $_POST['tipo_projeto'],
                ':data_inicio' => $_POST['data_inicio'],
                ':data_fim' => $_POST['data_fim'],
                ':status' => $_POST['status'],
                ':banner_path' => $banner_path,
                ':id_projeto' => $_POST['id_projeto'],
                ':id_empresa' => $id_empresa
            ]);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_project'])) {
            $pdo->beginTransaction();
            try {
                // Deletar progresso das tarefas associadas ao projeto
                $stmt = $pdo->prepare("
                    DELETE pt 
                    FROM progresso_tarefa pt
                    INNER JOIN tarefa t ON pt.id_tarefa = t.id_tarefa
                    WHERE t.id_projeto = :id_projeto
                ");
                $stmt->execute([':id_projeto' => $_POST['projeto_id']]);
        
                // Deletar tarefas associadas ao projeto
                $stmt = $pdo->prepare("DELETE FROM tarefa WHERE id_projeto = :id_projeto");
                $stmt->execute([':id_projeto' => $_POST['projeto_id']]);
        
                // Deletar registros na tabela membro_projeto primeiro
                $stmt = $pdo->prepare("DELETE FROM membro_projeto WHERE id_projeto = :id_projeto");
                $stmt->execute([':id_projeto' => $_POST['projeto_id']]);
        
                // Deletar o projeto
                $stmt = $pdo->prepare("DELETE FROM projeto WHERE id_projeto = :id_projeto AND id_empresa = :id_empresa");
                $stmt->execute([
                    ':id_projeto' => $_POST['projeto_id'],
                    ':id_empresa' => $id_empresa
                ]);
        
                $pdo->commit();
            } catch (PDOException $e) {
                $pdo->rollBack();
                die("Erro ao deletar projeto: " . $e->getMessage());
            }
        }

        // Buscar projetos da empresa
        $stmt = $pdo->prepare("SELECT id_projeto, nome, tipo, data_inicio, data_fim, status, banner_path FROM projeto WHERE id_empresa = :id_empresa");
        $stmt->execute([':id_empresa' => $id_empresa]);
        $projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Buscar id_membro
        $stmt = $pdo->prepare("SELECT id_membro FROM membro WHERE email = :email");
        $stmt->execute([':email' => $logado]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_membro = $result['id_membro'];

        // Buscar projetos associados ao membro
        $stmt = $pdo->prepare("
            SELECT p.id_projeto, p.nome, p.tipo, p.data_inicio, p.data_fim, p.status, p.banner_path
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
    background-color: var(--secondary-color);
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    width: 250px;
    padding-top: 20px;
    z-index: 1000;
    transition: transform 0.3s ease;
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

.banner {
    height: 150px;
    width: 100%;
    background-size: cover;
    background-position: center;
    border-radius: 15px 15px 0 0;
}

/* Responsividade para dispositivos móveis */
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: 100vh; /* Mantém a altura total da tela */
        transform: translateX(-100%); /* Esconde a sidebar fora da tela */
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
    }

    .sidebar.show {
        transform: translateX(0); /* Exibe a sidebar */
    }

    .content {
        margin-left: 0; /* Remove a margem quando a sidebar é escondida */
    }

    .banner {
        height: 120px; /* Ajusta a altura da banner em dispositivos móveis */
    }

    /* Adiciona um botão para exibir a sidebar em dispositivos móveis */
    .menu-btn {
        display: block;
        background-color: var(--secondary-color);
        color: var(--text-color);
        border: none;
        padding: 10px 15px;
        font-size: 1.2rem;
        cursor: pointer;
        position: fixed;
        top: 10px;
        left: 10px;
        z-index: 1100; /* Certifique-se de que o botão está acima da sidebar */
    }

    .menu-btn:focus {
        outline: none;
    }

    .content{
        margin-top: 20px;
    }
}

/* Adiciona um botão para exibir a sidebar em dispositivos móveis */
@media (min-width: 769px) {
    .menu-btn {
        display: none;
    }
}

    </style>
</head>

<body>
<button class="menu-btn" onclick="toggleSidebar()">☰</button>
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
    <?php } else { ?>
        <a class="nav-link" href="notificacao.php">
            <i class="fas fa-users me-1"></i>Notificações
        </a>
    <?php } ?>
    <a href="dynamic-full-calendar.php">
        <i class="fas fa-calendar-alt me-1"></i>Calendário
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
                                <form method="POST" action="projeto.php" enctype="multipart/form-data">
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
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="No Prazo">No Prazo</option>
                                            <option value="Atrasado">Atrasado</option>
                                            <option value="Perto do Prazo">Perto do Prazo</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="banner">Banner do Projeto</label>
                                        <input type="file" class="form-control-file" id="banner" name="banner">
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
                <?php foreach ($projetos as $projeto) { ?>
                    <div class="col-md-4">
                        <div class="card">
                            <?php if ($projeto['banner_path']) { ?>
                                <div class="banner" style="background-image: url('<?php echo htmlspecialchars($projeto['banner_path']); ?>');"></div>
                            <?php } else { ?>
                                <div class="banner" style="background-color: grey;"></div>
                            <?php } ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($projeto['nome']); ?></h5>
                                <p class="card-text">Tipo: <?php echo htmlspecialchars($projeto['tipo']); ?></p>
                                <p class="card-text">Início: <?php echo $projeto['data_inicio']; ?></p>
                                <p class="card-text">Término: <?php echo $projeto['data_fim']; ?></p>
                                <p class="card-text">Status: <?php echo $projeto['status']; ?></p>
                                <a href="tarefas_projeto.php?id_projeto=<?php echo $projeto['id_projeto']; ?>" class="btn btn-info">Ver Tarefas</a>
                                <?php if ($tipo_usuario == 'empresa') { ?>
                                    <button class="btn btn-primary" data-toggle="modal" data-target="#editProjectModal<?php echo $projeto['id_projeto']; ?>">Editar</button>
                                    <form method="POST" action="projeto.php" class="d-inline" onsubmit="return confirm('Tem certeza que deseja deletar este projeto?');">
                                        <input type="hidden" name="projeto_id" value="<?php echo $projeto['id_projeto']; ?>">
                                        <button type="submit" name="delete_project" class="btn btn-danger">Deletar</button>
                                    </form>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($tipo_usuario == 'empresa') { ?>
                        <!-- Modal para editar projeto -->
                        <div class="modal fade" id="editProjectModal<?php echo $projeto['id_projeto']; ?>" tabindex="-1" role="dialog" aria-labelledby="editProjectModalLabel<?php echo $projeto['id_projeto']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editProjectModalLabel<?php echo $projeto['id_projeto']; ?>">Editar Projeto</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="projeto.php" enctype="multipart/form-data">
                                            <input type="hidden" name="id_projeto" value="<?php echo $projeto['id_projeto']; ?>">
                                            <div class="form-group">
                                                <label for="nome_projeto<?php echo $projeto['id_projeto']; ?>">Nome do Projeto</label>
                                                <input type="text" class="form-control" id="nome_projeto<?php echo $projeto['id_projeto']; ?>" name="nome_projeto" value="<?php echo htmlspecialchars($projeto['nome']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="tipo_projeto<?php echo $projeto['id_projeto']; ?>">Tipo do Projeto</label>
                                                <input type="text" class="form-control" id="tipo_projeto<?php echo $projeto['id_projeto']; ?>" name="tipo_projeto" value="<?php echo htmlspecialchars($projeto['tipo']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="data_inicio<?php echo $projeto['id_projeto']; ?>">Data de Início</label>
                                                <input type="date" class="form-control" id="data_inicio<?php echo $projeto['id_projeto']; ?>" name="data_inicio" value="<?php echo $projeto['data_inicio']; ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="data_fim<?php echo $projeto['id_projeto']; ?>">Data de Término</label>
                                                <input type="date" class="form-control" id="data_fim<?php echo $projeto['id_projeto']; ?>" name="data_fim" value="<?php echo $projeto['data_fim']; ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="status<?php echo $projeto['id_projeto']; ?>">Status</label>
                                                <select class="form-control" id="status<?php echo $projeto['id_projeto']; ?>" name="status">
                                                    <option value="No Prazo" <?php if ($projeto['status'] == 'No Prazo') echo 'selected'; ?>>No Prazo</option>
                                                    <option value="Atrasado" <?php if ($projeto['status'] == 'Atrasado') echo 'selected'; ?>>Atrasado</option>
                                                    <option value="Perto do Prazo" <?php if ($projeto['status'] == 'Perto do Prazo') echo 'selected'; ?>>Perto do Prazo</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="banner<?php echo $projeto['id_projeto']; ?>">Banner do Projeto</label>
                                                <input type="file" class="form-control-file" id="banner<?php echo $projeto['id_projeto']; ?>" name="banner">
                                                <input type="hidden" name="current_banner" value="<?php echo htmlspecialchars($projeto['banner_path']); ?>">
                                            </div>
                                            <button type="submit" name="edit_project" class="btn btn-primary btn-block">Salvar Alterações</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Removendo a configuração que previne o fechamento dos modais ao clicar fora
            // Isso pode estar causando os modais abrirem automaticamente

            // Resetar o formulário ao fechar o modal
            $('.modal').on('hidden.bs.modal', function () {
                $(this).find('form').trigger('reset');
            });

            // Função para pré-visualização da imagem do banner
            $('input[type="file"]').change(function(e) {
                var file = e.target.files[0];
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = $('<img>').attr('src', e.target.result);
                    $(e.target).closest('.form-group').find('.banner-preview').html(preview);
                };
                reader.readAsDataURL(file);
            });
        });

    </script>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('show');
}
</script>
</body>

</html>