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

        $pdo->beginTransaction();

        // Verificar se o usuário tem permissão para excluir a tarefa
        if ($_SESSION['tipo_usuario'] == 'empresa') {
            $stmt = $pdo->prepare("
                SELECT t.id_tarefa 
                FROM tarefa t 
                INNER JOIN projeto p ON t.id_projeto = p.id_projeto 
                INNER JOIN empresa e ON p.id_empresa = e.id_empresa 
                WHERE t.id_tarefa = :id_tarefa AND e.email = :email
            ");
            $stmt->execute([':id_tarefa' => $id_tarefa, ':email' => $_SESSION['email']]);
        } else {
            $stmt = $pdo->prepare("
                SELECT t.id_tarefa 
                FROM tarefa t 
                INNER JOIN membro m ON t.id_membro = m.id_membro 
                WHERE t.id_tarefa = :id_tarefa AND m.email = :email
            ");
            $stmt->execute([':id_tarefa' => $id_tarefa, ':email' => $_SESSION['email']]);
        }

        if (!$stmt->fetch()) {
            throw new Exception("Você não tem permissão para excluir esta tarefa.");
        }

        // Excluir registros relacionados na tabela progresso_tarefa
        $stmt = $pdo->prepare("DELETE FROM progresso_tarefa WHERE id_tarefa = :id_tarefa");
        $stmt->execute([':id_tarefa' => $id_tarefa]);

        // Excluir a tarefa
        $stmt = $pdo->prepare("DELETE FROM tarefa WHERE id_tarefa = :id_tarefa");
        $stmt->execute([':id_tarefa' => $id_tarefa]);

        $pdo->commit();

        echo json_encode(['success' => 'Tarefa excluída com sucesso']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['error' => 'Erro ao excluir tarefa: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Método não permitido ou ID da tarefa não fornecido']);
}
?>