<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    unset($_SESSION['email']);
    unset($_SESSION['senha']);
    header('Location: login.php');
    exit;
}

$logado = $_SESSION['email'];
$tabela = $_SESSION['tipo_usuario'];

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($tabela === 'empresa') {
        $sql = "SELECT nome, cnpj, endereco, email, imagem FROM empresa WHERE email = :email";
    } else {
        $sql = "SELECT nome, data_nascimento, telefone, cpf, email, imagem FROM membro WHERE email = :email";
    }

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


if (empty($userData['imagem']) || $userData['imagem'] === '/img/avatares/padrao.png') {
    $userData['imagem'] = 'xa/img/avatares/padrao.png'; 
}


if (!str_starts_with($userData['imagem'], '/img/avatares')) {
    $userData['imagem'] = '/img/avatares/padrao.png';
}
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
        .sidebar {
            width: 250px;
            background: var(--primary-color);
            color: var(--text-color);
            position: fixed;
            height: 100%;
            padding-top: 60px;
            left: 0;
            z-index: 1000;
            transition: all 0.3s;
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

        .user-profile-wrapper {
            --profile-primary-color: #2c3e50;
            --profile-secondary-color: #34495e;
            --profile-accent-color: #3498db;
            --profile-text-color: #ecf0f1;
            --profile-background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
            min-height: 100vh;
            background-color: var(--profile-background-color);
            margin-left: 250px;
            transition: all 0.3s;
        }

        .user-profile-wrapper .profile-container {
            width: 100%;
            padding: 0;
        }

        .user-profile-wrapper .profile-header {
            background-color: var(--profile-primary-color);
            color: var(--profile-text-color);
            padding: 2rem;
            text-align: left;
            margin-bottom: 0;
            display: flex;
            align-items: center;
        }

        .user-profile-wrapper .profile-image img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--profile-accent-color);
            margin-right: 2rem;
        }

        .user-profile-wrapper .profile-info h1 {
            margin-top: 0;
            font-size: 2rem;
        }

        .user-profile-wrapper .profile-nav {
            background-color: var(--profile-secondary-color);
            padding: 1rem;
            margin-bottom: 0;
        }

        .user-profile-wrapper .profile-nav a {
            color: var(--profile-text-color);
            text-decoration: none;
            padding: 0.5rem 1rem;
            margin: 0 0.5rem;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .user-profile-wrapper .profile-nav a:hover {
            background-color: var(--profile-accent-color);
        }

        .user-profile-wrapper .profile-content {
            display: flex;
            flex-wrap: wrap;
            padding: 2rem;
        }

        .user-profile-wrapper .profile-section {
            background-color: #fff;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            flex: 1 1 100%;
        }

        .user-profile-wrapper .profile-section h2 {
            color: var(--profile-primary-color);
            border-bottom: 2px solid var(--profile-accent-color);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }

        .user-profile-wrapper .profile-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .user-profile-wrapper .detail-item {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .user-profile-wrapper .detail-item h3 {
            color: var(--profile-secondary-color);
            margin-bottom: 0.5rem;
        }

        .user-profile-wrapper .fade-in {
            opacity: 0;
            transform: translateY(20px);
            animation: profileFadeIn 0.5s ease forwards;
        }

        @keyframes profileFadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .navbar-dark .navbar-nav .nav-link {
            color: rgba(255, 255, 255, .8);
        }

        .navbar-dark .navbar-nav .nav-link:hover {
            color: #fff;
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
            #sidebarCollapse {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }

            .sidebar.active {
                left: 0;
            }

            .user-profile-wrapper {
                margin-left: 0;
            }

            .user-profile-wrapper .profile-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .user-profile-wrapper .profile-image {
                margin-right: 0;
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 576px) {
            .user-profile-wrapper .profile-content {
                padding: 1rem;
            }

            .user-profile-wrapper .profile-section {
                padding: 1rem;
            }
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .overlay.active {
            display: block;
        }
    </style>
</head>

<body>
    <button id="sidebarCollapse">
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
        <?php } ?>
        <a href="#">
            <i class="fas fa-chart-bar me-1"></i>Relatórios
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
                    <button class="btn profile-image" data-bs-toggle="modal" data-bs-target="#addImageModal">
                        <img src="<?= $userData['imagem'] ?>" alt="Foto de Perfil">
                    </button>
                </div>

                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($userData['nome']); ?></h1>
                    <p><?php echo ($tabela === 'empresa') ? 'Empresa' : 'Membro'; ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo ($tabela === 'empresa') ? htmlspecialchars($userData['endereco']) : 'Localização não disponível'; ?></p>
                </div>
            </div>

            <nav class="profile-nav fade-in">
                <a href="#sobre">Sobre</a>
                <a href="#detalhes">Detalhes</a>
                <a href="#configuracoes">Configurações</a>
            </nav>

            <div class="profile-content fade-in">
                <section id="sobre" class="profile-section">
                    <h2>Sobre</h2>
                    <p>Informações adicionais sobre <?php echo htmlspecialchars($userData['nome']); ?> seriam exibidas aqui.</p>
                </section>

                <section id="detalhes" class="profile-section">
                    <h2>Detalhes do Perfil</h2>
                    <div class="profile-details">
                        <?php if ($tabela === 'empresa') : ?>
                            <div class="detail-item">
                                <h3>CNPJ</h3>
                                <p><?php echo htmlspecialchars($userData['cnpj']); ?></p>
                            </div>
                            <div class="detail-item">
                                <h3>Endereço</h3>
                                <p><?php echo htmlspecialchars($userData['endereco']); ?></p>
                            </div>
                        <?php else : ?>
                            <div class="detail-item">
                                <h3>Data de Nascimento</h3>
                                <p><?php echo htmlspecialchars($userData['data_nascimento']); ?></p>
                            </div>
                            <div class="detail-item">
                                <h3>CPF</h3>
                                <p><?php echo htmlspecialchars($userData['cpf']); ?></p>
                            </div>
                            <div class="detail-item">
                                <h3>Telefone</h3>
                                <p><?php echo htmlspecialchars($userData['telefone']); ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="detail-item">
                            <h3>Email</h3>
                            <p><?php echo htmlspecialchars($userData['email']); ?></p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>


    <div class="modal fade" id="addImageModal" tabindex="-1" aria-labelledby="addImageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addImageModalLabel">Adicionar Imagem</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

    <script>
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
                .then(response => response.json())
                .then(data => {
                    const uploadMessage = document.getElementById('uploadMessage');
                    if (data.success) {
                        uploadMessage.innerHTML = data.message;

                        const profileImage = document.querySelector('.profile-image img');
                        profileImage.src = data.filePath + '?v=' + new Date().getTime(); // Adiciona um parâmetro de consulta para evitar cache

                       
                    } else {
                        uploadMessage.innerHTML = data.message;
                    }
                })
                .catch(error => {
                    console.error('Erro ao enviar o arquivo:', error);
                    document.getElementById('uploadMessage').innerHTML = 'Desculpe, houve um erro ao enviar seu arquivo.';
                });
        }
    </script>



    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const fadeElements = document.querySelectorAll('.user-profile-wrapper .fade-in');
            fadeElements.forEach((el, index) => {
                el.style.animationDelay = `${0.2 * index}s`;
            });
        });

        document.getElementById('sidebarCollapse').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.user-profile-wrapper').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        });

        document.getElementById('overlay').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.remove('active');
            document.querySelector('.user-profile-wrapper').classList.remove('active');
            document.getElementById('overlay').classList.remove('active');
        });
    </script>
</body>

</html>