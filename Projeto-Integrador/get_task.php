<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_tarefa'])) {
    $id_tarefa = $_POST['id_tarefa'];

    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("
            SELECT t.*, m.nome as membro_nome 
            FROM tarefa t 
            LEFT JOIN membro m ON t.id_membro = m.id_membro 
            WHERE t.id_tarefa = :id_tarefa
        ");
        $stmt->execute([':id_tarefa' => $id_tarefa]);
        $tarefa = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($tarefa) {
            // Adicionar informações de progresso
            $stmt = $pdo->prepare("
                SELECT * FROM progresso_tarefa 
                WHERE id_tarefa = :id_tarefa 
                ORDER BY data_atualizacao DESC 
                LIMIT 1
            ");
            $stmt->execute([':id_tarefa' => $id_tarefa]);
            $progresso = $stmt->fetch(PDO::FETCH_ASSOC);

            $tarefa['ultimo_progresso'] = $progresso;

            echo json_encode($tarefa);
        } else {
            echo json_encode(['error' => 'Tarefa não encontrada']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erro ao buscar tarefa: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Método não permitido ou ID da tarefa não fornecido']);
}
?>