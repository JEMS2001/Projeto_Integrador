<?php
session_start();


if (isset($_SESSION['email'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<?php
include('config.php');
$mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['nome'], $_POST['data_nascimento'], $_POST['telefone'], $_POST['cpf'], $_POST['email'], $_POST['senha'])) {
        $nome = $_POST['nome'];
        $data_nascimento = $_POST['data_nascimento'];
        $telefone = $_POST['telefone'];
        $cpf = $_POST['cpf'];
        $email = $_POST['email'];
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Use password hashing

        // Verificar se o CPF já existe no banco de dados
        $sql = "SELECT COUNT(*) FROM membro WHERE cpf = :cpf";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->execute();
        $cpfCount = $stmt->fetchColumn();

        if ($cpfCount > 0) {
            $mensagem = "CPF já cadastrado. Por favor, use outro CPF.";
        } else {
            $sql = "INSERT INTO membro(nome, data_nascimento, telefone, cpf, email, senha) VALUES (:nome, :data_nascimento, :telefone, :cpf, :email, :senha)";
            $stmt = $pdo->prepare($sql);

            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':data_nascimento', $data_nascimento);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':cpf', $cpf);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':senha', $senha);

            if ($stmt->execute()) {
                $mensagem = "Membro cadastrado com sucesso.";
                header("Location: home.php");
                exit();
            } else {
                $mensagem = "Erro ao cadastrar membro: " . $stmt->errorInfo()[2];
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
.registration-form input[type="password"],
.registration-form input[type="date"],
.registration-form input[type="tel"] {
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

.registration-form .btn-get-started {
    background-color: var(--accent-color);
    color: var(--primary-color);
}

.registration-form .btn-get-started:hover {
    background-color: #e89419;
}

.registration-form .btn-learn-more {
    background-color: transparent;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
}

.registration-form .btn-learn-more:hover {
    background-color: var(--primary-color);
    color: var(--text-color);
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
</style>
<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">


<div class="container mt-4">
    <div class="registration-container">
        <div class="registration-form">
            <h1 class="text-center mb-4">Cadastro de Membro</h1>
            <?php if (!empty($mensagem)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>
            <form id="registrationForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" placeholder="Nome Completo" required>
                </div>
                <div class="form-group">
                    <label for="data_nascimento">Data de Nascimento</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" required>
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" required>
                </div>
                <div class="form-group">
                    <label for="cpf">CPF</label>
                    <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" required>
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
                <img src="img/hero/5024147.jpg" class="img-fluid" alt="Dashboard">
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
    IMask(document.getElementById('cpf'), {
        mask: '000.000.000-00'
    });

    IMask(document.getElementById('telefone'), {
        mask: '(00) 00000-0000'
    });
});
</script>

<?php include 'layout/footer.php'; ?>
