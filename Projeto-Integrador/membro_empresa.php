<?php
session_start();
include_once('config.php');

if(!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    unset($_SESSION['email']);
    unset($_SESSION['senha']);
    header('Location: login.php');
    exit;
}

$logado = $_SESSION['email'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$id_empresa = $_SESSION['id_empresa']; // Verifique se $_SESSION['id_empresa'] está sendo definido corretamente

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cpf_membro'])) {
    $cpf_membro = $_POST['cpf_membro'];

    try {
        // Conexão com PDO (usando as configurações de config.php)
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verificar se o CPF do membro existe na tabela de membros
        $sql = "SELECT id_membro FROM membro WHERE cpf = :cpf";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':cpf', $cpf_membro);
        $stmt->execute();
        $membro = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($membro) {
            // Adicionar o ID da empresa ao membro
            $sql_update = "UPDATE membro SET id_empresa = :id_empresa WHERE id_membro = :id_membro";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->bindParam(':id_empresa', $id_empresa);
            $stmt_update->bindParam(':id_membro', $membro['id_membro']);
            $stmt_update->execute();

            echo "Membro adicionado à empresa com sucesso.";
        } else {
            echo "CPF de membro não encontrado.";
        }

    } catch(PDOException $e) {
        die("Erro na conexão: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="estilo.css">
    <title>Adicionar Membro à Empresa</title>
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
                    <?php if ($tipo_usuario == 'empresa') { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="membros.php">Membros</a>
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
    <h2>Adicionar Membro à Empresa</h2>
    <form method="POST" action="membro_empresa.php">
        <div class="form-group">
            <label for="cpf_membro">CPF do Membro:</label>
            <input type="text" class="form-control" id="cpf_membro" name="cpf_membro" required>
        </div>
        <button type="submit" class="btn btn-primary">Adicionar Membro</button>
    </form>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Fecha a conexão com o banco de dados após exibir os dados
$pdo = null;
?>
