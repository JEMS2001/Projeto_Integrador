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

// Inicializar variável de mensagem
$mensagem = '';

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar se todos os campos foram preenchidos
    if (isset($_POST['nome'], $_POST['data_nascimento'], $_POST['telefone'], $_POST['cpf'], $_POST['email'], $_POST['senha'])) {
        // Receber os dados do formulário
        $nome = $_POST['nome'];
        $data_nascimento = $_POST['data_nascimento'];
        $telefone = $_POST['telefone'];
        $cpf = $_POST['cpf'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        // Preparar a consulta SQL com parâmetros nomeados
        $sql = "INSERT INTO membro(nome, data_nascimento, telefone, cpf, email, senha) VALUES (:nome, :data_nascimento, :telefone, :cpf, :email, :senha)";

        // Preparar a declaração SQL usando PDO para evitar SQL injection
        $stmt = $pdo->prepare($sql);

        // Bind dos parâmetros
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':data_nascimento', $data_nascimento);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha);

        // Executar a consulta
        if ($stmt->execute()) {
            $mensagem = "Membro cadastrado com sucesso.";
            header("Location: home.php"); // Redirecionar após o cadastro
            exit(); // Encerrar o script para evitar execução adicional
        } else {
            $mensagem = "Erro ao cadastrar membro: " . $stmt->errorInfo()[2]; // Mensagem de erro específica do PDO
        }
    } else {
        $mensagem = "Todos os campos do formulário devem ser preenchidos.";
    }
}
?>

<?php include 'header.php'; ?>

<div class="container my-5">
    <h1 class="text-center mb-4">Cadastro de Membro</h1>
    <?php if (!empty($mensagem)): ?>
        <div class="alert <?php echo ($stmt && $stmt->execute()) ? 'alert-success' : 'alert-danger'; ?>" role="alert">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome" required>
        </div>
        <div class="mb-3">
            <label for="data_nascimento" class="form-label">Data de Nascimento</label>
            <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" required>
        </div>
        <div class="mb-3">
            <label for="telefone" class="form-label">Telefone</label>
            <input type="tel" class="form-control" id="telefone" name="telefone" placeholder="Telefone" required>
        </div>
        <div class="mb-3">
            <label for="cpf" class="form-label">CPF</label>
            <input type="text" class="form-control" id="cpf" name="cpf" placeholder="CPF" required>
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
