<?php
require 'database_connection.php';

// Obtém os dados enviados via POST e sanitiza
$event_id = intval($_POST['event_id']); // Garante que event_id seja um inteiro
$event_name = mysqli_real_escape_string($con, $_POST['event_name']);
$event_date = mysqli_real_escape_string($con, $_POST['event_date']);
$event_start_time = mysqli_real_escape_string($con, $_POST['event_start_time']);
$event_end_time = mysqli_real_escape_string($con, $_POST['event_end_time']);
$event_platform = mysqli_real_escape_string($con, $_POST['event_platform']);

// Cria a consulta SQL para atualizar o evento usando consultas preparadas
$update_query = "UPDATE calendar_event_master
                 SET event_name = ?, 
                     event_date = ?,
                     event_start_time = ?,
                     event_end_time = ?,
                     event_platform = ?
                 WHERE event_id = ?";

$stmt = mysqli_prepare($con, $update_query);
mysqli_stmt_bind_param($stmt, 'sssssi', $event_name, $event_date, $event_start_time, $event_end_time, $event_platform, $event_id);

if (mysqli_stmt_execute($stmt)) {
    $data = array(
        'status' => true,
        'msg' => 'Evento atualizado com sucesso!'
    );
} else {
    $data = array(
        'status' => false,
        'msg' => 'Desculpe, evento não foi atualizado. Erro: ' . mysqli_error($con)
    );
}

mysqli_stmt_close($stmt);
mysqli_close($con);

echo json_encode($data);
?>