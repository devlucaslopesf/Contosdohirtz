<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "rpg_database");

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Pega as informações da ficha do jogador
$stmt = $conn->prepare("SELECT character_name, race, class, level FROM ficha WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($character_name, $race, $class, $level);
$stmt->fetch();
$stmt->close();

// Pega a quantidade de moedas do usuário
$result = $conn->query("SELECT moedas FROM users WHERE id = $user_id");
$row = $result->fetch_assoc();
$moedas = $row['moedas'];

// Processa a compra de um item, se o botão for pressionado
if (isset($_POST['comprar'])) {
    $item_id = $_POST['comprar'];
    $result = $conn->query("SELECT preco FROM itens WHERE id = $item_id");

    if ($result) {
        $item = $result->fetch_assoc();

        if ($moedas >= $item['preco']) {
            $conn->query("UPDATE users SET moedas = moedas - {$item['preco']} WHERE id = $user_id");
            $conn->query("INSERT INTO compras (user_id, item_id) VALUES ($user_id, $item_id)");
            $_SESSION['message'] = "Compra realizada com sucesso!";
            header("Location: ficha.php");
            exit;
        } else {
            $_SESSION['message'] = "Você não tem moedas suficientes para comprar este item.";
            header("Location: ficha.php");
            exit;
        }
    } else {
        $_SESSION['message'] = "Erro ao obter o preço do item: " . $conn->error;
        header("Location: ficha.php");
        exit;
    }
}

$itens = $conn->query("SELECT * FROM itens");

$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de RPG</title>
    <link rel="stylesheet" href="src/ficha2.css">
</head>
<body>

<div class="ficha-container">
    <h2>Ficha de RPG</h2>
    <p><strong>Nome do Personagem:</strong> <?= htmlspecialchars($character_name) ?></p>
    <p><strong>Raça:</strong> <?= htmlspecialchars($race) ?></p>
    <p><strong>Classe:</strong> <?= htmlspecialchars($class) ?></p>
    <p><strong>Nível:</strong> <?= htmlspecialchars($level) ?></p>
    <p><strong>Moedas:</strong> <?= $moedas ?></p>
    
    <div class="compras">
        <h3>Itens Comprados</h3>
        <ul>
            <?php
            $compras = $conn->query("SELECT i.nome, i.descricao FROM itens i JOIN compras c ON i.id = c.item_id WHERE c.user_id = $user_id");
            while ($item = $compras->fetch_assoc()): ?>
                <li><strong><?= htmlspecialchars($item['nome']) ?>:</strong> <?= htmlspecialchars($item['descricao']) ?></li>
            <?php endwhile; ?>
        </ul>
    </div>

    <a href="logout.php" class="logout-button">Sair</a>
</div>

<?php if ($message): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="loja">
    <h3>Loja de Itens</h3>
    <form method="post">
        <?php while ($item = $itens->fetch_assoc()): ?>
            <div class="item">
                <p><strong>Item:</strong> <?= htmlspecialchars($item['nome']) ?></p>
                <p><strong>Descrição:</strong> <?= htmlspecialchars($item['descricao']) ?></p>
                <p><strong>Preço:</strong> <?= $item['preco'] ?> moedas</p>
                <button type="submit" name="comprar" value="<?= $item['id'] ?>">Comprar</button>
            </div>
            <hr>
        <?php endwhile; ?>
    </form>
</div>

<?php $conn->close(); ?>

</body>
</html>
