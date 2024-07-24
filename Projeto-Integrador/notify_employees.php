<?php
require 'database_connection.php';
require 'PHPMailer/PHPMailerAutoload.php'; // Biblioteca PHPMailer

// Definir o intervalo de tempo para notificações (5 horas antes da reunião)
$interval = new DateInterval('PT5H');
$now = new DateTime();
$notification_time = $now->add($interval);

// Converter para o formato compatível com o banco de dados
$notification_time_str = $notification_time->format('Y-m-d H:i:s');

// Consulta para selecionar eventos que precisam de notificação
$select_query = "
    SELECT e.event_name, e.event_start_time, e.event_date, e.event_platform, e.event_id, emp.employee_email
    FROM calendar_event_master e
    JOIN meeting_participants mp ON e.event_id = mp.meeting_id
    JOIN employees emp ON mp.employee_id = emp.employee_id
    WHERE CONCAT(e.event_date, ' ', e.event_start_time) = ?
";

$stmt = $con->prepare($select_query);
$stmt->bind_param('s', $notification_time_str);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $event_name = $row['event_name'];
        $event_start_time = $row['event_start_time'];
        $event_date = $row['event_date'];
        $event_platform = $row['event_platform'];
        $employee_email = $row['employee_email'];

        // Configuração do e-mail
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.your-email-provider.com'; // Servidor SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@example.com'; // Seu e-mail
        $mail->Password = 'your-email-password'; // Sua senha
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('your-email@example.com', 'Seu Nome');
        $mail->addAddress($employee_email);

        $mail->isHTML(true);
        $mail->Subject = 'Notificação de Reunião';
        $mail->Body = "
            <p>Prezado(a) Colaborador(a),</p>
            <p>Você tem uma reunião agendada para <b>{$event_date} às {$event_start_time}</b> na plataforma <b>{$event_platform}</b>.</p>
            <p>Nome da Reunião: <b>{$event_name}</b></p>
            <p>Por favor, esteja preparado.</p>
            <p>Atenciosamente,</p>
            <p>Seu Nome</p>
        ";

        if (!$mail->send()) {
            echo 'Mensagem não enviada. Erro: ' . $mail->ErrorInfo;
        } else {
            echo 'Mensagem enviada para ' . $employee_email . '<br>';
        }
    }
} else {
    echo 'Nenhum evento para notificar.';
}

$stmt->close();
$con->close();
?>