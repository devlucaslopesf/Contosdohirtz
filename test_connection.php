<?php
$conn = new mysqli("localhost", "root", "", "rpg_database");

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
} else {
    echo "Conexão bem-sucedida!";
}

$conn->close();
?>
