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
$tabela=$_SESSION['tipo_usuario'];
try {
    // Conexão com PDO (usando as configurações de config.php)
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta SQL para buscar informações com base no email logado
    if ($tabela === 'empresa') {
        $sql = "SELECT nome, cnpj, endereco, email FROM empresa WHERE email = :email";
    } else {
        $sql = "SELECT nome, data_nascimento, telefone, cpf, email FROM membro WHERE email = :email";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $logado);
    $stmt->execute();

} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="estilo.css">
    <title>Perfil</title>
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


<div class="container mt-4">
   
    <h2>Informações do Perfil</h2>
    <table class="table table-bordered">
        <tbody>
            <?php
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($tabela === 'empresa') {
                    echo "<tr>";
                    echo "<th>Nome da Empresa</th><td>".$row['nome']."</td>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<th>CNPJ</th><td>".$row['cnpj']."</td>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<th>Endereço</th><td>".$row['endereco']."</td>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<th>Email</th><td>".$row['email']."</td>";
                    echo "</tr>";
                } else {
                    echo "<tr>";
                    echo "<th>Nome</th><td>".$row['nome']."</td>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<th>Data de Nascimento</th><td>".$row['data_nascimento']."</td>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<th>Telefone</th><td>".$row['telefone']."</td>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<th>CPF</th><td>".$row['cpf']."</td>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<th>Email</th><td>".$row['email']."</td>";
                    echo "</tr>";
                }
            }
            ?>
        </tbody>
    </table>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Fecha a conexão com o banco de dados após exibir os dados
$pdo = null;
?>
