<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

$logado = $_SESSION['email'];
$tabela = $_SESSION['tipo_usuario'];

function validateInput($input, $fieldName) {
    $input = trim($input);
    if (empty($input)) {
        throw new Exception("O campo $fieldName é obrigatório.");
    }
    return $input;
}

function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email inválido.");
    }
    return $email;
}

function validateCNPJ($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    if (strlen($cnpj) != 14) {
        throw new Exception("CNPJ inválido.");
    }
    // Add more complex CNPJ validation if needed
    return $cnpj;
}

function validateCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11) {
        throw new Exception("CPF inválido.");
    }
    // Add more complex CPF validation if needed
    return $cpf;
}

function validatePhone($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    if (strlen($phone) < 10 || strlen($phone) > 11) {
        throw new Exception("Número de telefone inválido.");
    }
    return $phone;
}

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $nome = validateInput($_POST['nome'], 'Nome');
    $email = validateEmail($_POST['email']);

    $updateFields = [
        'nome' => $nome,
        'email' => $email
    ];

    if ($tabela === 'empresa') {
        $updateFields['cnpj'] = validateCNPJ($_POST['cnpj']);
        $updateFields['endereco'] = validateInput($_POST['endereco'], 'Endereço');
    } else {
        $updateFields['data_nascimento'] = validateInput($_POST['data_nascimento'], 'Data de Nascimento');
        $updateFields['cpf'] = validateCPF($_POST['cpf']);
        $updateFields['telefone'] = validatePhone($_POST['telefone']);
    }

    // Handle image upload
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['imagem']['type'], $allowedTypes)) {
            throw new Exception("Tipo de arquivo não permitido. Use apenas JPEG, PNG ou GIF.");
        }

        if ($_FILES['imagem']['size'] > $maxFileSize) {
            throw new Exception("O arquivo é muito grande. O tamanho máximo permitido é 5MB.");
        }

        $uploadDir = 'img/avatares/';
        $fileExtension = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid() . '.' . $fileExtension;
        $uploadFile = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $uploadFile)) {
            $updateFields['imagem'] = $uploadFile;
        } else {
            throw new Exception("Falha ao fazer upload da imagem.");
        }
    }

    $sql = "UPDATE $tabela SET " . implode(', ', array_map(function($key) { return "$key = :$key"; }, array_keys($updateFields))) . " WHERE email = :old_email";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':old_email', $logado);
    
    foreach ($updateFields as $key => $value) {
        $stmt->bindParam(":$key", $updateFields[$key]);
    }

    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $_SESSION['email'] = $email; // Update session with new email if changed
        echo json_encode(['success' => true, 'message' => 'Perfil atualizado com sucesso.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nenhuma alteração realizada.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro de banco de dados: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>