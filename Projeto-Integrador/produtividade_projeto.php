<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['email']) || empty($_SESSION['email']) || $_SESSION['tipo_usuario'] !== 'empresa') {
    header('Location: login.php');
    exit;
}

$logado = $_SESSION['email'];
$id_projeto = $_GET['id_projeto'] ?? null;
$tabela = $_SESSION['tipo_usuario'];

if (!$id_projeto) {
    die("ID do projeto não fornecido.");
}

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get project details
    $stmt = $pdo->prepare("SELECT * FROM projeto WHERE id_projeto = :id_projeto");
    $stmt->execute([':id_projeto' => $id_projeto]);
    $projeto = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get task statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_tasks,
            SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_tasks,
            AVG(DATEDIFF(IFNULL(data_conclusao, CURRENT_DATE), data_criacao)) as avg_completion_time,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks,
            SUM(CASE WHEN status = 'to_do' THEN 1 ELSE 0 END) as to_do_tasks
        FROM tarefa
        WHERE id_projeto = :id_projeto
    ");
    $stmt->execute([':id_projeto' => $id_projeto]);
    $task_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calculate project progress percentage
    $project_progress = ($task_stats['completed_tasks'] / $task_stats['total_tasks']) * 100;

    // Get member productivity (including members without tasks)
    $stmt = $pdo->prepare("
        SELECT 
            m.id_membro,
            m.nome as membro_nome,
            m.imagem as membro_imagem,
            COUNT(t.id_tarefa) as total_tasks,
            SUM(CASE WHEN t.status = 'done' THEN 1 ELSE 0 END) as completed_tasks,
            AVG(DATEDIFF(IFNULL(t.data_conclusao, CURRENT_DATE), t.data_criacao)) as avg_completion_time,
            SUM(CASE WHEN t.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks
        FROM membro m
        LEFT JOIN tarefa t ON m.id_membro = t.id_membro AND t.id_projeto = :id_projeto
        WHERE m.id_membro IN (SELECT id_membro FROM membro_projeto WHERE id_projeto = :id_projeto)
        GROUP BY m.id_membro
        ORDER BY completed_tasks DESC, m.nome ASC
    ");
    $stmt->execute([':id_projeto' => $id_projeto]);
    $member_productivity = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get task distribution by difficulty
    $stmt = $pdo->prepare("
        SELECT 
            nivel_dificuldade,
            COUNT(*) as count
        FROM tarefa
        WHERE id_projeto = :id_projeto
        GROUP BY nivel_dificuldade
    ");
    $stmt->execute([':id_projeto' => $id_projeto]);
    $task_difficulty = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent activities
    $stmt = $pdo->prepare("
        SELECT 
            t.nome as tarefa_nome,
            m.nome as membro_nome,
            pt.status_novo,
            pt.data_atualizacao
        FROM progresso_tarefa pt
        JOIN tarefa t ON pt.id_tarefa = t.id_tarefa
        JOIN membro m ON pt.id_membro = m.id_membro
        WHERE t.id_projeto = :id_projeto
        ORDER BY pt.data_atualizacao DESC
        LIMIT 5
    ");
    $stmt->execute([':id_projeto' => $id_projeto]);
    $recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtividade do Projeto: <?php echo htmlspecialchars($projeto['nome']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="estilo.css">
    <style>

        .content {
            margin-left: 270px;
            padding: 20px;
        }

        .sidebar {
            width: 250px;
            background: var(--primary-color);
            color: #fff;
            position: fixed;
            height: 100%;
            padding-top: 60px;
        }

        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 15px;
            display: block;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background: var(--secondary-color);
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .progress-bar {
            background-color: var(--success-color);
        }

        .member-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }

        .task-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .task-status.done { background-color: var(--success-color); color: #fff; }
        .task-status.in-progress { background-color: var(--warning-color); color: #fff; }
        .task-status.to-do { background-color: var(--danger-color); color: #fff; }

        .animate-fade-in {
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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
        <?php } ?>
        <a href="sair.php" class="btn btn-danger mt-auto">
            <i class="fas fa-sign-out-alt me-1"></i>Sair
        </a>
    </div>

    <div class="content">
        <h2 class="mb-4 animate-fade-in">Produtividade do Projeto: <?php echo htmlspecialchars($projeto['nome']); ?></h2>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card animate-fade-in">
                    <div class="card-body">
                        <h5 class="card-title">Progresso do Projeto</h5>
                        <div class="progress mb-3" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo $project_progress; ?>%;" aria-valuenow="<?php echo $project_progress; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo round($project_progress, 1); ?>%</div>
                        </div>
                        <p>Total de Tarefas: <?php echo $task_stats['total_tasks']; ?></p>
                        <p>Tarefas Concluídas: <?php echo $task_stats['completed_tasks']; ?></p>
                        <p>Tarefas em Andamento: <?php echo $task_stats['in_progress_tasks']; ?></p>
                        <p>Tarefas a Fazer: <?php echo $task_stats['to_do_tasks']; ?></p>
                        <p>Tempo Médio de Conclusão: <?php echo round($task_stats['avg_completion_time'], 2); ?> dias</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card animate-fade-in">
                    <div class="card-body">
                        <h5 class="card-title">Distribuição de Tarefas por Dificuldade</h5>
                        <canvas id="taskDifficultyChart" style="max-height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mb-4 animate-fade-in">Produtividade por Membro</h3>
        <div class="row">
            <?php foreach ($member_productivity as $index => $member): ?>
                <div class="col-md-4 mb-4 animate-fade-in" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title d-flex align-items-center">
                                <?php if (!empty($member['membro_imagem'])): ?>
                                    <img src="<?php echo htmlspecialchars($member['membro_imagem']); ?>" alt="<?php echo htmlspecialchars($member['membro_nome']); ?>" class="member-image">
                                <?php else: ?>
                                    <i class="fas fa-user-circle fa-3x mr-2" style="color: #007bff;"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($member['membro_nome']); ?>
                            </h5>
                            <p>Total de Tarefas: <?php echo $member['total_tasks']; ?></p>
                            <p>Tarefas Concluídas: <?php echo $member['completed_tasks']; ?></p>
                            <p>Tarefas em Andamento: <?php echo $member['in_progress_tasks']; ?></p>
                            <p>Tempo Médio de Conclusão: <?php echo round($member['avg_completion_time'] ?? 0, 2); ?> dias</p>
                            <div class="progress mt-3" style="height: 10px;">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo ($member['total_tasks'] > 0) ? ($member['completed_tasks'] / $member['total_tasks']) * 100 : 0; ?>%;" aria-valuenow="<?php echo ($member['total_tasks'] > 0) ? ($member['completed_tasks'] / $member['total_tasks']) * 100 : 0; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <h3 class="mb-4 animate-fade-in">Atividades Recentes</h3>
        <div class="card animate-fade-in">
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php foreach ($recent_activities as $activity): ?>
                        <li class="list-group-item">
                            <strong><?php echo htmlspecialchars($activity['membro_nome']); ?></strong> 
                            atualizou a tarefa "<?php echo htmlspecialchars($activity['tarefa_nome']); ?>" 
                            para <span class="task-status <?php echo str_replace(' ', '-', strtolower($activity['status_novo'])); ?>"><?php echo htmlspecialchars($activity['status_novo']); ?></span>
                            em <?php echo date('d/m/Y H:i', strtotime($activity['data_atualizacao'])); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Task Difficulty Chart
        var ctx = document.getElementById('taskDifficultyChart').getContext('2d');
        var taskDifficultyChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($task_difficulty, 'nivel_dificuldade')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($task_difficulty, 'count')); ?>,
                    backgroundColor: [
                        'rgba(46, 204, 113, 0.8)',
                        'rgba(243, 156, 18, 0.8)',
                        'rgba(231, 76, 60, 0.8)'
                    ],
                    borderColor: [
                        'rgba(46, 204, 113, 1)',
                        'rgba(243, 156, 18, 1)',
                        'rgba(231, 76, 60, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: true,
                    text: 'Distribuição de Tarefas por Dificuldade'
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });

        // Animate progress bars on scroll
        $(window).scroll(function() {
            $('.progress-bar').each(function() {
                var position = $(this).offset().top;
                var scroll = $(window).scrollTop();
                var windowHeight = $(window).height();
                if (scroll > position - windowHeight + 200) {
                    $(this).css('width', $(this).attr('aria-valuenow') + '%');
                }
            });
        });

        // Tooltip initialization
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })

        // Member productivity chart
        var memberProductivityChart = new Chart(document.getElementById('memberProductivityChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($member_productivity, 'membro_nome')); ?>,
                datasets: [{
                    label: 'Tarefas Concluídas',
                    data: <?php echo json_encode(array_column($member_productivity, 'completed_tasks')); ?>,
                    backgroundColor: 'rgba(46, 204, 113, 0.8)',
                    borderColor: 'rgba(46, 204, 113, 1)',
                    borderWidth: 1
                }, {
                    label: 'Tarefas em Andamento',
                    data: <?php echo json_encode(array_column($member_productivity, 'in_progress_tasks')); ?>,
                    backgroundColor: 'rgba(243, 156, 18, 0.8)',
                    borderColor: 'rgba(243, 156, 18, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Produtividade por Membro'
                    },
                }
            }
        });

        // Add interactivity to member cards
        $('.member-card').on('click', function() {
            $(this).find('.member-details').slideToggle();
        });

        // Add a date range picker for filtering
        $('#dateRange').daterangepicker({
            opens: 'left'
        }, function(start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
            // Here you would typically make an AJAX call to update the dashboard with the new date range
        });

        // Add a search functionality
        $('#searchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $(".member-card").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        // Add export functionality
        $('#exportButton').on('click', function() {
            // Here you would typically implement the logic to export the dashboard data
            alert('Exporting dashboard data...');
        });
    </script>
</body>
</html>