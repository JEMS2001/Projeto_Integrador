<?php
session_start();

include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $tipo_usuario = $_POST['tipo_usuario'];

    if ($tipo_usuario == "empresa") {
        $table = "empresa";
    } else if ($tipo_usuario == "membro") {
        $table = "membro";
    } else {
        die("Tipo de usuário inválido.");
    }

    try {
        $sql = "SELECT * FROM $table WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($senha, $row['senha'])) {
                echo "<div class='alert alert-success'>Login realizado com sucesso!</div>";
                $_SESSION['email'] = $email;
                $_SESSION['tipo_usuario'] = $tipo_usuario;

                if ($tipo_usuario == "empresa") {
                    $_SESSION['id_empresa'] = $row['id_empresa'];
                }

                header("Location: dashboard.php");
            } else {
                echo "<div class='alert alert-danger'>Email ou senha incorretos.</div>";
                unset($_SESSION['email']);
                header("Location: login.php");
            }
        } else {
            echo "<div class='alert alert-danger'>Email ou senha incorretos.</div>";
            unset($_SESSION['email']);
            header("Location: login.php");
        }
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}
?>

<?php include 'layout/header.php'; ?>
<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

<style>
.login-container {
    display: flex;
    height: 80vh;
}

.login-form {
    width: 50%;
    padding: 40px;
    background: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.login-form h1 {
    margin-bottom: 20px;
    opacity: 0;
    transform: translateY(-20px);
    transition: opacity 0.5s, transform 0.5s;
}

.login-form input[type="email"], 
.login-form input[type="password"], 
.login-form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    opacity: 0;
    transform: translateX(-20px);
    transition: opacity 0.5s, transform 0.5s;
}

.login-form button {
    width: 100%;
    padding: 10px;
    background-color: var(--accent-color);
    color: var(--primary-color);
    border: none;
    border-radius: 4px;
    cursor: pointer;
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s, transform 0.5s, background-color 0.3s;
}

.login-form button:hover {
    background-color: #e89419;
}
</style>

<div class="container">
    <div class="login-container">
        <div class="login-form">
            <h1 class="text-center mb-4">Login</h1>
            <form id="loginForm" action="login.php" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="exemplo@dominio.com" required>
                </div>
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="Senha" required>
                </div>
                <div class="form-group">
                    <label for="tipo_usuario">Tipo de Usuário</label>
                    <select id="tipo_usuario" name="tipo_usuario" class="form-control" required>
                        <option value="empresa">Empresa</option>
                        <option value="membro">Membro</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success btn-lg w-100">Login</button>
            </form>
            <!-- Botão "Esqueci a minha senha" -->
            <div class="text-center mt-3">
                <a href="forgot_password.php" class="btn btn-link">Esqueci a minha senha</a>
            </div>
        </div>
        <div class="login-background">
            <div class="hero-img mt-5" data-aos="zoom-in" data-aos-delay="200">
                <img src="img/hero/why-us.png" class="img-fluid" alt="Dashboard">
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const elements = document.querySelectorAll('h1, label, input, button, .password-hint, select, .login-background');
    elements.forEach((el, index) => {
        setTimeout(() => {
            el.style.opacity = '1';
            el.style.transform = 'translate(0, 0)';
        }, index * 100);
    });

    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.transform = 'scale(1.05)';
            this.style.transition = 'transform 0.3s';
        });
        input.addEventListener('blur', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
</script>

<?php include 'layout/footer.php'; ?>
