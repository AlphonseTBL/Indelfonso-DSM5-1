<?php
session_start();

// Configuración de conexión a MySQL
$servername = "localhost";
$username = "root";
$password = "";
$database = "test";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Código para manejar la autenticación de usuarios
// Procesar login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';

    // Usar consultas preparadas para evitar inyección
    $stmt = $conn->prepare("SELECT id, password FROM usuarios WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stored = $row['password'];

            // Verificar contraseña modernamente hasheada
            if (password_verify($password, $stored)) {
                $_SESSION["user_id"] = $row["id"];
                $_SESSION["email"] = $email;
                header("Location: loggeado.php");
                exit();
            }

            // Manejo de contraseñas legacy/no-hasheadas para facilitar migración:
            // Comprobar igualdad en texto plano o hashes comunes (md5, sha1)
            $legacy_ok = false;
            if ($password === $stored) {
                $legacy_ok = true;
            } elseif (md5($password) === $stored) {
                $legacy_ok = true;
            } elseif (sha1($password) === $stored) {
                $legacy_ok = true;
            }

            if ($legacy_ok) {
                // Re-hashear la contraseña con password_hash y actualizar la DB
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $up = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                if ($up) {
                    $up->bind_param('si', $newHash, $row['id']);
                    $up->execute();
                    $up->close();
                }

                // Iniciar sesión
                $_SESSION["user_id"] = $row["id"];
                $_SESSION["email"] = $email;
                header("Location: loggeado.php");
                exit();
            }

            $error = "Contraseña incorrecta";
        } else {
            $error = "Usuario no encontrado";
        }

        $stmt->close();
    } else {
        $error = "Error en la consulta (prepare)";
    }
}

$conn->close();
?>

<?php if (isset($error)): ?>
    <p style="color: red;"><?php echo $error; ?></p>
<?php endif; ?>

//EL FOking BODY
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Mantenimiento</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-container">
    <h1>Acceso Mantenimiento</h1>

    <form method="POST" action="login.php">
        <label>Email</label>
        <input type="text" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Entrar</button>
    </form>

    <?php if (isset($error)): ?>
        <p style="color:red; margin-top:10px;">
            <?php echo $error; ?>
        </p>
    <?php endif; ?>
</div>

</body>
</html>
