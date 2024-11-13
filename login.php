<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Contos do Hirtz - Login</title>
    <link rel="stylesheet" href="src/login.css">
</head>

<body>

<div class="container">
<img src="images/logo.png" alt="Logo">
    <h2>Login</h2>
    <form action="login.php" method="post">
        <label>Email: <input type="email" name="email" required></label><br>
        <label>Senha: <input type="password" name="password" required></label><br>
        <input type="submit" value="Entrar">
    </form>

    <div class="register-link">
        <p>Não tem uma conta?  <a href="register.php">Cadastre-se </a>e comece sua aventura!</p>
    </div>
    
</div>

<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $conn = new mysqli("localhost", "root", "", "rpg_database");

    if ($conn->connect_error) {
        die("Erro de conexão: " . $conn->connect_error);
    }

    // Seleciona os dados do usuário e verifica se é administrador
    $stmt = $conn->prepare("SELECT id, password, is_admin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $hashed_password, $is_admin);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            // Define o ID do usuário e o status de administrador na sessão
            $_SESSION['user_id'] = $user_id;
            $_SESSION['is_admin'] = $is_admin;

            // Redireciona o administrador para a página admin.php e o usuário comum para ficha.php
            if ($is_admin) {
                header("Location: admin.php");
            } else {
                header("Location: ficha.php");
            }
        } else {
            echo "Senha incorreta!";
        }
    } else {
        echo "Usuário não encontrado!";
    }

    $stmt->close();
    $conn->close();
}
?>

</body>

</html>
