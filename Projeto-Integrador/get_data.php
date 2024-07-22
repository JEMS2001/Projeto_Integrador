<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['id_empresa'])) {
    die(json_encode(['error' => 'ID da empresa não está definido.']));
}

$empresa_id_logada = $_SESSION['id_empresa'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$selected_member = isset($_GET['membro']) ? $_GET['membro'] : '';
$selected_week_start = isset($_GET['semana_inicio']) ? $_GET['semana_inicio'] : date('Y-m-d', strtotime('monday this week'));
$selected_week_end = isset($_GET['semana_fim']) ? $_GET['semana_fim'] : date('Y-m-d', strtotime('sunday this week'));

$where_clauses = ["m.id_empresa = $empresa_id_logada"];

if ($selected_member) {
    $where_clauses[] = "m.nome = '$selected_member'";
}

$where_clauses[] = "DATE(s.data_inicio) BETWEEN '$selected_week_start' AND '$selected_week_end'";

$where_sql = 'WHERE ' . implode(' AND ', $where_clauses);

$sql = "
SELECT 
    m.nome AS membro,
    e.nome AS empresa,
    SUM(s.duracao_segundos) / 3600 AS horas_na_semana
FROM 
    sessao_usuario s
JOIN 
    membro m ON s.id_membro = m.id_membro
JOIN 
    empresa e ON m.id_empresa = e.id_empresa
$where_sql
GROUP BY 
    m.id_membro, e.id_empresa
ORDER BY 
    m.nome;
";

$result = $conn->query($sql);

$presenca = [];
$total_horas_semana = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $presenca[] = $row;
        $total_horas_semana += $row['horas_na_semana'];
    }
}

$conn->close();

echo json_encode(['dados' => $presenca, 'total_horas' => $total_horas_semana]);
?>