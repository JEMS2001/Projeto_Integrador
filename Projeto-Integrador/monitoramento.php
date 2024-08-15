<?php
session_start();

include_once('config.php');

if (!isset($_SESSION['id_empresa'])) {
    die("ID da empresa não está definido. Por favor, faça login.");
}

$empresa_id_logada = $_SESSION['id_empresa'];
$tabela = $_SESSION['tipo_usuario'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$current_week_start = date('Y-m-d', strtotime('monday this week'));
$current_week_end = date('Y-m-d', strtotime('sunday this week'));

$selected_member = isset($_GET['membro']) ? $_GET['membro'] : '';
$selected_week_start = isset($_GET['semana_inicio']) ? $_GET['semana_inicio'] : $current_week_start;
$selected_week_end = isset($_GET['semana_fim']) ? $_GET['semana_fim'] : $current_week_end;

$membros_sql = "SELECT nome FROM membro WHERE id_empresa = $empresa_id_logada";
$membros_result = $conn->query($membros_sql);
$membros = [];
if ($membros_result->num_rows > 0) {
    while ($row = $membros_result->fetch_assoc()) {
        $membros[] = $row['nome'];
    }
}

$conn->close();

function formatHoursMinutes($hours) {
    $hours = floatval($hours);
    $h = floor($hours);
    $m = round(($hours - $h) * 60);
    return sprintf("%02d:%02d", $h, $m);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Monitoramento de Presença</title>
    <link rel="stylesheet" href="estilo.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        h1 {
            color: #007bff;
            text-align: center;
            margin-bottom: 30px;
        }
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
        }
        .filters .form-group {
            flex: 1;
            min-width: 200px;
        }
        .total-hours {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
            color: #28a745;
        }
        .chart-container {
            position: relative;
            margin: auto;
            height: 400px;
            width: 100%;
        }
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255,255,255,0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        #tabela-presenca {
            width: 100% !important;
        }
        .feedback {
            text-align: center;
            font-size: 18px;
            margin: 20px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .feedback-success {
            background-color: #d4edda;
            color: #155724;
        }
        .feedback-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .feedback-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .sidebar {
            background-color: var(--secondary-color);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            padding-top: 20px;
            z-index: 1000;
        }
        .sidebar.active {
            left: 0;
        }
        .sidebar a {
            color: var(--text-color);
            text-decoration: none;
            padding: 15px;
            display: block;
            transition: 0.3s;
        }
        .sidebar a:hover {
            background: var(--secondary-color);
        }
        .navbar-brand {
            color: var(--accent-color) !important;
        }
        #sidebarCollapse {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
        }
        @media (min-width: 768px) {
            .sidebar {
                left: 0;
            }
            #sidebarCollapse {
                display: none;
            }
        }
    </style>
</head>
<body>
    
<div class="sidebar">
        <a class="navbar-brand d-flex align-items-center justify-content-center" href="home.php">
            <i class="fas fa-code me-2"></i>JFHK
        </a>

        <a href="projeto.php">
            <i class="fas fa-project-diagram me-1"></i>Projetos
        </a>
        <a href="perfil.php">
            <i class="fas fa-user me-1"></i>Perfil
        </a>
        <?php if ($tabela == 'empresa') { ?>
            <a href="membro_empresa.php">
                <i class="fas fa-users me-1"></i>Membros
            </a>
            <a href="monitoramento.php">
                <i class="fas fa-chart-bar me-1"></i>Relatórios
            </a>
            <?php }else{ ?>

            <a class="nav-link" href="notificacao.php">
                <i class="fas fa-users me-1"></i>Notificações
            </a>

        <?php } ?>
            <i class="fas fa-calendar-alt me-1"></i>Calendário
        </a>
        <a href="sair.php" class="btn btn-danger mt-auto">
            <i class="fas fa-sign-out-alt me-1"></i>Sair
        </a>
    </div>


    <div class="container">
        <h1><i class="fas fa-chart-line"></i> Relatório de Monitoramento de Presença</h1>
        
        <form id="filtro-form" class="filters">
            <div class="form-group">
                <label for="membro"><i class="fas fa-user"></i> Membro:</label>
                <select class="form-control" id="membro" name="membro">
                    <option value="">Todos</option>
                    <?php foreach ($membros as $membro): ?>
                        <option value="<?php echo $membro; ?>" <?php echo ($selected_member == $membro) ? 'selected' : ''; ?>><?php echo $membro; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="semana_inicio"><i class="fas fa-calendar-alt"></i> Semana Início:</label>
                <input type="date" class="form-control" id="semana_inicio" name="semana_inicio" value="<?php echo $selected_week_start; ?>">
            </div>
            <div class="form-group">
                <label for="semana_fim"><i class="fas fa-calendar-alt"></i> Semana Fim:</label>
                <input type="date" class="form-control" id="semana_fim" name="semana_fim" value="<?php echo $selected_week_end; ?>">
            </div>
            <div class="form-group">
                <label for="tipo_grafico"><i class="fas fa-chart-bar"></i> Tipo de Gráfico:</label>
                <select class="form-control" id="tipo_grafico" name="tipo_grafico">
                    <option value="bar" selected>Barra</option>
                    <option value="line">Linha</option>
                    <option value="pie">Pizza</option>
                </select>
            </div>
        </form>

        <div id="feedback" class="feedback" style="display: none;"></div>

        <div class="total-hours">
            <i class="fas fa-clock"></i> Total de horas na semana: <span id="total-horas">00:00</span>
        </div>

        <div class="chart-container">
            <canvas id="chart"></canvas>
        </div>
    </div>

    <div class="container">
        <table id="tabela-presenca" class="display table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Membro</th>
                    <th>Empresa</th>
                    <th>Horas na Semana</th>
                </tr>
            </thead>
            <tbody>
                <!-- Os dados serão preenchidos via JavaScript -->
            </tbody>
        </table>
    </div>

    <div id="loading" class="loading" style="display: none;">
        <div class="loading-spinner"></div>
    </div>

    <script>
        $(document).ready(function() {
            var dataTable = $('#tabela-presenca').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json'
                },
                columns: [
                    { data: 'membro' },
                    { data: 'empresa' },
                    { 
                        data: 'horas_na_semana',
                        render: function(data) {
                            return formatHoursMinutes(parseFloat(data));
                        }
                    }
                ]
            });
            var chart;

            function formatHoursMinutes(hours) {
                var h = Math.floor(hours);
                var m = Math.round((hours - h) * 60);
                return (h < 10 ? "0" : "") + h + ":" + (m < 10 ? "0" : "") + m;
            }

            function showLoading() {
                $('#loading').show();
            }

            function hideLoading() {
                $('#loading').hide();
            }

            function showFeedback(message, type) {
                var feedbackDiv = $('#feedback');
                feedbackDiv.removeClass('feedback-success feedback-warning feedback-error')
                           .addClass('feedback-' + type)
                           .html(message)
                           .show();
            }

            function atualizarDados() {
                showLoading();
                $.ajax({
                    url: 'get_data.php',
                    method: 'GET',
                    data: $('#filtro-form').serialize(),
                    dataType: 'json',
                    success: function(response) {
                        hideLoading();
                        if (response.dados && response.dados.length > 0) {
                            dataTable.clear().rows.add(response.dados).draw();
                            $('#total-horas').text(formatHoursMinutes(parseFloat(response.total_horas)));
                            atualizarGrafico(response.dados);
                            showFeedback('Dados atualizados com sucesso!', 'success');
                        } else {
                            dataTable.clear().draw();
                            $('#total-horas').text('00:00');
                            if (chart) {
                                chart.destroy();
                                chart = null;
                            }
                            showFeedback('Nenhum dado encontrado para os filtros selecionados.', 'warning');
                        }
                    },
                    error: function() {
                        hideLoading();
                        showFeedback('Erro ao carregar os dados. Por favor, tente novamente.', 'error');
                    }
                });
            }

            function atualizarGrafico(dados) {
                var labels = dados.map(function(e) { return e.membro; });
                var data = dados.map(function(e) { return parseFloat(e.horas_na_semana); });
                var tipoGrafico = $('#tipo_grafico').val();

                if (chart) {
                    chart.destroy();
                }

                var ctx = document.getElementById('chart').getContext('2d');
                chart = new Chart(ctx, {
                    type: tipoGrafico,
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Horas na Semana',
                            data: data,
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.2)',
                                'rgba(54, 162, 235, 0.2)',
                                'rgba(255, 206, 86, 0.2)',
                                'rgba(75, 192, 192, 0.2)',
                                'rgba(153, 102, 255, 0.2)',
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return formatHoursMinutes(value);
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: tipoGrafico === 'pie'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += formatHoursMinutes(context.parsed.y);
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            $('#filtro-form select, #filtro-form input').change(atualizarDados);
            atualizarDados();
        });
    </script>
</body>
</html>