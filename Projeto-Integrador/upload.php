<?php
session_start();
include_once('config.php');

header('Content-Type: application/json'); // Garante que a resposta seja JSON

$uploadDirectory = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'avatares') . DIRECTORY_SEPARATOR; // Caminho absoluto no servidor
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($_FILES['imageFile']['name'], PATHINFO_EXTENSION));
$originalFileName = basename($_FILES['imageFile']['name']);

// Verifique se o diretório existe
if (!file_exists($uploadDirectory)) {
    error_log('O diretório de upload não existe: ' . $uploadDirectory);
    echo json_encode(['success' => false, 'message' => 'Erro interno: diretório de upload não encontrado.']);
    exit;
}

// Verifique se o diretório é gravável
if (!is_writable($uploadDirectory)) {
    error_log('O diretório de upload não é gravável: ' . $uploadDirectory);
    echo json_encode(['success' => false, 'message' => 'Erro interno: diretório de upload não é gravável.']);
    exit;
}

// Depuração: Verificar $_FILES
error_log('$_FILES: ' . print_r($_FILES, true));

$check = getimagesize($_FILES['imageFile']['tmp_name']);
if ($check === false) {
    error_log('O arquivo não é uma imagem.');
    echo json_encode(['success' => false, 'message' => 'O arquivo não é uma imagem.']);
    exit;
}

if ($_FILES['imageFile']['size'] > 500000) { // Limite de 500KB
    error_log('Arquivo muito grande.');
    echo json_encode(['success' => false, 'message' => 'Desculpe, seu arquivo é muito grande.']);
    exit;
}

if ($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "png" && $imageFileType != "gif") {
    error_log('Tipo de arquivo não permitido.');
    echo json_encode(['success' => false, 'message' => 'Desculpe, apenas arquivos JPG, JPEG, PNG e GIF são permitidos.']);
    exit;
}

if ($uploadOk == 0) {
    error_log('Arquivo não foi enviado.');
    echo json_encode(['success' => false, 'message' => 'Desculpe, seu arquivo não foi enviado.']);
    exit;
} else {
    $uniqueFileName = md5(uniqid(rand(), true)) . '.' . $imageFileType;
    $targetFile = $uploadDirectory . $uniqueFileName;

    // Depuração: Caminho do arquivo de destino
    error_log('Caminho do arquivo de destino: ' . $targetFile);

    if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $targetFile)) {
        try {
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $email = $_SESSION['email'];
            $table = $_SESSION['tipo_usuario'] === 'empresa' ? 'empresa' : 'membro';

            // Salva o caminho absoluto da imagem no banco de dados
            $absolutePath = 'img/avatares/' . $uniqueFileName;

            // Depuração: Caminho da imagem salva
            error_log('Caminho da imagem salva: ' . $absolutePath);

            $stmt = $pdo->prepare("UPDATE $table SET imagem = :imagem WHERE email = :email");
            $stmt->bindParam(':imagem', $absolutePath);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            echo json_encode([
                'success' => true,
                'message' => 'O arquivo "' . htmlspecialchars($originalFileName) . '" foi enviado.',
                'filePath' => $absolutePath
            ]);
        } catch (PDOException $e) {
            error_log('Erro ao atualizar o banco de dados: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o banco de dados: ' . $e->getMessage()]);
        }
    } else {
        error_log('Erro ao mover o arquivo.');
        echo json_encode(['success' => false, 'message' => 'Desculpe, houve um erro ao enviar seu arquivo.']);
    }
}
?>
