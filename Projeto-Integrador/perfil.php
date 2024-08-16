<?php
// profile.php
session_start();
include_once('config.php');

if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

$logado = $_SESSION['email'];
$tabela = $_SESSION['tipo_usuario'];

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = ($tabela === 'empresa') 
        ? "SELECT nome, cnpj, endereco, email, imagem FROM empresa WHERE email = :email"
        : "SELECT nome, data_nascimento, telefone, cpf, email, imagem FROM membro WHERE email = :email";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $logado);
    $stmt->execute();

    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        die("Nenhum dado encontrado para o usuário.");
    }
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

$userData['imagem'] = empty($userData['imagem']) || !str_starts_with($userData['imagem'], 'img/avatares')
    ? 'img/avatares/padrao.png'
    : $userData['imagem'];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($userData['nome']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="estilo.css">
    <style>
        
        .form-group.error input {
            border-color: #dc3545;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 0.25rem;
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
        .user-profile-wrapper {
            margin-left: 250px;
            transition: all 0.3s;
        }
        .profile-header {
            background-color: var(--primary-color);
            color: var(--text-color);
            padding: 2rem;
            display: flex;
            align-items: center;
        }
        .profile-image img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--accent-color);
            margin-right: 2rem;
        }
        .profile-nav {
            background-color: var(--secondary-color);
            padding: 1rem;
        }
        .profile-nav a {
            color: var(--text-color);
            text-decoration: none;
            padding: 0.5rem 1rem;
            margin: 0 0.5rem;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .profile-nav a:hover {
            background-color: var(--accent-color);
        }
        .profile-content {
            padding: 2rem;
        }
        .profile-section {
            background-color: #fff;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .profile-section h2 {
            color: var(--primary-color);
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        .btn-danger-custom {
            background-color: #dc3545;
            color: white;
            transition: background-color 0.3s;
        }
        .btn-danger-custom:hover {
            background-color: #c82333;
        }
        .btn-danger-custom:disabled {
            background-color: #6c757d;
        }
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
            .user-profile-wrapper {
                margin-left: 0;
            }
            .profile-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .profile-image {
                margin-right: 0;
                margin-bottom: 1rem;
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

    <div class="overlay" id="overlay"></div>

    <div class="user-profile-wrapper">
        <div class="profile-container">
            <div class="profile-header fade-in">
                <div class="profile-image">
                    <?php if (isset($userData['imagem'])): ?>
                        <button class="btn profile-image" onclick="openModal()">
                            <img src="<?= $userData['imagem'] ?>" alt="Foto de Perfil">
                        </button>

                        <script>
                            function openModal() {
                                // Code to open the modal here
                                // For example, you can use Bootstrap's modal function:
                                $('#addImageModal').modal('show');
                            }
                        </script>
                    <?php else: ?>
                        <img src="default-image.jpg" alt="Foto de Perfil">
                    <?php endif; ?>
                </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($userData['nome']); ?></h1>
                <p><?php echo ($tabela === 'empresa') ? 'Empresa' : 'Membro'; ?></p>
                <?php if ($tabela === 'empresa'): ?>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($userData['endereco']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <nav class="profile-nav">
            <a href="#sobre">Sobre</a>
            <a href="#detalhes">Detalhes</a>
            <a href="#" data-toggle="modal" data-target="#editProfileModal">Configurações</a>
        </nav>

        <div class="profile-content">
            <section id="sobre" class="profile-section">
                <h2>Sobre</h2>
                <p>Informações adicionais sobre <?php echo htmlspecialchars($userData['nome']); ?> seriam exibidas aqui.</p>
            </section>

            <section id="detalhes" class="profile-section">
                <h2>Detalhes do Perfil</h2>
                <?php if ($tabela === 'empresa'): ?>
                    <p><strong>CNPJ:</strong> <?php echo htmlspecialchars($userData['cnpj']); ?></p>
                    <p><strong>Endereço:</strong> <?php echo htmlspecialchars($userData['endereco']); ?></p>
                <?php else: ?>
                    <p><strong>Data de Nascimento:</strong> <?php echo htmlspecialchars($userData['data_nascimento']); ?></p>
                    <p><strong>CPF:</strong> <?php echo htmlspecialchars($userData['cpf']); ?></p>
                    <p><strong>Telefone:</strong> <?php echo htmlspecialchars($userData['telefone']); ?></p>
                <?php endif; ?>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></p>
            </section>
        </div>
    </div>


    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Editar Perfil</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editProfileForm" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="editName">Nome</label>
                            <input type="text" class="form-control" id="editName" name="nome" value="<?php echo htmlspecialchars($userData['nome']); ?>" required>
                            <div class="error-message"></div>
                        </div>
                        <?php if ($tabela === 'empresa'): ?>
                            <div class="form-group">
                                <label for="editCNPJ">CNPJ</label>
                                <input type="text" class="form-control" id="editCNPJ" name="cnpj" value="<?php echo htmlspecialchars($userData['cnpj']); ?>" required>
                                <div class="error-message"></div>
                            </div>
                            <div class="form-group">
                                <label for="editEndereco">Endereço</label>
                                <input type="text" class="form-control" id="editEndereco" name="endereco" value="<?php echo htmlspecialchars($userData['endereco']); ?>" required>
                                <div class="error-message"></div>
                            </div>
                        <?php else: ?>
                            <div class="form-group">
                                <label for="editDataNascimento">Data de Nascimento</label>
                                <input type="date" class="form-control" id="editDataNascimento" name="data_nascimento" value="<?php echo htmlspecialchars($userData['data_nascimento']); ?>" required>
                                <div class="error-message"></div>
                            </div>
                            <div class="form-group">
                                <label for="editCPF">CPF</label>
                                <input type="text" class="form-control" id="editCPF" name="cpf" value="<?php echo htmlspecialchars($userData['cpf']); ?>" required>
                                <div class="error-message"></div>
                            </div>
                            <div class="form-group">
                                <label for="editTelefone">Telefone</label>
                                <input type="tel" class="form-control" id="editTelefone" name="telefone" value="<?php echo htmlspecialchars($userData['telefone']); ?>" required>
                                <div class="error-message"></div>
                            </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="editEmail">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                            <div class="error-message"></div>
                        </div>
                        <div class="form-group">
                            <label for="editImagem">Imagem de Perfil</label>
                            <input type="file" class="form-control-file" id="editImagem" name="imagem" accept="image/*">
                            <div class="error-message"></div>
                        </div>
                    </form>
                    <h5>Excluir Conta</h5>
                    <p>Atenção: Esta ação é irreversível. Todos os seus dados serão permanentemente removidos.</p>
                    <button id="initiateDelete" class="btn btn-danger-custom">Iniciar Processo de Exclusão</button>
                    <div id="deleteConfirmation" style="display: none;">
                        <p>Por favor, aguarde 5 segundos antes de confirmar a exclusão da conta.</p>
                        <button id="confirmDelete" class="btn btn-danger-custom" disabled>Confirmar Exclusão</button>
                        <button id="cancelDelete" class="btn btn-secondary">Cancelar</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="saveProfileChanges()">Salvar Alterações</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addImageModal" tabindex="-1" aria-labelledby="addImageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addImageModalLabel">Adicionar Imagem</h5>
                    <button class="close-button" onclick="closeModal()">X</button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center mb-4">
                        <img id="selectedAvatar" src="https://mdbootstrap.com/img/Photos/Others/placeholder-avatar.jpg" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;" alt="example placeholder" />
                    </div>
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="d-flex justify-content-center">
                            <div class="btn btn-primary btn-rounded">
                                <label class="form-label text-white m-1" for="customFile2">Escolher arquivo</label>
                                <input type="file" class="form-control d-none" id="customFile2" name="imageFile" onchange="displaySelectedImage(event, 'selectedAvatar')" />
                            </div>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            <button type="button" class="btn btn-primary" onclick="uploadImage()">Enviar</button>
                        </div>
                    </form>
                    <div id="uploadMessage" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
                        

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('initiateDelete').addEventListener('click', function() {
            this.style.display = 'none';
            document.getElementById('deleteConfirmation').style.display = 'block';
            setTimeout(function() {
                document.getElementById('confirmDelete').disabled = false;
            }, 5000);
        });

        document.getElementById('cancelDelete').addEventListener('click', function() {
            document.getElementById('initiateDelete').style.display = 'block';
            document.getElementById('deleteConfirmation').style.display = 'none';
            document.getElementById('confirmDelete').disabled = true;
        });

        document.getElementById('overlay').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.remove('active');
            document.querySelector('.user-profile-wrapper').classList.remove('active');
            document.getElementById('overlay').classList.remove('active');
        });

        document.getElementById('confirmDelete').addEventListener('click', function() {
            if (confirm('Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.')) {
                fetch('delete_account.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: '<?php echo $logado; ?>',
                        tabela: '<?php echo $tabela; ?>'
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Sua conta foi excluída com sucesso.');
                        window.location.href = 'logout.php';
                    } else {
                        alert('Erro ao excluir conta: ' + data.message);
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('Ocorreu um erro ao tentar excluir a conta.');
                });
            }
        });
        function displaySelectedImage(event, elementId) {
            const selectedImage = document.getElementById(elementId);
            const fileInput = event.target;

            if (fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const img = new Image();
                    img.onload = function() {
                        if (img.width === 150 && img.height === 150) {
                            selectedImage.src = e.target.result;
                        } else {
                            alert('A imagem deve ter 150x150 pixels.');
                        }
                    };
                    img.src = e.target.result;
                };

                reader.readAsDataURL(fileInput.files[0]);
            }
        }

        function uploadImage() {
                const formData = new FormData(document.getElementById('uploadForm'));

                fetch('upload.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text()) // Obtém a resposta como texto
                    .then(text => {
                        try {
                            const data = JSON.parse(text); // Tenta analisar o texto como JSON

                            const uploadMessage = document.getElementById('uploadMessage');
                            if (data.success) {
                                uploadMessage.innerHTML = data.message;

                                const profileImage = document.querySelector('.profile-image img');
                                profileImage.src = data.filePath + '?v=' + new Date().getTime(); // Adiciona um parâmetro de consulta para evitar cache

                                console.log('Imagem enviada com sucesso:', data.filePath);
                            } else {
                                uploadMessage.innerHTML = data.message;
                                console.log('Erro no upload:', data.message);
                            }
                        } catch (error) {
                            // Se a resposta não for um JSON válido, registra o texto bruto para depuração
                            console.error('Erro ao analisar a resposta como JSON:', text);
                            document.getElementById('uploadMessage').innerHTML = 'Desculpe, houve um erro inesperado. Verifique os logs do servidor.';
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao enviar o arquivo:', error);
                        document.getElementById('uploadMessage').innerHTML = 'Desculpe, houve um erro ao enviar seu arquivo.';
                    });
        }

        function validateForm() {
            const form = document.getElementById('editProfileForm');
            const inputs = form.querySelectorAll('input[required]');
            let isValid = true;

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    showError(input, 'Este campo é obrigatório.');
                    isValid = false;
                } else {
                    clearError(input);
                }
            });

            // Validate email
            const emailInput = document.getElementById('editEmail');
            if (emailInput.value && !isValidEmail(emailInput.value)) {
                showError(emailInput, 'Por favor, insira um email válido.');
                isValid = false;
            }

            // Validate CNPJ or CPF
            const cnpjInput = document.getElementById('editCNPJ');
            const cpfInput = document.getElementById('editCPF');
            if (cnpjInput && !isValidCNPJ(cnpjInput.value)) {
                showError(cnpjInput, 'Por favor, insira um CNPJ válido.');
                isValid = false;
            } else if (cpfInput && !isValidCPF(cpfInput.value)) {
                showError(cpfInput, 'Por favor, insira um CPF válido.');
                isValid = false;
            }

            // Validate phone number
            const telefoneInput = document.getElementById('editTelefone');
            if (telefoneInput && !isValidPhone(telefoneInput.value)) {
                showError(telefoneInput, 'Por favor, insira um número de telefone válido.');
                isValid = false;
            }

            return isValid;
        }

        function showError(input, message) {
            const formGroup = input.closest('.form-group');
            formGroup.classList.add('error');
            const errorElement = formGroup.querySelector('.error-message');
            errorElement.textContent = message;
        }

        function clearError(input) {
            const formGroup = input.closest('.form-group');
            formGroup.classList.remove('error');
            const errorElement = formGroup.querySelector('.error-message');
            errorElement.textContent = '';
        }

        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        function isValidCNPJ(cnpj) {
            cnpj = cnpj.replace(/[^\d]+/g,'');
            if(cnpj == '') return false;
            if (cnpj.length != 14) return false;
            
            return true;
        }

        function isValidCPF(cpf) {
            cpf = cpf.replace(/[^\d]+/g,'');
            if(cpf == '') return false;
            if (cpf.length != 11) return false;
            
            return true;
        }

        function isValidPhone(phone) {
            return /^\d{10,11}$/.test(phone.replace(/\D/g, ''));
        }

        function saveProfileChanges() {
            if (!validateForm()) {
                return;
            }

            const form = document.getElementById('editProfileForm');
            const formData = new FormData(form);

            fetch('update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Perfil atualizado com sucesso!');
                    location.reload();
                } else {
                    alert('Erro ao atualizar perfil: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocorreu um erro ao tentar atualizar o perfil.');
            });
        }
    </script>

    <script>
        function closeModal() {
            $('#addImageModal').modal('hide');
        }

        $(document).ready(function() {
            // Inicializa o modal do Bootstrap
            $('#addImageModal').modal({
                show: false
            });
        });
    </script>

    
</body>
</html>