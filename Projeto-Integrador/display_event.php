<?php
require 'database_connection.php';

// Consulta para selecionar eventos da tabela 'calendar_event_master'
$display_query = "SELECT event_id, event_name, CONCAT(event_date, 'T', event_start_time) AS start, 
                         CONCAT(event_date, 'T', event_end_time) AS end, event_platform AS platform 
                  FROM calendar_event_master";
$results = mysqli_query($con, $display_query);

$count = mysqli_num_rows($results);

if ($count > 0) {
    $data_arr = array();
    while ($data_row = mysqli_fetch_assoc($results)) {
        $data_arr[] = array(
            'event_id' => $data_row['event_id'],
            'title' => $data_row['event_name'],
            'start' => $data_row['start'],
            'end' => $data_row['end'],
            'platform' => $data_row['platform'],
            'backgroundColor' => '#' . substr(uniqid(), -6) // Gera uma cor única para cada evento
        );
    }

    $data = array(
        'status' => true,
        'msg' => 'sucesso!',
        'data' => $data_arr
    );
} else {
    $data = array(
        'status' => false,
        'msg' => 'Nenhum evento encontrado.'
    );
}

echo json_encode($data);
?>