<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração</title>
    <link rel="stylesheet" href="src/admin.css"> 
</head>
<body>

<header>
    <h1>Painel de Administração</h1>
</header>

<!-- Seção de Fichas -->
<h2>Fichas de Personagem</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome do Personagem</th>
            <th>Raça</th>
            <th>Classe</th>
            <th>Nível</th>
            <th>Moedas</th> 
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Conexão ao banco de dados
        $conn = new mysqli("localhost", "root", "", "rpg_database");

        if ($conn->connect_error) {
            die("Erro de conexão: " . $conn->connect_error);
        }

        // Consulta para obter todas as fichas, incluindo a coluna moedas (na tabela users)
        $stmt = $conn->prepare("SELECT f.id, f.character_name, f.race, f.class, f.level, u.moedas FROM ficha f JOIN users u ON f.user_id = u.id");
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['character_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['race']) . "</td>";
            echo "<td>" . htmlspecialchars($row['class']) . "</td>";
            echo "<td>" . htmlspecialchars($row['level']) . "</td>";
            echo "<td>" . htmlspecialchars($row['moedas']) . "</td>"; // Exibindo a quantidade de moedas do usuário
            echo "<td><a class='button' href='edit_ficha.php?id=" . $row['id'] . "'>Editar</a></td>";
            echo "</tr>";
        }

        $stmt->close();
        ?>
    </tbody>
</table>

<!-- Seção de Administração da Loja -->
<h2>Loja - Adicionar Itens</h2>
<form method="post" action="admin.php">
    <label>Nome do Item:
        <input type="text" name="nome_item" required>
    </label>
    <label>Descrição:
        <textarea name="descricao_item" required></textarea>
    </label>
    <label>Preço em Moedas:
        <input type="number" name="preco_item" required>
    </label>
    <input type="submit" value="Adicionar Item" class="button">
</form>

<?php
// Tratamento do formulário de adição de itens
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nome_item'], $_POST['descricao_item'], $_POST['preco_item'])) {
    $nome_item = $_POST['nome_item'];
    $descricao_item = $_POST['descricao_item'];
    $preco_item = $_POST['preco_item'];

    // Consulta para adicionar o item à tabela de itens
    $stmt = $conn->prepare("INSERT INTO itens (nome, descricao, preco) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $nome_item, $descricao_item, $preco_item);

    if ($stmt->execute()) {
        // Redireciona para evitar o reenvio do formulário
        header("Location: admin.php");
        exit();
    } else {
        echo "<p class='message'>Erro ao adicionar item: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

// Consulta para exibir itens na loja
$result = $conn->query("SELECT * FROM itens");
?>

<!-- Tabela de Itens da Loja -->
<h2>Itens Disponíveis na Loja</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome do Item</th>
            <th>Descrição</th>
            <th>Preço</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($row['descricao']) . "</td>";
            echo "<td>" . htmlspecialchars($row['preco']) . "</td>";
            echo "<td>
                    <form action='admin.php' method='post' style='display:inline;'>
                        <input type='hidden' name='item_id' value='" . $row['id'] . "'>
                        <input type='submit' name='delete_item' value='Excluir' class='button' onclick='return confirm(\"Tem certeza que deseja excluir este item?\");'>
                    </form>
                  </td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

<?php
// Tratamento da exclusão de itens
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_item'])) {
    $item_id = $_POST['item_id'];

    // Deletar compras relacionadas ao item
    $conn->query("DELETE FROM compras WHERE item_id = $item_id");

    // Agora, deletar o item
    if ($conn->query("DELETE FROM itens WHERE id = $item_id")) {
        echo "<p class='message'>Item excluído com sucesso.</p>";
    } else {
        echo "<p class='message'>Erro ao excluir item: " . $conn->error . "</p>";
    }
}

$conn->close();
?>

<a class="button" href="logout.php">Sair</a>

</body>
</html>
