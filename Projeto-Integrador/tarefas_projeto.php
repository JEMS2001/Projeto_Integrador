<?php
session_start();
include_once('config.php');

if(!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

$logado = $_SESSION['email'];
$tipo_usuario = $_SESSION['tipo_usuario'];

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_GET['id_projeto'])) {
        throw new Exception("ID do projeto não fornecido.");
    }

    $id_projeto = $_GET['id_projeto'];

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
    <style>
        .kanban-board {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .kanban-column {
            flex: 1;
            margin: 0 10px;
            background-color: #f4f4f4;
            border-radius: 5px;
            padding: 10px;
        }
        .kanban-task {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            cursor: move;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="home.php">JFHK</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="projeto.php">Projetos</a></li>
                <li class="nav-item"><a class="nav-link" href="perfil.php">Perfil</a></li>
                <?php if ($tipo_usuario == 'empresa'): ?>
                    <li class="nav-item"><a class="nav-link" href="membro_empresa.php">Membros</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="btn btn-danger" href="sair.php">Sair</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2>Detalhes do Projeto: <?php echo htmlspecialchars($projeto['nome']); ?></h2>
    <p><strong>Tipo:</strong> <?php echo htmlspecialchars($projeto['tipo']); ?></p>
    <p><strong>Data de Início:</strong> <?php echo htmlspecialchars($projeto['data_inicio']); ?></p>
    <p><strong>Data de Término:</strong> <?php echo htmlspecialchars($projeto['data_fim']); ?></p>

    <h3>Adicionar Tarefa</h3>
    <?php if ($tipo_usuario == 'empresa'): ?>
    <form method="POST" action="tarefas_projeto.php?id_projeto=<?php echo $id_projeto; ?>" class="mb-4">
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
        <button type="submit" name="add_task" class="btn btn-success">Adicionar Tarefa</button>
    </form>
    <?php endif; ?>

    <h3>Quadro Kanban</h3>
    <div class="kanban-board">
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
                        
                        <button class="btn btn-sm btn-info edit-task" data-id="<?php echo $tarefa['id_tarefa']; ?>">Editar</button>
                        
                    </div>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        <?php endforeach; ?>
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
        // Implementar lógica de edição aqui
        console.log('Editar tarefa: ' + taskId);
    });
});
</script>

</body>
</html>
