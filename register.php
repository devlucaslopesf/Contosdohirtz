<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="stylesheet" href="src/register2.css"> 
</head>
<body>

<div id="container">
    <h2>Cadastro de Usuário e Ficha de RPG</h2>
    <form action="register.php" method="post">
        <div class="form-group">
            <label>Usuário:</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Senha:</label>
            <input type="password" name="password" required>
        </div>

        <h3>Ficha de RPG</h3>
        <div class="form-group">
            <label>Nome do Personagem:</label>
            <input type="text" name="character_name" required>
        </div>
        <div class="form-group">
            <label>Raça:</label>
            <input type="text" name="race" required>
        </div>
        <div class="form-group">
            <label>Classe:</label>
            <input type="text" name="class" required>
        </div>

        <input type="submit" value="Cadastrar">
    </form>
    <div class="link">
        <span>Já tem uma conta? <a href="login.php">Faça login aqui</a></span>
    </div>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $character_name = $_POST['character_name'];
        $race = $_POST['race'];
        $class = $_POST['class'];

        $conn = new mysqli("localhost", "root", "", "rpg_database");
        
        if ($conn->connect_error) {
            die("Erro de conexão: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            $stmt_ficha = $conn->prepare("INSERT INTO ficha (user_id, character_name, race, class) VALUES (?, ?, ?, ?)");
            $stmt_ficha->bind_param("isss", $user_id, $character_name, $race, $class);
            $stmt_ficha->execute();
            echo "<p class='message'>Cadastro realizado com sucesso!</p>";
        } else {
            echo "<p class='message'>Erro ao cadastrar: " . $stmt->error . "</p>";
        }

        $stmt->close();
        $conn->close();
    }
    ?>
</div>

</body>
</html>
