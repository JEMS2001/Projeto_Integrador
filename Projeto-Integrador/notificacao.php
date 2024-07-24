<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

$tabela = $_SESSION['tipo_usuario'];
$logado = $_SESSION['email'];
$id_membro = $_SESSION['id_membro'];

// Handle accepting or rejecting notifications
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accept'])) {
        $id_notificacao = $_POST['accept'];
        try {
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Get notification details
            $sql = "SELECT id_empresa FROM notificacao WHERE id_notificacao = :id_notificacao";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_notificacao', $id_notificacao);
            $stmt->execute();
            $notificacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($notificacao) {
                $id_empresa = $notificacao['id_empresa'];

                // Update member's id_empresa
                $sql_update_membro = "UPDATE membro SET id_empresa = :id_empresa WHERE id_membro = :id_membro";
                $stmt_update_membro = $pdo->prepare($sql_update_membro);
                $stmt_update_membro->bindParam(':id_empresa', $id_empresa);
                $stmt_update_membro->bindParam(':id_membro', $id_membro);
                $stmt_update_membro->execute();

                // Delete notification
                $sql_delete_notificacao = "DELETE FROM notificacao WHERE id_notificacao = :id_notificacao";
                $stmt_delete_notificacao = $pdo->prepare($sql_delete_notificacao);
                $stmt_delete_notificacao->bindParam(':id_notificacao', $id_notificacao);
                $stmt_delete_notificacao->execute();

                echo '<div class="alert alert-success">Convite aceito com sucesso.</div>';
            }
        } catch (PDOException $e) {
            die("Erro na conexão: " . $e->getMessage());
        }
    } elseif (isset($_POST['reject'])) {
        $id_notificacao = $_POST['reject'];
        try {
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Delete notification
            $sql_delete_notificacao = "DELETE FROM notificacao WHERE id_notificacao = :id_notificacao";
            $stmt_delete_notificacao = $pdo->prepare($sql_delete_notificacao);
            $stmt_delete_notificacao->bindParam(':id_notificacao', $id_notificacao);
            $stmt_delete_notificacao->execute();

            echo '<div class="alert alert-success">Convite recusado com sucesso.</div>';
        } catch (PDOException $e) {
            die("Erro na conexão: " . $e->getMessage());
        }
    }
}

// Fetch notifications
$notificacoes = [];
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT n.id_notificacao, n.cpf_membro, e.nome AS nome_empresa 
            FROM notificacao n 
            JOIN empresa e ON n.id_empresa = e.id_empresa 
            WHERE n.cpf_membro = (SELECT cpf FROM membro WHERE id_membro = :id_membro)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_membro', $id_membro);
    $stmt->execute();
    $notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
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
    <title>Notificações</title>
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
        <?php }else{ ?>
            
                <a class="nav-link" href="notificacao.php">
                    <i class="fas fa-users me-1"></i>Notificações
                </a>
            
        <?php } ?>
        <a href="monitoramento.php">
            <i class="fas fa-chart-bar me-1"></i>Relatórios
        </a>
        <a href="sair.php" class="btn btn-danger mt-auto">
            <i class="fas fa-sign-out-alt me-1"></i>Sair
        </a>
    </div>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Notificações</h2>
        <div class="row">
            <?php if (count($notificacoes) > 0): ?>
                <?php foreach ($notificacoes as $notificacao): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card animate__animated animate__fadeInUp">
                            <div class="card-body">
                                <h5 class="card-title">Convite de: <?php echo htmlspecialchars($notificacao['nome_empresa']); ?></h5>
                                <form method="POST" action="notificacao.php">
                                    <input type="hidden" name="id_notificacao" value="<?php echo $notificacao['id_notificacao']; ?>">
                                    <button type="submit" name="accept" value="<?php echo $notificacao['id_notificacao']; ?>" class="btn btn-success">Aceitar</button>
                                    <button type="submit" name="reject" value="<?php echo $notificacao['id_notificacao']; ?>" class="btn btn-danger">Recusar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">Nenhuma notificação encontrada.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/bootstrap.min.js"></script>
</body>
</html>
