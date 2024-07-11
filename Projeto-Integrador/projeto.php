<?php
session_start();
include_once('config.php');

if(!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

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

        // Adicionar membro ao projeto (apenas para empresas)
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_member_to_project'])) {
            $stmt = $pdo->prepare("INSERT INTO membro_projeto (id_membro, id_projeto) VALUES (:id_membro, :id_projeto)");
            $stmt->execute([
                ':id_membro' => $_POST['id_membro'],
                ':id_projeto' => $_POST['id_projeto']
            ]);
        }

        // Buscar todos os projetos da empresa
        $stmt = $pdo->prepare("SELECT id_projeto, nome, tipo, data_inicio, data_fim FROM projeto WHERE id_empresa = :id_empresa");
        $stmt->execute([':id_empresa' => $id_empresa]);
        $projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Buscar todos os membros da empresa
        $stmt = $pdo->prepare("SELECT id_membro, nome FROM membro WHERE id_empresa = :id_empresa");
        $stmt->execute([':id_empresa' => $id_empresa]);
        $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="estilo.css">
    <title>Projetos</title>
</head>
<body>
    
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="home.php">JFHK</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
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
                    <?php if ($tipo_usuario == 'empresa') { ?>
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

<div class="container mt-4">
    <h2>Projetos</h2>

    <?php if ($tipo_usuario == 'empresa') { ?>
    <!-- Formulário para adicionar projeto (apenas para empresas) -->
    <form method="POST" action="projeto.php" class="mb-4">
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
        <button type="submit" name="add_project" class="btn btn-success">Adicionar Projeto</button>
    </form>

    <!-- Formulário para adicionar membro ao projeto (apenas para empresas) -->
    <form method="POST" action="projeto.php" class="mb-4">
        <div class="form-group">
            <label for="id_projeto">Projeto</label>
            <select class="form-control" id="id_projeto" name="id_projeto" required>
                <?php foreach ($projetos as $projeto) { ?>
                    <option value="<?php echo $projeto['id_projeto']; ?>"><?php echo htmlspecialchars($projeto['nome']); ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="id_membro">Membro</label>
            <select class="form-control" id="id_membro" name="id_membro" required>
                <?php foreach ($membros as $membro) { ?>
                    <option value="<?php echo $membro['id_membro']; ?>"><?php echo htmlspecialchars($membro['nome']); ?></option>
                <?php } ?>
            </select>
        </div>
        <button type="submit" name="add_member_to_project" class="btn btn-primary">Adicionar Membro ao Projeto</button>
    </form>
    <?php } ?>

    <!-- Tabela de projetos -->
    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Data de Início</th>
                <th>Data de Término</th>
                <?php if ($tipo_usuario == 'empresa') { ?>
                <th>Ações</th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projetos as $projeto) { ?>
                <tr>
                    <td><a href='tarefas_projeto.php?id_projeto=<?php echo $projeto['id_projeto']; ?>'><?php echo htmlspecialchars($projeto['nome']); ?></a></td>
                    <td><?php echo htmlspecialchars($projeto['tipo']); ?></td>
                    <td><?php echo $projeto['data_inicio']; ?></td>
                    <td><?php echo $projeto['data_fim']; ?></td>
                    <?php if ($tipo_usuario == 'empresa') { ?>
                    <td>
                        <form method='POST' action='projeto.php' style='display:inline-block'>
                            <input type='hidden' name='projeto_id' value='<?php echo $projeto['id_projeto']; ?>'>
                            <button type='submit' name='delete_project' class='btn btn-danger'>Deletar</button>
                        </form>
                    </td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="js/bootstrap.min.js"></script>
</body>
</html>