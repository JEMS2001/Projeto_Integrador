<?php
include 'config.php';
// Display all errors (in development environment)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Capture all errors and convert them to JSON
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

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Include database connection
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->beginTransaction();

        // Check if all expected data is present
        $required_fields = ['id_tarefa', 'nome_tarefa', 'descricao', 'status', 'id_membro', 'nivel_dificuldade', 'tempo_estimado'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field])) {
                throw new Exception("Campo obrigatório ausente: $field");
            }
        }

        // Retrieve form data
        $id_tarefa = $_POST['id_tarefa'];
        $nome_tarefa = $_POST['nome_tarefa'];
        $descricao = $_POST['descricao'];
        $status = $_POST['status'];
        $id_membro = $_POST['id_membro'];
        $nivel_dificuldade = $_POST['nivel_dificuldade'];
        $tempo_estimado = $_POST['tempo_estimado'];  // In days

        $data_conclusao = ($status === 'done') ? date('Y-m-d H:i:s') : null;

        // Prepare SQL query for update
        $sql = "UPDATE tarefa SET 
                    nome = :nome, 
                    descricao = :descricao, 
                    status = :status, 
                    id_membro = :id_membro, 
                    nivel_dificuldade = :nivel_dificuldade, 
                    tempo_estimado = :tempo_estimado, 
                    data_conclusao = :data_conclusao
                WHERE id_tarefa = :id_tarefa";

        $stmt = $pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':nome', $nome_tarefa);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id_membro', $id_membro, PDO::PARAM_INT);
        $stmt->bindParam(':nivel_dificuldade', $nivel_dificuldade); 
        $stmt->bindParam(':tempo_estimado', $tempo_estimado, PDO::PARAM_INT);
        $stmt->bindParam(':data_conclusao', $data_conclusao);
        $stmt->bindParam(':id_tarefa', $id_tarefa, PDO::PARAM_INT);

        // Execute the query
        $stmt->execute();

        $pdo->commit();

        // Return success JSON
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Erro: ' . $e->getMessage()]);
    }
} else {
    // Return error JSON if not a POST request
    echo json_encode(['success' => false, 'error' => 'Método de requisição inválido']);
}
?>