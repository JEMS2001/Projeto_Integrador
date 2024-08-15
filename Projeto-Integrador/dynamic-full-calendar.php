<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    unset($_SESSION['email']);
    unset($_SESSION['senha']);
    header('Location: login.php');
    exit;
}

$tabela = $_SESSION['tipo_usuario'];
$logado = $_SESSION['email'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$id_empresa = $_SESSION['id_empresa'];

$message = '';

function getConnection($servername, $dbname, $username, $password) {
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Erro na conexão: " . $e->getMessage());
    }
}

function getCompanyName($pdo, $id_empresa) {
    $sql_empresa = "SELECT nome FROM empresa WHERE id_empresa = :id_empresa";
    $stmt_empresa = $pdo->prepare($sql_empresa);
    $stmt_empresa->bindParam(':id_empresa', $id_empresa);
    $stmt_empresa->execute();
    return $stmt_empresa->fetch(PDO::FETCH_ASSOC);
}

function checkExistingNotification($pdo, $id_empresa, $cpf_membro) {
    $sql_check_notificacao = "SELECT * FROM notificacao WHERE id_empresa = :id_empresa AND cpf_membro = :cpf_membro";
    $stmt_check_notificacao = $pdo->prepare($sql_check_notificacao);
    $stmt_check_notificacao->bindParam(':id_empresa', $id_empresa);
    $stmt_check_notificacao->bindParam(':cpf_membro', $cpf_membro);
    $stmt_check_notificacao->execute();
    return $stmt_check_notificacao->fetch(PDO::FETCH_ASSOC);
}

function insertNotification($pdo, $id_empresa, $nome_empresa, $cpf_membro) {
    $sql_insert_notificacao = "INSERT INTO notificacao (id_empresa, nome, cpf_membro) VALUES (:id_empresa, :nome, :cpf_membro)";
    $stmt_insert_notificacao = $pdo->prepare($sql_insert_notificacao);
    $stmt_insert_notificacao->bindParam(':id_empresa', $id_empresa);
    $stmt_insert_notificacao->bindParam(':nome', $nome_empresa);
    $stmt_insert_notificacao->bindParam(':cpf_membro', $cpf_membro);
    $stmt_insert_notificacao->execute();
}

function addMemberToCompany($pdo, $cpf_membro, $id_empresa) {
    global $message;
    
    $empresa = getCompanyName($pdo, $id_empresa);
    if (!$empresa) {
        $message = '<div class="alert alert-danger">Empresa não encontrada.</div>';
        return;
    }
    
    $nome_empresa = $empresa['nome'];
    
    $sql_membro = "SELECT id_membro FROM membro WHERE cpf = :cpf";
    $stmt_membro = $pdo->prepare($sql_membro);
    $stmt_membro->bindParam(':cpf', $cpf_membro);
    $stmt_membro->execute();
    $membro = $stmt_membro->fetch(PDO::FETCH_ASSOC);

    if ($membro) {
        $notificacao_existente = checkExistingNotification($pdo, $id_empresa, $cpf_membro);

        if ($notificacao_existente) {
            $message = '<div class="alert alert-warning">Convite já foi feito para este membro.</div>';
        } else {
            insertNotification($pdo, $id_empresa, $nome_empresa, $cpf_membro);
            $message = '<div class="alert alert-success">Notificação adicionada ao membro com sucesso.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">CPF de membro não encontrado.</div>';
    }
}

function getMembers($pdo, $id_empresa) {
    $sql = "SELECT * FROM membro WHERE id_empresa = :id_empresa";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_empresa', $id_empresa);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$pdo = getConnection($servername, $dbname, $username, $password);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cpf_membro'])) {
    $cpf_membro = $_POST['cpf_membro'];
    addMemberToCompany($pdo, $cpf_membro, $id_empresa);
}

$members = getMembers($pdo, $id_empresa);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendário de Eventos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/pt-br.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="estilo.css">
    <style>
.sidebar {
    background-color: var(--primary-color);
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    width: 250px; /* Largura da sidebar */
    padding-top: 20px;
    transform: translateX(0); /* Sempre visível em desktop */
    transition: transform 0.3s ease; /* Adiciona transição para efeitos visuais */
    z-index: 1000;
}

/* Estilo para o conteúdo */
.container {
    margin-left: 250px; /* Margem para compensar a largura da sidebar */
    padding: 20px;
    max-width: calc(100% - 250px); /* Largura máxima para o conteúdo */
}

/* Estilo para o botão que aparece em dispositivos móveis */
.sidebar-toggle {
    display: none;
    position: fixed;
    top: 10px;
    left: 10px;
    z-index: 1100;
}

/* Mostrar botão e esconder a sidebar em dispositivos móveis */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-250px); /* Sidebar escondida por padrão */
    }

    .sidebar.active {
        transform: translateX(0); /* Sidebar visível quando ativa */
    }

    .sidebar-toggle {
        display: block; /* Mostrar botão */
    }

    .container {
        margin-left: 0; /* Sem margem extra */
        max-width: 100%; /* Largura total do container */
    }
}

/* Estilo para o botão de hambúrguer */
.sidebar-toggle {
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 5px;
    padding: 10px 15px;
    cursor: pointer;
}

/* Estilo para o calendário */
#calendar {
    width: 100%; /* Largura total do calendário */
    max-width: 800px; /* Limite de largura do calendário */
    margin: 0 auto; /* Centraliza o calendário horizontalmente */
}

/* Estilos para os botões */
.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
}

.btn-warning:hover {
    background-color: #e0a800;
    border-color: #d39e00;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

    </style>
</head>
<body>

    <!-- Botão para mostrar/ocultar a sidebar -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

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
        <a href="dynamic-full-calendar.php">
            <i class="fas fa-calendar-alt me-1"></i>Calendário
        </a>
        <a href="sair.php" class="btn btn-danger mt-auto">
            <i class="fas fa-sign-out-alt me-1"></i>Sair
        </a>
    </div>

<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <h3 class="header-title">Calendário de Eventos</h3>
            <!-- Botões para adicionar, editar e excluir eventos -->
            <button class="btn btn-primary" data-toggle="modal" data-target="#event_entry_modal">Adicionar Evento</button>
            <br><br>
            <button class="btn btn-warning" id="edit_event_button" style="display:none;" data-toggle="modal" data-target="#event_edit_modal">Editar Evento</button>
            <button class="btn btn-danger" id="delete_event_button" style="display:none;">Excluir Evento</button>
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Modal para adicionar evento -->
<div class="modal fade" id="event_entry_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Adicionar Novo Evento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="event_form">
                    <div class="form-group">
                        <label for="event_name">Nome do Evento</label>
                        <input type="text" name="event_name" id="event_name" class="form-control" placeholder="Digite o nome do evento" required>
                    </div>
                    <div class="form-group">
                        <label for="event_date">Data do Evento</label>
                        <input type="date" name="event_date" id="event_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="event_start_time">Hora de Início</label>
                        <input type="time" name="event_start_time" id="event_start_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="event_end_time">Hora de Término</label>
                        <input type="time" name="event_end_time" id="event_end_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="event_platform">Plataforma</label>
                        <input type="text" name="event_platform" id="event_platform" class="form-control" placeholder="Digite a plataforma" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="save_event()">Salvar Evento</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar evento -->
<div class="modal fade" id="event_edit_modal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Editar Evento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit_event_form">
                    <input type="hidden" id="edit_event_id">
                    <div class="form-group">
                        <label for="edit_event_name">Nome do Evento</label>
                        <input type="text" name="event_name" id="edit_event_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_event_date">Data do Evento</label>
                        <input type="date" name="event_date" id="edit_event_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_event_start_time">Hora de Início</label>
                        <input type="time" name="event_start_time" id="edit_event_start_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_event_end_time">Hora de Término</label>
                        <input type="time" name="event_end_time" id="edit_event_end_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_event_platform">Plataforma</label>
                        <input type="text" name="event_platform" id="edit_event_platform" class="form-control" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="update_event()">Atualizar Evento</button>
                <button type="button" class="btn btn-danger" onclick="delete_event()">Excluir Evento</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        editable: true,
        locale: 'pt-br', // Configura a localização para português
        eventSources: [
            {
                url: 'display_event.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.status) {
                        return data.data;
                    } else {
                        console.log('Erro: ' + data.msg);
                        return [];
                    }
                },
                error: function(xhr, status) {
                    console.log('Erro ao carregar eventos: ' + xhr.statusText);
                }
            }
        ],
        selectable: true,
        selectHelper: true,
        select: function(start, end) {
            $('#event_entry_modal').modal('show');
            $('#event_date').val(moment(start).format('YYYY-MM-DD'));
            $('#event_start_time').val(moment(start).format('HH:mm'));
            $('#event_end_time').val(moment(end).format('HH:mm'));
        },
        eventClick: function(event) {
            $('#edit_event_id').val(event.event_id);
            $('#edit_event_name').val(event.title);
            $('#edit_event_date').val(moment(event.start).format('YYYY-MM-DD'));
            $('#edit_event_start_time').val(moment(event.start).format('HH:mm'));
            $('#edit_event_end_time').val(moment(event.end).format('HH:mm'));
            $('#edit_event_platform').val(event.platform);
            $('#event_edit_modal').modal('show');
            $('#edit_event_button').show(); // Mostra o botão de edição
            $('#delete_event_button').show(); // Mostra o botão de exclusão
        }
    });
});

function save_event() {
    var event_name = $("#event_name").val();
    var event_date = $("#event_date").val();
    var event_start_time = $("#event_start_time").val();
    var event_end_time = $("#event_end_time").val();
    var event_platform = $("#event_platform").val();

    if (!event_name || !event_date || !event_start_time || !event_end_time || !event_platform) {
        alert("Por favor, preencha todos os detalhes obrigatórios.");
        return false;
    }

    $.ajax({
        url: 'save_event.php',
        type: 'POST',
        dataType: 'json',
        data: {
            event_name: event_name,
            event_date: event_date,
            event_start_time: event_start_time,
            event_end_time: event_end_time,
            event_platform: event_platform
        },
        success: function(response) {
            $('#event_entry_modal').modal('hide');
            if (response.status) {
                alert(response.msg);
                $('#calendar').fullCalendar('refetchEvents'); // Recarrega os eventos
            } else {
                alert(response.msg);
            }
        },
        error: function(xhr, status) {
            console.log('Erro ao adicionar evento: ' + xhr.statusText);
            alert('Erro ao adicionar o evento.');
        }
    });

    return false;
}

function update_event() {
    var event_id = $("#edit_event_id").val();
    var event_name = $("#edit_event_name").val();
    var event_date = $("#edit_event_date").val();
    var event_start_time = $("#edit_event_start_time").val();
    var event_end_time = $("#edit_event_end_time").val();
    var event_platform = $("#edit_event_platform").val();

    if (!event_name || !event_date || !event_start_time || !event_end_time || !event_platform) {
        alert("Por favor, preencha todos os detalhes obrigatórios.");
        return false;
    }

    $.ajax({
        url: 'update_event.php',
        type: 'POST',
        dataType: 'json',
        data: {
            event_id: event_id,
            event_name: event_name,
            event_date: event_date,
            event_start_time: event_start_time,
            event_end_time: event_end_time,
            event_platform: event_platform
        },
        success: function(response) {
            $('#event_edit_modal').modal('hide');
            if (response.status) {
                alert(response.msg);
                $('#calendar').fullCalendar('refetchEvents'); // Recarrega os eventos
            } else {
                alert(response.msg);
            }
        },
        error: function(xhr, status) {
            console.log('Erro ao atualizar evento: ' + xhr.statusText);
            alert('Erro ao atualizar o evento.');
        }
    });

    return false;
}

function delete_event() {
    var event_id = $("#edit_event_id").val();

    $.ajax({
        url: 'delete_event.php',
        type: 'POST',
        dataType: 'json',
        data: { event_id: event_id },
        success: function(response) {
            $('#event_edit_modal').modal('hide');
            if (response.status) {
                alert(response.msg);
                $('#calendar').fullCalendar('refetchEvents'); // Recarrega os eventos
            } else {
                alert(response.msg);
            }
        },
        error: function(xhr, status) {
            console.log('Erro ao excluir evento: ' + xhr.statusText);
            alert('Erro ao excluir o evento.');
        }
    });

    return false;
}
</script>
        <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }
        </script>
</body>
</html>