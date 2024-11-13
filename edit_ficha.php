<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "rpg_database");

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
       // Adiciona moedas
if (isset($_POST['add_coins'])) {
    $coins_to_add = (int)$_POST['coins']; // Converte para inteiro

    // Adiciona moedas ao usuário (na tabela 'users')
    $stmt = $conn->prepare("UPDATE users SET moedas = moedas + ? WHERE id = ?");
    $stmt->bind_param("ii", $coins_to_add, $id);
    if ($stmt->execute()) {
        echo "<p style='color: green;'>Moedas adicionadas com sucesso!</p>";
    } else {
        echo "<p style='color: red;'>Erro ao adicionar moedas: " . $stmt->error . "</p>";
    }
    $stmt->close();
}else {
            // Atualiza a ficha do personagem
            $character_name = $_POST['character_name'];
            $race = $_POST['race'];
            $class = $_POST['class'];
            $level = $_POST['level'];

            $stmt = $conn->prepare("UPDATE ficha SET character_name = ?, race = ?, class = ?, level = ? WHERE id = ?");
            $stmt->bind_param("sssii", $character_name, $race, $class, $level, $id);

            if ($stmt->execute()) {
                echo "<p style='color: green;'>Ficha atualizada com sucesso!</p>";
            } else {
                echo "<p style='color: red;'>Erro ao atualizar a ficha: " . $stmt->error . "</p>";
            }
            $stmt->close();
        }
    }

    // Recupera dados da ficha
    $stmt = $conn->prepare("SELECT character_name, race, class, level, coins FROM ficha WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($character_name, $race, $class, $level, $coins);
    $stmt->fetch();
    $stmt->close();
} else {
    echo "Ficha não encontrada.";
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Ficha</title>
    <link rel="stylesheet" href="src/edit_ficha2.css"> <!-- Vincula o arquivo CSS -->
</head>
<body>
    <div class="container">
        <h2>Editar Ficha de Personagem</h2>
        <form method="post">
            <label>Nome do Personagem:</label>
            <input type="text" name="character_name" value="<?= htmlspecialchars($character_name) ?>" required>

            <label>Raça:</label>
            <input type="text" name="race" value="<?= htmlspecialchars($race) ?>" required>

            <label>Classe:</label>
            <input type="text" name="class" value="<?= htmlspecialchars($class) ?>" required>

            <label>Nível:</label>
            <input type="number" name="level" value="<?= htmlspecialchars($level) ?>" required>

            <input type="submit" value="Salvar">
        </form>

        <!-- Formulário para adicionar moedas -->
        <h3>Adicionar Moedas</h3>
        <form method="post">
            <label>Quantidade de Moedas:</label>
            <input type="number" name="coins" required>
            <input type="submit" name="add_coins" value="Adicionar Moedas">
        </form>

        <a href="admin.php">Voltar ao Painel de Administração</a>
    </div>
</body>
</html>
