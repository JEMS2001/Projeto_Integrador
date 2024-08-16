<?php
// Inicie a sessão e inclua a configuração no topo do arquivo
session_start();
include 'config.php';

// Função para redirecionar e encerrar o script
function redirect($url) {
    header("Location: $url");
    exit();
}

// Função para exibir mensagens de erro ou sucesso
function setMessage($type, $message) {
    $_SESSION['message'] = ['type' => $type, 'text' => $message];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $tipo_usuario = $_POST['tipo_usuario'];

    if ($tipo_usuario == "empresa") {
        $table = "empresa";
    } else if ($tipo_usuario == "membro") {
        $table = "membro";
    } else {
        setMessage('danger', "Tipo de usuário inválido.");
        redirect("login.php");
    }

    try {
        $sql = "SELECT * FROM $table WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($senha, $row['senha'])) {
                setMessage('success', "Login realizado com sucesso!");
                $_SESSION['email'] = $email;
                $_SESSION['tipo_usuario'] = $tipo_usuario;

                if ($tipo_usuario == "empresa") {
                    $_SESSION['id_empresa'] = $row['id_empresa'];
                } else if ($tipo_usuario == "membro") {
                    $_SESSION['id_membro'] = $row['id_membro'];
                    
                    if (!empty($row['id_empresa'])) {
                        $_SESSION['id_empresa'] = $row['id_empresa'];
                        
                        $sql_sessao = "INSERT INTO sessao_usuario (id_membro, data_inicio) VALUES (:id_membro, NOW())";
                        $stmt_sessao = $pdo->prepare($sql_sessao);
                        $stmt_sessao->bindParam(':id_membro', $row['id_membro']);
                        $stmt_sessao->execute();
                        
                        $_SESSION['id_sessao'] = $pdo->lastInsertId();

                        $sql_update = "UPDATE membro SET esta_logado = TRUE, ultimo_login = NOW() WHERE id_membro = :id_membro";
                        $stmt_update = $pdo->prepare($sql_update);
                        $stmt_update->bindParam(':id_membro', $row['id_membro']);
                        $stmt_update->execute();
                    }
                }

                redirect("dashboard.php");
            } else {
                setMessage('danger', "Email ou senha incorretos.");
                redirect("login.php");
            }
        } else {
            setMessage('danger', "Email ou senha incorretos.");
            redirect("login.php");
        }
    } catch (PDOException $e) {
        setMessage('danger', "Erro: " . $e->getMessage());
        redirect("login.php");
    }
}

// Incluir o cabeçalho e o restante do HTML
include 'layout/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

<style>
/* Estilos gerais */
.img-fluid {
    padding: 40px;
}

/* Configuração para telas grandes */
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

.footer-spacing {
    height: 100px; /* Ajuste o valor conforme necessário */
}

body {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

main {
    flex-grow: 1;
}

footer {
    background-color: #343a40;
    color: #ffffff;
    padding: 1.5rem 0;
    height: auto; /* Alterar altura fixa para altura automática */
    text-align: center;
}

/* Esconde a imagem do início em telas maiores */
.login-background-mobile {
    display: none;
}

.login-background {
    margin-bottom: 100px; /* Ajuste o valor conforme necessário */
}

/* Ajustes para dispositivos móveis */
@media (max-width: 768px) {
    /* Mostra a imagem no início em dispositivos móveis */
    .login-background-mobile {
        display: block;
        width: 100%;
        text-align: center;
        margin-top: 40px;
        margin-bottom: 20px;
        animation: fadeInMove 3s infinite ease-in-out; /* Animação repetida indefinidamente */
    }

    .login-background-mobile img {
        width: 100%;
        max-width: 350px;
        height: auto;
        display: block;
        margin: 0 auto;
    }

    /* Esconde a imagem que fica ao lado do formulário em dispositivos móveis */
    .login-background {
        display: none;
    }

    /* Estilização da página em dispositivos móveis */
    .login-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-top: 5px;
    }

    .login-form {
        width: 90%;
        max-width: 400px;
        padding: 20px;
        background: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin-top: 0;
    }
}

/* Animação para imagem em dispositivos móveis */
@keyframes fadeInMove {
    0% {
        opacity: 0;
        transform: translateY(-20px);
    }
    50% {
        opacity: 1;
        transform: translateY(0);
    }
    100% {
        opacity: 0;
        transform: translateY(20px);
    }
}

</style>

<!-- Imagem visível apenas em dispositivos móveis -->
<div class="login-background-mobile">
    <img src="img/hero/why-us.png" class="img-fluid" alt="Dashboard">
</div>

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
        </div>

        <!-- Imagem visível apenas em telas maiores -->
        <div class="login-background">
            <div class="hero-img mt-5" data-aos="zoom-in" data-aos-delay="200">
                <img src="img/hero/why-us.png" class="img-fluid" alt="Dashboard">
            </div>
        </div>
    </div>
    
    <!-- Adiciona o espaçamento entre a imagem e o footer -->
    <div class="footer-spacing"></div>
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
