<?php
session_start();
include 'config.php'; // Certifique-se de que este arquivo contém a conexão com o banco de dados

// Verifica se é um membro logado e se há uma sessão de monitoramento ativa
if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] == 'membro' && isset($_SESSION['id_membro']) && isset($_SESSION['id_sessao'])) {
    try {
        // Finaliza a sessão de monitoramento
        $sql = "UPDATE sessao_usuario 
                SET data_fim = NOW(), 
                    duracao_segundos = TIMESTAMPDIFF(SECOND, data_inicio, NOW()) 
                WHERE id_sessao = :id_sessao";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_sessao', $_SESSION['id_sessao']);
        $stmt->execute();

        // Atualiza o status de login do membro
        $sql_update = "UPDATE membro SET esta_logado = FALSE WHERE id_membro = :id_membro";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->bindParam(':id_membro', $_SESSION['id_membro']);
        $stmt_update->execute();
    } catch (PDOException $e) {
        // Log do erro, você pode querer registrar isso em um arquivo de log
        error_log("Erro no logout: " . $e->getMessage());
    }
}

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Se for necessário matar a sessão completamente, remova também o cookie da sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir a sessão
session_destroy();

// Redirecionar para a página de login
header("Location: login.php");
exit();
?>