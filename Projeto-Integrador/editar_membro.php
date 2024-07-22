<?php
session_start();
include_once('config.php');

$mensagem = '';


$id_membro = $_GET['id'];

$sql = "SELECT * FROM membro WHERE id_membro = :id_membro";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id_membro', $id_membro);
$stmt->execute();
$membro = $stmt->fetch(PDO::FETCH_ASSOC);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['nome'], $_POST['data_nascimento'], $_POST['telefone'], $_POST['cpf'], $_POST['email'], $_POST['senha'])) {
        $nome = $_POST['nome'];
        $data_nascimento = $_POST['data_nascimento'];
        $telefone = $_POST['telefone'];
        $cpf = $_POST['cpf'];
        $email = $_POST['email'];
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

        $sql = "UPDATE membro SET nome = :nome, data_nascimento = :data_nascimento, telefone = :telefone, cpf = :cpf, email = :email, senha = :senha WHERE id_membro = :id_membro";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':data_nascimento', $data_nascimento);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha);
        $stmt->bindParam(':id_membro', $id_membro);

        if ($stmt->execute()) {
            $mensagem = "Membro atualizado com sucesso.";
            header("Location: home.php");
            exit();
        } else {
            $mensagem = "Erro ao atualizar membro: " . $stmt->errorInfo()[2];
        }
    } else {
        $mensagem = "Todos os campos do formulário devem ser preenchidos.";
    }
}
?>

<?php include 'layout/header.php'; ?>

<div class="container mt-4">
    <div class="registration-container">
        <div class="registration-form">
            <h1 class="text-center mb-4">Editar Membro</h1>
            <?php if (!empty($mensagem)): ?>
                <div class="alert <?php echo ($stmt && $stmt->execute()) ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>
            <form id="registrationForm" action="<?php echo $_SERVER['PHP_SELF'] . "?id=" . $id_membro; ?>" method="post">
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" placeholder="Nome Completo" value="<?php echo $membro['nome']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="data_nascimento">Data de Nascimento</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo $membro['data_nascimento']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" value="<?php echo $membro['telefone']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="cpf">CPF</label>
                    <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" value="<?php echo $membro['cpf']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="exemplo@dominio.com" value="<?php echo $membro['email']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="Senha" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}">
                    <div class="password-hint">A senha deve ter pelo menos 8 caracteres, incluindo letras maiúsculas, minúsculas, números e caracteres especiais.</div>
                </div>
                <button type="submit">Salvar Alterações</button>
            </form>
        </div>
        <div class="login-background">
            <div class="hero-img mt-5" data-aos="zoom-in" data-aos-delay="200">
                <img src="img\hero\5024147.jpg" class="img-fluid" alt="Dashboard">
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>