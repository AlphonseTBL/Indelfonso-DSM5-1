<?php
session_start();

// Si no hay sesión válida, redirigir al formulario de login
if (!isset($_SESSION['user_id'])) {
    header('Location: login');
    exit();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>loggeado</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="">
    </head>
    <body>
        <h1>Logged In Successfully</h1>
        <p>Welcome to your dashboard, <?php echo htmlspecialchars($_SESSION['email']); ?>.</p>
        <script src="" async defer></script>
    </body>
</html>