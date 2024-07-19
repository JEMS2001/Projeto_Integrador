<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $tipo_usuario = $_POST['tipo_usuario'];

    if ($tipo_usuario == "empresa") {
        $table = "empresa";
    } else if ($tipo_usuario == "membro") {
        $table = "membro";
    } else {
        die("Tipo de usuário inválido.");
    }

    $sql = "SELECT * FROM $table WHERE reset_token = :token AND reset_token_expire > NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':token', $token);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $sql = "UPDATE $table SET senha = :senha, reset_token = NULL, reset_token_expire = NULL WHERE reset_token = :token";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':senha', $senha);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        echo "<div class='alert alert-success'>Sua senha foi redefinida com sucesso.</div>";
        header("Location: login.php");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Token inválido ou expirado.</div>";
    }
} else if (isset($_GET['token'])) {
    $token = $_GET['token'];
} else {
    die("Token não fornecido.");
}
?>

<?php include 'layout/header.php'; ?>

<div class="container mt-4">
    <div class="login-container">
        <div class="login-form">
            <h1 class="text-center mb-4">Redefinir Senha</h1>
            <form id="resetPasswordForm" action="reset_password.php" method="post">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="form-group">
                    <label for="senha">Nova Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="Nova Senha" required>
                </div>
                <div class="form-group">
                    <label for="tipo_usuario">Tipo de Usuário</label>
                    <select id="tipo_usuario" name="tipo_usuario" class="form-control" required>
                        <option value="empresa">Empresa</option>
                        <option value="membro">Membro</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">Redefinir Senha</button>
            </form>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>