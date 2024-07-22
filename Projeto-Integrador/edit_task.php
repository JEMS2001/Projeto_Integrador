<?php
include 'config.php';
// Exibir todos os erros (em ambiente de desenvolvimento)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Capturar todos os erros e convertê-los em JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => "$errstr in $errfile on line $errline"]);
    exit();
});

set_exception_handler(function($exception) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $exception->getMessage()]);
    exit();
});

// Verifique se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Inclua a conexão com o banco de dados
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->beginTransaction();

    try {
        // Verifique se todos os dados esperados estão presentes
        if (!isset($_POST['id_tarefa']) || !isset($_POST['nome_tarefa']) || !isset($_POST['descricao']) || 
            !isset($_POST['status']) || !isset($_POST['id_membro']) || !isset($_POST['nivel_dificuldade']) || 
            !isset($_POST['tempo_estimado'])) {
            echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
            exit();
        }

        // Recupere os dados do formulário
        $id_tarefa = $_POST['id_tarefa'];
        $nome_tarefa = $_POST['nome_tarefa'];
        $descricao = $_POST['descricao'];
        $status = $_POST['status'];
        $id_membro = $_POST['id_membro'];
        $nivel_dificuldade = $_POST['nivel_dificuldade'];
        $tempo_estimado = $_POST['tempo_estimado'];  // Em dias
        $tempo_gasto = $_POST['tempo_gasto'] ?? null; // Em minutos
        $data_conclusao = null;

        // Atualize a data de conclusão se o status for "done"
        if ($status === 'done') {
            $data_conclusao = date('Y-m-d H:i:s');
        }

        // Prepare a consulta SQL para atualização
        $sql = "UPDATE tarefa SET 
                    nome = ?, 
                    descricao = ?, 
                    status = ?, 
                    id_membro = ?, 
                    nivel_dificuldade = ?, 
                    tempo_estimado = ?, 
                    tempo_gasto = ?,
                    data_conclusao = ?
                WHERE id_tarefa = ?";

        if ($stmt = $conn->prepare($sql)) {
            // Vincule os parâmetros
            $stmt->bind_param(
                'sssiiissi', 
                $nome_tarefa, 
                $descricao, 
                $status, 
                $id_membro, 
                $nivel_dificuldade, 
                $tempo_estimado, 
                $tempo_gasto,
                $data_conclusao,
                $id_tarefa
            );

            // Execute a consulta
            if ($stmt->execute()) {
                // Retorne um JSON de sucesso
                echo json_encode(['success' => true]);
            } else {
                // Retorne um JSON de erro
                echo json_encode(['success' => false, 'error' => 'Erro ao executar consulta: ' . $stmt->error]);
            }

            // Feche a declaração
            $stmt->close();
        } else {
            // Retorne um JSON de erro
            echo json_encode(['success' => false, 'error' => 'Erro ao preparar consulta: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Exceção capturada: ' . $e->getMessage()]);
    }

    // Feche a conexão
    $conn->close();
} else {
    // Retorne um JSON de erro se não for uma requisição POST
    echo json_encode(['success' => false, 'error' => 'Método de requisição inválido']);
}
?>
