<?php
// Dados de conexão com o banco de dados
$servername = "mysql";  // Nome do servidor MySQL (geralmente 'localhost')
$username = "jfhk";  // Nome de usuário do MySQL
$password = "jfhk123";    // Senha do MySQL
$dbname = "projeto";  // Nome do banco de dados que você criou

// Cria a conexão
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Conexão falhou: " . $e->getMessage());
}
?>
