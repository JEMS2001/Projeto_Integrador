<?php
session_start();

include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $tipo_usuario = $_POST['tipo_usuario'];
    
    // Selecionar a tabela com base no tipo de usuário
    if ($tipo_usuario == "empresa") {
        $table = "empresa";
    } else if ($tipo_usuario == "membro") {
        $table = "membro";
    } else {
        die("Tipo de usuário inválido.");
    }

    // Consulta SQL para verificar as credenciais
    try {
        $sql = "SELECT * FROM $table WHERE email = :email AND senha = :senha";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Login bem-sucedido
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<div class='alert alert-success'>Login realizado com sucesso!</div>";
            $_SESSION['email'] = $email;
            $_SESSION['senha'] = $senha;
            $_SESSION['tipo_usuario'] = $tipo_usuario;
            
            // Se for empresa, define o id_empresa na sessão
            if ($tipo_usuario == "empresa") {
                $_SESSION['id_empresa'] = $row['id_empresa'];
            }
            
            header("Location: dashboard.php");
        } else {
            // Falha no login
            echo "<div class='alert alert-danger'>Email ou senha incorretos.</div>";
            unset($_SESSION['email']);
            unset($_SESSION['senha']);
            header("Location: login.php");
        }
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}
?>

<?php include 'header.php'; ?>

<div class="container my-5">
    <div class="login text-center">
        <h1>Login</h1>
        <form action="login.php" method="post">
            <div class="mb-3">
                <input type="email" class="form-control" placeholder="Email" name="email" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" placeholder="Senha" name="senha" required>
            </div>
            <div class="mb-3">
                <select class="form-control" name="tipo_usuario" required>
                    <option value="empresa">Empresa</option>
                    <option value="membro">Membro</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success btn-lg w-100">Login</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
