<?php
session_start();

if(isset($_SESSION['email'])) {
    
    header("Location: dashboard.php");
    exit; 
}
?>

<?php
// Incluir o arquivo de configuração que contém a conexão com o banco de dados
include_once('config.php');

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar se todos os campos foram preenchidos
    if (isset($_POST['nome'], $_POST['cnpj'], $_POST['endereco'], $_POST['email'], $_POST['senha'])) {
        // Receber os dados do formulário
        $nome = $_POST['nome'];
        $cnpj = $_POST['cnpj'];
        $endereco = $_POST['endereco'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        // Preparar a consulta SQL com parâmetros nomeados
        $sql = "INSERT INTO empresa(nome, cnpj, endereco, email, senha) VALUES (:nome, :cnpj, :endereco, :email, :senha)";

        // Preparar a declaração SQL usando PDO para evitar SQL injection
        $stmt = $pdo->prepare($sql);

        // Bind dos parâmetros
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':cnpj', $cnpj);
        $stmt->bindParam(':endereco', $endereco);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha);

        // Executar a consulta
        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Cadastro realizado com sucesso.";
            header("Location: home.php"); 
            exit();
        } else {
            $mensagem = "Erro ao inserir o registro: " . $stmt->errorInfo()[2]; // Mensagem de erro específica do PDO
        }
    } else {
        $mensagem = "Todos os campos do formulário devem ser preenchidos.";
    }
}
?>

<?php include 'header.php'; ?>

<div class="container my-5">
    <h1 class="text-center mb-4">Cadastro de Empresa</h1>
    <?php if (isset($mensagem)): ?>
        <div class="alert <?php echo ($stmt && $stmt->execute()) ? 'alert-success' : 'alert-danger'; ?>" role="alert">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome da Empresa</label>
            <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome da Empresa" required>
        </div>
        <div class="mb-3">
            <label for="cnpj" class="form-label">CNPJ</label>
            <input type="text" class="form-control" id="cnpj" name="cnpj" placeholder="CNPJ" required>
        </div>
        <div class="mb-3">
            <label for="endereco" class="form-label">Endereço</label>
            <input type="text" class="form-control" id="endereco" name="endereco" placeholder="Endereço" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
        </div>
        <div class="mb-3">
            <label for="senha" class="form-label">Senha</label>
            <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Cadastrar</button>
    </form>
</div>

<?php include 'footer.php'; ?>
