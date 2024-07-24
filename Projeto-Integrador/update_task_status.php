<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_tarefa = $_POST['id_tarefa'];
    $new_status = $_POST['status'];
    $old_status = $_POST['old_status'];

    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->beginTransaction();

        // Atualizar o status da tarefa
        $stmt = $pdo->prepare("UPDATE tarefa SET status = :status WHERE id_tarefa = :id_tarefa");
        $stmt->execute([':status' => $new_status, ':id_tarefa' => $id_tarefa]);

        // Obter o id_membro associado à tarefa
        $stmt = $pdo->prepare("SELECT id_membro FROM tarefa WHERE id_tarefa = :id_tarefa");
        $stmt->execute([':id_tarefa' => $id_tarefa]);
        $id_membro = $stmt->fetchColumn();

        // Registrar o progresso
        $stmt = $pdo->prepare("INSERT INTO progresso_tarefa (id_tarefa, id_membro, status_anterior, status_novo, tempo_gasto) VALUES (:id_tarefa, :id_membro, :status_anterior, :status_novo, 0)");
        $stmt->execute([
            ':id_tarefa' => $id_tarefa,
            ':id_membro' => $id_membro,
            ':status_anterior' => $old_status,
            ':status_novo' => $new_status
        ]);

        // Se o novo status for 'done', atualize a data de conclusão da tarefa
        if ($new_status == 'done') {
            $stmt = $pdo->prepare("UPDATE tarefa SET data_conclusao = CURRENT_TIMESTAMP WHERE id_tarefa = :id_tarefa");
            $stmt->execute([':id_tarefa' => $id_tarefa]);
        }

        $pdo->commit();

        echo json_encode(['success' => 'Status atualizado com sucesso']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['error' => 'Erro ao atualizar status: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Método não permitido']);
}
?>