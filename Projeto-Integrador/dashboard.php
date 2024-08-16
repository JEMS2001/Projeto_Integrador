<?php
session_start();

$logado = $_SESSION['email'];
$tabela = $_SESSION['tipo_usuario'];
$id_usuario = $_SESSION['id_' . $tabela];

// Database connection
$pdo = new PDO("mysql:host=localhost;dbname=projeto", "root", "senha_da_nasa");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch user-specific data
if ($tabela == 'empresa') {
    $user = $pdo->query("SELECT * FROM empresa WHERE id_empresa = $id_usuario")->fetch(PDO::FETCH_ASSOC);
    $membros = $pdo->query("SELECT COUNT(*) FROM membro WHERE id_empresa = $id_usuario")->fetchColumn();
    $projetos = $pdo->query("SELECT COUNT(*) FROM projeto WHERE id_empresa = $id_usuario")->fetchColumn();
    $tarefas = $pdo->query("SELECT COUNT(*) FROM tarefa WHERE id_projeto IN (SELECT id_projeto FROM projeto WHERE id_empresa = $id_usuario)")->fetchColumn();
    $membros_online = $pdo->query("SELECT COUNT(*) FROM membro WHERE id_empresa = $id_usuario AND esta_logado = TRUE")->fetchColumn();
    $projetos_atrasados = $pdo->query("SELECT COUNT(*) FROM projeto WHERE id_empresa = $id_usuario AND status = 'atrasado'")->fetchColumn();
    $tarefas_concluidas = $pdo->query("SELECT COUNT(*) FROM tarefa WHERE id_projeto IN (SELECT id_projeto FROM projeto WHERE id_empresa = $id_usuario) AND status = 'Concluída'")->fetchColumn();
    $produtividade = $tarefas > 0 ? round(($tarefas_concluidas / $tarefas) * 100, 2) : 0;
    
    // Próximos prazos de projetos
    $proximos_prazos = $pdo->query("SELECT nome, data_fim FROM projeto WHERE id_empresa = $id_usuario AND status != 'Concluído' ORDER BY data_fim ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    // Membros mais produtivos
    $membros_produtivos = $pdo->query("
        SELECT m.nome, COUNT(t.id_tarefa) as tarefas_concluidas
        FROM membro m
        JOIN tarefa t ON m.id_membro = t.id_membro
        WHERE m.id_empresa = $id_usuario AND t.status = 'Concluída'
        GROUP BY m.id_membro
        ORDER BY tarefas_concluidas DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Distribuição de tarefas por nível de dificuldade
    $tarefas_dificuldade = $pdo->query("
        SELECT nivel_dificuldade, COUNT(*) as count
        FROM tarefa
        WHERE id_projeto IN (SELECT id_projeto FROM projeto WHERE id_empresa = $id_usuario)
        GROUP BY nivel_dificuldade
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Status dos projetos
    $projeto_status = $pdo->query("
        SELECT status, COUNT(*) as count
        FROM projeto
        WHERE id_empresa = $id_usuario
        GROUP BY status
    ")->fetchAll(PDO::FETCH_ASSOC);

} else {
    if ($tabela == 'membro') {
        $user = $pdo->query("SELECT * FROM membro WHERE id_membro = $id_usuario")->fetch(PDO::FETCH_ASSOC);
    } else {
        $user = $pdo->query("SELECT m.*, e.nome as nome_empresa FROM membro m JOIN empresa e ON m.id_empresa = e.id_empresa WHERE m.id_membro = $id_usuario")->fetch(PDO::FETCH_ASSOC);
    }
    $projetos = $pdo->query("SELECT COUNT(*) FROM membro_projeto WHERE id_membro = $id_usuario")->fetchColumn();
    $tarefas = $pdo->query("SELECT COUNT(*) FROM tarefa WHERE id_membro = $id_usuario")->fetchColumn();
    $tarefas_pendentes = $pdo->query("SELECT COUNT(*) FROM tarefa WHERE id_membro = $id_usuario AND status != 'Concluída'")->fetchColumn();
    $tarefas_concluidas = $pdo->query("SELECT COUNT(*) FROM tarefa WHERE id_membro = $id_usuario AND status = 'Concluída'")->fetchColumn();
    $tempo_logado = $pdo->query("SELECT SUM(duracao_segundos) FROM sessao_usuario WHERE id_membro = $id_usuario AND DATE(data_inicio) = CURDATE()")->fetchColumn();
    $tempo_logado_horas = $tempo_logado ? round($tempo_logado / 3600, 2) : 0;
    $produtividade = $tarefas > 0 ? round(($tarefas_concluidas / $tarefas) * 100, 2) : 0;
    
    // Próximas tarefas
    $proximas_tarefas = $pdo->query("
        SELECT t.nome, t.status, p.nome as projeto_nome, p.data_fim
        FROM tarefa t
        JOIN projeto p ON t.id_projeto = p.id_projeto
        WHERE t.id_membro = $id_usuario AND t.status != 'Concluída'
        ORDER BY p.data_fim ASC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Histórico de produtividade (últimos 7 dias)
    $produtividade_semanal = $pdo->query("
        SELECT DATE(data_conclusao) as data, COUNT(*) as tarefas_concluidas
        FROM tarefa
        WHERE id_membro = $id_usuario AND status = 'Concluída' AND data_conclusao >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(data_conclusao)
        ORDER BY data ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Distribuição de tarefas por nível de dificuldade
    $tarefas_dificuldade = $pdo->query("
        SELECT nivel_dificuldade, COUNT(*) as count
        FROM tarefa
        WHERE id_membro = $id_usuario
        GROUP BY nivel_dificuldade
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Status das tarefas
    $tarefa_status = $pdo->query("
        SELECT status, COUNT(*) as count
        FROM tarefa
        WHERE id_membro = $id_usuario
        GROUP BY status
    ")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo $tabela == 'empresa' ? 'Empresa' : 'Membro'; ?></title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="estilo.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            height: 100%;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .chart-container {
            height: 300px;
            max-width: 100%;
        }
        .progress-bar {
            background-color: var(--accent-color);
        }
        .table-responsive {
            max-height: 300px;
            overflow-y: auto;
        }
        @media (max-width: 992px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .content {
                margin-left: 0;
            }
            .chart-container {
                height: 250px;
            }
        }
        @media (max-width: 768px) {
            .chart-container {
                height: 200px;
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
        <a href="dynamic-full-calendar.html">
            <i class="fas fa-calendar-alt me-1"></i>Calendário
        </a>
        <a href="sair.php" class="btn btn-danger mt-auto">
            <i class="fas fa-sign-out-alt me-1"></i>Sair
        </a>
    </div>

    <div class="content">
        <h1 class="mb-4">Olá, <?php echo $user['nome']; ?>!</h1>
        
        <?php if ($tabela == 'empresa'): ?>
        <!-- Dashboard da Empresa -->
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-users"></i> Membros</h5>
                        <p class="card-text display-4"><?php echo $membros; ?></p>
                        <p class="card-text">Online: <?php echo $membros_online; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-project-diagram"></i> Projetos</h5>
                        <p class="card-text display-4"><?php echo $projetos; ?></p>
                        <p class="card-text text-danger">Atrasados: <?php echo $projetos_atrasados; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-tasks"></i> Tarefas</h5>
                        <p class="card-text display-4"><?php echo $tarefas; ?></p>
                        <p class="card-text text-success">Concluídas: <?php echo $tarefas_concluidas; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-chart-line"></i> Produtividade</h5>
                        <p class="card-text display-4"><?php echo $produtividade; ?>%</p>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: <?php echo $produtividade; ?>%" aria-valuenow="<?php echo $produtividade; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Próximos Prazos de Projetos</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Projeto</th>
                                        <th>Data de Conclusão</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($proximos_prazos as $projeto): ?>
                                    <tr>
                                        <td><?php echo $projeto['nome']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($projeto['data_fim'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Membros Mais Produtivos</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Membro</th>
                                        <th>Tarefas Concluídas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($membros_produtivos as $membro): ?>
                                    <tr>
                                        <td><?php echo $membro['nome']; ?></td>
                                        <td><?php echo $membro['tarefas_concluidas']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Dashboard do Membro -->
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Membro</h5>
                        <p class="card-text"><?php echo $user['nome']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-project-diagram"></i> Projetos</h5>
                        <p class="card-text display-4"><?php echo $projetos; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-tasks"></i> Tarefas</h5>
                        <p class="card-text display-4"><?php echo $tarefas; ?></p>
                        <p class="card-text text-warning">Pendentes: <?php echo $tarefas_pendentes; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-chart-line"></i> Produtividade</h5>
                        <p class="card-text display-4"><?php echo $produtividade; ?>%</p>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: <?php echo $produtividade; ?>%" aria-valuenow="<?php echo $produtividade; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Próximas Tarefas</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tarefa</th>
                                        <th>Projeto</th>
                                        <th>Status</th>
                                        <th>Prazo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($proximas_tarefas as $tarefa): ?>
                                    <tr>
                                        <td><?php echo $tarefa['nome']; ?></td>
                                        <td><?php echo $tarefa['projeto_nome']; ?></td>
                                        <td><span class="badge bg-<?php echo $tarefa['status'] == 'Concluída' ? 'success' : 'warning'; ?>"><?php echo $tarefa['status']; ?></span></td>
                                        <td><?php echo date('d/m/Y', strtotime($tarefa['data_fim'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Produtividade Semanal</h5>
                        <div class="chart-container">
                            <canvas id="produtividadeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php echo $tabela == 'empresa' ? 'Status dos Projetos' : 'Status das Tarefas'; ?>
                        </h5>
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Dificuldade das Tarefas</h5>
                        <div class="chart-container">
                            <canvas id="difficultyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Status Chart
        var statusCtx = document.getElementById('statusChart').getContext('2d');
        var statusData = <?php echo json_encode($tabela == 'empresa' ? $projeto_status : $tarefa_status); ?>;
        var statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusData.map(item => item.status),
                datasets: [{
                    data: statusData.map(item => item.count),
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        fontSize: 10
                    }
                },
                title: {
                    display: true,
                    text: '<?php echo $tabela == 'empresa' ? 'Status dos Projetos' : 'Status das Tarefas'; ?>',
                    fontSize: 16
                },
                layout: {
                    padding: {
                        left: 10,
                        right: 10,
                        top: 0,
                        bottom: 0
                    }
                }
            }
        });

        // Difficulty Chart
        var difficultyCtx = document.getElementById('difficultyChart').getContext('2d');
        var difficultyData = <?php echo json_encode($tarefas_dificuldade); ?>;
        var difficultyChart = new Chart(difficultyCtx, {
            type: 'bar',
            data: {
                labels: difficultyData.map(item => item.nivel_dificuldade),
                datasets: [{
                    label: 'Número de Tarefas',
                    data: difficultyData.map(item => item.count),
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                title: {
                    display: true,
                    text: 'Dificuldade das Tarefas',
                    fontSize: 16
                }
            }
        });

        <?php if ($tabela == 'membro'): ?>
        // Produtividade Semanal Chart
        var produtividadeCtx = document.getElementById('produtividadeChart').getContext('2d');
        var produtividadeData = <?php echo json_encode($produtividade_semanal); ?>;
        var produtividadeChart = new Chart(produtividadeCtx, {
            type: 'line',
            data: {
                labels: produtividadeData.map(item => item.data),
                datasets: [{
                    label: 'Tarefas Concluídas',
                    data: produtividadeData.map(item => item.tarefas_concluidas),
                    borderColor: '#4BC0C0',
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                title: {
                    display: true,
                    text: 'Produtividade Semanal',
                    fontSize: 16
                }
            }
        });
        <?php endif; ?>

        // Função para redimensionar os gráficos
        function resizeCharts() {
            [statusChart, difficultyChart].forEach(chart => {
                chart.resize();
            });
            <?php if ($tabela == 'membro'): ?>
            produtividadeChart.resize();
            <?php endif; ?>
        }

        // Chame a função de redimensionamento quando a janela for redimensionada
        window.addEventListener('resize', resizeCharts);
    </script>
</body>
</html>