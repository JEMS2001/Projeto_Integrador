<?php
session_start();

if (isset($_SESSION['email'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<?php
include_once('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['nome'], $_POST['cnpj'], $_POST['endereco'], $_POST['email'], $_POST['senha'])) {
        $nome = $_POST['nome'];
        $cnpj = $_POST['cnpj'];
        $endereco = $_POST['endereco'];
        $email = $_POST['email'];
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

        // Verificar se o CNPJ já existe no banco de dados
        $sql = "SELECT COUNT(*) FROM empresa WHERE cnpj = :cnpj";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':cnpj', $cnpj);
        $stmt->execute();
        $cnpjCount = $stmt->fetchColumn();

        if ($cnpjCount > 0) {
            $mensagem = "CNPJ já cadastrado. Por favor, use outro CNPJ.";
        } else {
            $sql = "INSERT INTO empresa(nome, cnpj, endereco, email, senha) VALUES (:nome, :cnpj, :endereco, :email, :senha)";
            $stmt = $pdo->prepare($sql);

            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':cnpj', $cnpj);
            $stmt->bindParam(':endereco', $endereco);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':senha', $senha);

            if ($stmt->execute()) {
                $_SESSION['mensagem'] = "Cadastro realizado com sucesso.";
                header("Location: home.php");
                exit();
            } else {
                $mensagem = "Erro ao inserir o registro: " . $stmt->errorInfo()[2];
            }
        }
    } else {
        $mensagem = "Todos os campos do formulário devem ser preenchidos.";
    }
}
?>

<?php include 'layout/header.php'; ?>

<style>
.registration-container {
    display: flex;
    height: 100vh;
    align-items: center; /* Centraliza verticalmente */
}

.registration-form {
    width: 50%;
    padding: 40px;
    background: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.login-background {
    width: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white; /* Adicione uma cor de fundo se desejar */
}

.login-background img {
    max-width: 100%;
    height: auto;
}

.registration-form h1 {
    margin-bottom: 20px;
    opacity: 0;
    transform: translateY(-20px);
    transition: opacity 0.5s, transform 0.5s;
}

.registration-form input[type="text"], 
.registration-form input[type="email"], 
.registration-form input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    opacity: 0;
    transform: translateX(-20px);
    transition: opacity 0.5s, transform 0.5s;
}

.registration-form button {
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

.registration-form button:hover {
    background-color: #e89419;
}

.registration-form .form-group {
    margin-bottom: 15px;
}

.registration-form label {
    display: block;
    margin-bottom: 5px;
    opacity: 0;
    transform: translateX(-20px);
    transition: opacity 0.5s, transform 0.5s;
}

.password-hint {
    font-size: 0.9em;
    color: #888;
    margin-top: -10px;
    margin-bottom: 15px;
    opacity: 0;
    transform: translateX(-20px);
    transition: opacity 0.5s, transform 0.5s;
}

.login-background img {
    max-width: 100%;
    height: auto;
}
</style>

<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">


<div class="container">
    <div class="registration-container">
        <div class="registration-form">
            <h1 class="text-center mb-4">Cadastro de Empresa</h1>
            <?php if (isset($mensagem)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>
            <form id="registrationForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <div class="form-group">
                    <label for="nome">Nome da Empresa</label>
                    <input type="text" id="nome" name="nome" placeholder="Nome da Empresa" required>
                </div>
                <div class="form-group">
                    <label for="cnpj">CNPJ</label>
                    <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00" required>
                </div>
                <div class="form-group">
                    <label for="endereco">Endereço</label>
                    <input type="text" id="endereco" name="endereco" placeholder="Endereço" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="exemplo@dominio.com" required>
                </div>
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="Senha" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}">
                    <div class="password-hint">A senha deve ter pelo menos 8 caracteres, incluindo letras maiúsculas, minúsculas, números e caracteres especiais.</div>
                </div>
                <div class="form-group">
                    <label for="confirmarSenha">Confirmar Senha</label>
                    <input type="password" id="confirmarSenha" name="confirmarSenha" placeholder="Confirmar Senha" required>
                </div>
                <button type="submit">Cadastrar</button>
            </form>
        </div>
        <div class="login-background">
            <div class="hero-img mt-5" data-aos="zoom-in" data-aos-delay="200">
                <img src="img/hero/skills.png" class="img-fluid" alt="Dashboard">
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/imask"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const elements = document.querySelectorAll('h1, label, input, button, .password-hint');
    elements.forEach((el, index) => {
        setTimeout(() => {
            el.style.opacity = '1';
            el.style.transform = 'translate(0, 0)';
        }, index * 100);
    });

    const form = document.getElementById('registrationForm');
    const senha = document.getElementById('senha');
    const confirmarSenha = document.getElementById('confirmarSenha');

    form.addEventListener('submit', function(event) {
        if (senha.value !== confirmarSenha.value) {
            event.preventDefault();
            alert('As senhas não coincidem. Por favor, tente novamente.');
            senha.value = '';
            confirmarSenha.value = '';
            senha.focus();
        }
    });

    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.transform = 'scale(1.05)';
            this.style.transition = 'transform 0.2s';
        });
        input.addEventListener('blur', function() {
            this.style.transform = 'scale(1)';
        });
    });

    // Apply IMask to inputs
    IMask(document.getElementById('cnpj'), {
        mask: '00.000.000/0000-00'
    });
});
</script>

<?php include 'layout/footer.php'; ?>
