<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

$logado = $_SESSION['email'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$tabela = $_SESSION['tipo_usuario'];

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_GET['id_projeto'])) {
        throw new Exception("ID do projeto não fornecido.");
    }

    $id_projeto = $_GET['id_projeto'];

    if ($tipo_usuario == 'empresa' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_member_to_project'])) {
        $stmt = $pdo->prepare("INSERT INTO membro_projeto (id_membro, id_projeto) VALUES (:id_membro, :id_projeto)");
        $stmt->execute([
            ':id_membro' => $_POST['id_membro'],
            ':id_projeto' => $_POST['id_projeto']
        ]);
        header("Location: projeto.php?id_projeto=$id_projeto");
        exit;
    }
    
    // Verificar se o usuário tem acesso ao projeto
    if ($tipo_usuario == 'empresa') {
        $stmt = $pdo->prepare("SELECT * FROM projeto WHERE id_projeto = :id_projeto AND id_empresa = (SELECT id_empresa FROM empresa WHERE email = :email)");
        $stmt->execute([':id_projeto' => $id_projeto, ':email' => $logado]);
    } else {
        $stmt = $pdo->prepare("
            SELECT p.* 
            FROM projeto p
            INNER JOIN membro_projeto mp ON p.id_projeto = mp.id_projeto
            INNER JOIN membro m ON mp.id_membro = m.id_membro
            WHERE p.id_projeto = :id_projeto AND m.email = :email
        ");
        $stmt->execute([':id_projeto' => $id_projeto, ':email' => $logado]);
    }

    $projeto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$projeto) {
        throw new Exception("Projeto não encontrado ou você não tem permissão para acessá-lo.");
    }

    // Buscar tarefas do projeto
    if ($tipo_usuario == 'empresa') {
        $stmt = $pdo->prepare("SELECT t.*, m.nome as membro_nome FROM tarefa t LEFT JOIN membro m ON t.id_membro = m.id_membro WHERE t.id_projeto = :id_projeto");
        $stmt->execute([':id_projeto' => $id_projeto]);
    } else {
        $stmt = $pdo->prepare("
            SELECT t.*, m.nome as membro_nome 
            FROM tarefa t 
            LEFT JOIN membro m ON t.id_membro = m.id_membro 
            WHERE t.id_projeto = :id_projeto AND m.email = :email
        ");
        $stmt->execute([':id_projeto' => $id_projeto, ':email' => $logado]);
    }
    $tarefas = $stmt->fetchAll(PDO::FETCH_ASSOC);

   
   
    
    // Buscar membros da empresa (apenas para empresas)
    $membros = [];
    if ($tipo_usuario == 'empresa') {
        $stmt = $pdo->prepare("SELECT m.id_membro, m.nome FROM membro m INNER JOIN membro_projeto mp ON m.id_membro = mp.id_membro WHERE m.id_empresa = (SELECT id_empresa FROM empresa WHERE email = :email) AND mp.id_projeto = :id_projeto");
        $stmt->execute([':email' => $logado, ':id_projeto' => $id_projeto]);
        $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Adicionar tarefa (apenas para empresas)
    if ($tipo_usuario == 'empresa' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_task'])) {
        $stmt = $pdo->prepare("INSERT INTO tarefa (nome, descricao, status, id_projeto, id_membro) VALUES (:nome, :descricao, :status, :id_projeto, :id_membro)");
        $stmt->execute([
            ':nome' => $_POST['nome_tarefa'],
            ':descricao' => $_POST['descricao'],
            ':status' => $_POST['status'],
            ':id_projeto' => $id_projeto,
            ':id_membro' => $_POST['id_membro']
        ]);
        header("Location: tarefas_projeto.php?id_projeto=$id_projeto");
        exit;
    }

    // Adicionar membro ao projeto (apenas para empresas)
  

    // Buscar id_empresa
    $stmt = $pdo->prepare("SELECT id_empresa FROM empresa WHERE email = :email");
    $stmt->execute([':email' => $logado]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $id_empresa = $result['id_empresa'];

    // Buscar todos os projetos da empresa
    $stmt = $pdo->prepare("SELECT id_projeto, nome, tipo, data_inicio, data_fim FROM projeto WHERE id_empresa = :id_empresa");
    $stmt->execute([':id_empresa' => $id_empresa]);
    $projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar todos os membros da empresa
    $stmt = $pdo->prepare("SELECT id_membro, nome FROM membro WHERE id_empresa = :id_empresa");
    $stmt->execute([':id_empresa' => $id_empresa]);
    $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(Exception $e) {
    die("Erro: " . $e->getMessage());
}


?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Projeto: <?php echo htmlspecialchars($projeto['nome']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="estilo.css">
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
            margin-left: 270px;
            padding: 20px;
        }
        .kanban-board {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .kanban-column {
            flex: 1;
            margin: 0 10px;
            background-color: #fff;
            border-radius: 5px;
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: background-color 0.3s;
        }
        .kanban-column h4 {
            color: #ff8c00;
        }
        .kanban-task {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            cursor: move;
            transition: transform 0.3s;
        }
        .kanban-task:hover {
            transform: scale(1.05);
        }
        .modal-content {
            border-radius: 15px;
        }
        .modal-header {
            background-color: var(--primary-color);
            color: var(--text-color);
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .modal-footer {
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
        }
        .btn-custom {
            background-color: var(--primary-color);
            color: var(--text-color);
        }
        .btn-custom:hover {
            background-color: var(--secondary-color);
        }

        .btn-add-task {
            margin-bottom: 20px;
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
    <h2>Detalhes do Projeto: <?php echo htmlspecialchars($projeto['nome']); ?></h2>
    <p><strong>Tipo:</strong> <?php echo htmlspecialchars($projeto['tipo']); ?></p>
    <p><strong>Data de Início:</strong> <?php echo htmlspecialchars($projeto['data_inicio']); ?></p>
    <p><strong>Data de Término:</strong> <?php echo htmlspecialchars($projeto['data_fim']); ?></p>

    <?php if ($tipo_usuario == 'empresa'): ?>
    <button type="button" class="btn btn-custom btn-add-task" data-toggle="modal" data-target="#addTaskModal">
        <i class="fas fa-plus"></i> Adicionar Tarefa
    </button>


     <!-- Botão para abrir o modal de adicionar membro ao projeto -->
     <button class="btn btn-primary mb-4" data-toggle="modal" data-target="#addMemberModal"><i class="fas fa-user-plus"></i> Adicionar Membro ao Projeto</button>


<!-- Modal para adicionar membro ao projeto -->
<div class="modal fade" id="addMemberModal" tabindex="-1" role="dialog" aria-labelledby="addMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMemberModalLabel">Adicionar Membro ao Projeto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="tarefas_projeto.php?id_projeto=<?php echo $id_projeto; ?>">
                    <div class="form-group">
                        <label for="id_projeto">Projeto</label>
                        <select class="form-control" id="id_projeto" name="id_projeto" required disabled>
                            <option value="<?php echo $projeto['id_projeto']; ?>"><?php echo htmlspecialchars($projeto['nome']); ?></option>
                        </select>
                        <input type="hidden" name="id_projeto" value="<?php echo $projeto['id_projeto']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="id_membro">Membro</label>
                        <select class="form-control" id="id_membro" name="id_membro" required>
                            <?php foreach ($membros as $membro) { ?>
                                <option value="<?php echo $membro['id_membro']; ?>"><?php echo htmlspecialchars($membro['nome']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <button type="submit" name="add_member_to_project" class="btn btn-primary btn-block">Adicionar Membro ao Projeto</button>
                </form>
            </div>
        </div>
    </div>
</div>




    <?php 


endif; ?>

    <h3 class="mt-5">Quadro Kanban</h3>
    <div class="kanban-board mt-5">
        <?php
        $status_columns = ['todo' => 'To Do', 'inprogress' => 'In Progress', 'done' => 'Done'];
        foreach ($status_columns as $status => $title):
        ?>
            <div class="kanban-column" id="<?php echo $status; ?>">
                <h4><?php echo $title; ?> <span class="badge badge-secondary"><?php echo count(array_filter($tarefas, function($t) use ($status) { return $t['status'] == $status; })); ?></span></h4>
                <?php
                foreach ($tarefas as $tarefa):
                    if ($tarefa['status'] == $status):
                ?>
                    <div class="kanban-task" data-id="<?php echo $tarefa['id_tarefa']; ?>">
                        <h5><?php echo htmlspecialchars($tarefa['nome']); ?></h5>
                        <p><?php echo htmlspecialchars($tarefa['descricao']); ?></p>
                        <p><small>Responsável: <?php echo htmlspecialchars($tarefa['membro_nome']); ?></small></p>
                        
                        <button class="btn btn-sm btn-info edit-task" data-id="<?php echo $tarefa['id_tarefa']; ?>" data-toggle="modal" data-target="#editTaskModal">Editar</button>
                        <button class="btn btn-sm btn-danger delete-task" data-id="<?php echo $tarefa['id_tarefa']; ?>" data-toggle="modal" data-target="#deleteTaskModal">Deletar</button>

                    </div>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal para Adicionar Tarefa -->
<div class="modal fade" id="addTaskModal" tabindex="-1" role="dialog" aria-labelledby="addTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTaskModalLabel">Adicionar Nova Tarefa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="tarefas_projeto.php?id_projeto=<?php echo $id_projeto; ?>">
                    <div class="form-group">
                        <label for="nome_tarefa">Nome da Tarefa</label>
                        <input type="text" class="form-control" id="nome_tarefa" name="nome_tarefa" required>
                    </div>
                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="todo">To Do</option>
                            <option value="inprogress">In Progress</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_membro">Membro Responsável</label>
                        <select class="form-control" id="id_membro" name="id_membro" required>
                            <?php foreach ($membros as $membro): ?>
                                <option value="<?php echo $membro['id_membro']; ?>"><?php echo htmlspecialchars($membro['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="add_task" class="btn btn-custom">Adicionar Tarefa</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" role="dialog" aria-labelledby="editTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTaskModalLabel">Editar Tarefa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editTaskForm" method="POST" action="edit_task.php">
                    <input type="hidden" id="editTaskId" value="<?= $tarefa['id_tarefa'] ?>" name="id_tarefa">
                    <input type="hidden" name="id_projeto" value="<?php echo $projeto['id_projeto']; ?>">
                    <div class="form-group">
                        <label for="editTaskName">Nome da Tarefa</label>
                        <input type="text" class="form-control" id="editTaskName" name="nome_tarefa" required>
                    </div>
                    <div class="form-group">
                        <label for="editTaskDescription">Descrição</label>
                        <textarea class="form-control" id="editTaskDescription" name="descricao" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editTaskStatus">Status</label>
                        <select class="form-control" id="editTaskStatus" name="status" required>
                            <option value="todo">To Do</option>
                            <option value="inprogress">In Progress</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editTaskMember">Membro Responsável</label>
                        <select class="form-control" id="editTaskMember" name="id_membro" required>
                            <?php foreach ($membros as $membro): ?>
                                <option value="<?php echo $membro['id_membro']; ?>"><?php echo htmlspecialchars($membro['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="edit_task" class="btn btn-custom">Salvar Alterações</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Delete Task Modal -->
<div class="modal fade" id="deleteTaskModal" tabindex="-1" role="dialog" aria-labelledby="deleteTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTaskModalLabel">Excluir Tarefa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="deleteTaskForm" method="POST" action="delete_task.php">
                    <input type="hidden" id="deleteTaskId" name="id_tarefa">
                    <p>Tem certeza de que deseja excluir esta tarefa?</p>
                    <button type="submit" name="delete_task" class="btn btn-danger">Excluir</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
$(function() {
    $(".kanban-column").sortable({
        connectWith: ".kanban-column",
        update: function(event, ui) {
            var taskId = ui.item.data('id');
            var newStatus = ui.item.parent().attr('id');
            $.ajax({
                url: 'update_task_status.php',
                method: 'POST',
                data: { id_tarefa: taskId, status: newStatus },
                success: function(response) {
                    console.log('Status atualizado');
                }
            });
        }
    }).disableSelection();

    $(".edit-task").click(function() {
        var taskId = $(this).data('id');
        $.ajax({
            url: 'get_task.php',
            method: 'POST',
            data: { id_tarefa: taskId },
            success: function(response) {
                var task = JSON.parse(response);
                $("#editTaskId").val(task.id_tarefa);
                $("#editTaskName").val(task.nome);
                $("#editTaskDescription").val(task.descricao);
                $("#editTaskStatus").val(task.status);
                $("#editTaskMember").val(task.id_membro);
                $("#editTaskModal").modal('show');
            }
        });
    });

    $(document).ready(function() {
    $(".delete-task").click(function() {
        var taskId = $(this).data('id');
        $('#deleteTaskId').val(taskId);
    });

    $("#deleteTaskForm").submit(function(event) {
        event.preventDefault();
        $.ajax({
            url: 'delete_task.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.startsWith('error:')) {
                    alert(response.replace('error: ', ''));
                } else if (response === 'success') {
                    
                    alert('Tarefa excluída com sucesso!');
                    location.reload();
                }
            }
        });
    });
});

});
</script>

</body>
</html>
