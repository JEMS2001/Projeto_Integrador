
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
    width: 250px;
    background: var(--primary-color);
    color: var(--text-color);
    position: fixed;
    height: 100%;
    padding-top: 60px;
}

.titulo-calendario {
    text-align: center; 
    margin-top: 30px;   
    font-size: 35px;   
    font-weight: bold;  
    color: var(--secondary-color);
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
        <?php } ?>
        <?php if ($tabela == 'empresa') { ?>
            <a href="monitoramento.php">
                <i class="fas fa-chart-bar me-1"></i>Relatórios
            </a>
        <?php } ?>

        <a class="nav-link" href="notificacao.php">
                <i class="fas fa-users me-1"></i>Notificações
            </a>

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
            <h5 class="titulo-calendario">Calendário de Eventos</h5>
            <!-- Botões para adicionar, editar e excluir eventos -->
            <button class="btn btn-primary" data-toggle="modal" data-target="#event_entry_modal">Adicionar Evento</button>
            <br> <br>
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
</body>
</html>