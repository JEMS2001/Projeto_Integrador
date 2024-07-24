<?php
require 'database_connection.php';

// Recebendo e sanitizando dados do formulário
$event_name = mysqli_real_escape_string($con, $_POST['event_name']);
$event_date = mysqli_real_escape_string($con, $_POST['event_date']);
$event_start_time = mysqli_real_escape_string($con, $_POST['event_start_time']);
$event_end_time = mysqli_real_escape_string($con, $_POST['event_end_time']);
$event_platform = mysqli_real_escape_string($con, $_POST['event_platform']);

// Prepare a consulta SQL
$insert_query = "INSERT INTO calendar_event_master (event_name, event_date, event_start_time, event_end_time, event_platform) 
                 VALUES (?, ?, ?, ?, ?)";

// Use consultas preparadas para maior segurança
$stmt = mysqli_prepare($con, $insert_query);

if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars(mysqli_error($con)));
}

mysqli_stmt_bind_param($stmt, 'sssss', $event_name, $event_date, $event_start_time, $event_end_time, $event_platform);

if (mysqli_stmt_execute($stmt)) {
    $data = array(
        'status' => true,
        'msg' => 'Evento adicionado com sucesso!'
    );
} else {
    $data = array(
        'status' => false,
        'msg' => 'Desculpe, evento não foi adicionado. Erro: ' . htmlspecialchars(mysqli_stmt_error($stmt))
    );
}

mysqli_stmt_close($stmt);
mysqli_close($con);

echo json_encode($data);
?>