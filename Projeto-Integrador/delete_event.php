<?php
require 'database_connection.php';

// Obtém o ID do evento enviado via POST
$event_id = $_POST['event_id'];

// Cria a consulta SQL para excluir o evento
$delete_query = "DELETE FROM calendar_event_master WHERE event_id = $event_id";

if (mysqli_query($con, $delete_query)) {
    $data = array(
        'status' => true,
        'msg' => 'Evento excluído com sucesso!'
    );
} else {
    $data = array(
        'status' => false,
        'msg' => 'Desculpe, evento não foi excluído. Erro: ' . mysqli_error($con)
    );
}

echo json_encode($data);
?>