<?php
session_start();
include_once('config.php');

$uploadDirectory = $_SERVER['DOCUMENT_ROOT'] . '/img/avatares/'; // Caminho absoluto no servidor
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($_FILES['imageFile']['name'], PATHINFO_EXTENSION));
$originalFileName = basename($_FILES['imageFile']['name']);

$check = getimagesize($_FILES['imageFile']['tmp_name']);
if ($check === false) {
    echo json_encode(['success' => false, 'message' => 'O arquivo não é uma imagem.']);
    $uploadOk = 0;
}

if ($_FILES['imageFile']['size'] > 500000) { // Limite de 500KB
    echo json_encode(['success' => false, 'message' => 'Desculpe, seu arquivo é muito grande.']);
    $uploadOk = 0;
}

if ($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "png" && $imageFileType != "gif") {
    echo json_encode(['success' => false, 'message' => 'Desculpe, apenas arquivos JPG, JPEG, PNG e GIF são permitidos.']);
    $uploadOk = 0;
}

if ($uploadOk == 0) {
    echo json_encode(['success' => false, 'message' => 'Desculpe, seu arquivo não foi enviado.']);
} else {
    $uniqueFileName = md5(uniqid(rand(), true)) . '.' . $imageFileType;
    $targetFile = $uploadDirectory . $uniqueFileName;

    if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $targetFile)) {
        try {
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $email = $_SESSION['email'];
            $table = $_SESSION['tipo_usuario'] === 'empresa' ? 'empresa' : 'membro';

            // Salva o caminho absoluto da imagem no banco de dados
            $absolutePath = '/img/avatares/' . $uniqueFileName;
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
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o banco de dados: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Desculpe, houve um erro ao enviar seu arquivo.']);
    }
}
?>
