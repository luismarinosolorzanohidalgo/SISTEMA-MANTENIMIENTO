<?php
session_start();

// Crear variables de control si no existen
if (!isset($_SESSION['intentos'])) {
    $_SESSION['intentos'] = 0;
}
if (!isset($_SESSION['bloqueado_hasta'])) {
    $_SESSION['bloqueado_hasta'] = 0;
}

$mensaje = "";

// Verificar si está bloqueado
if (time() < $_SESSION['bloqueado_hasta']) {
    $restante = $_SESSION['bloqueado_hasta'] - time();
    $mensaje = "Demasiados intentos fallidos. Intenta nuevamente en $restante segundos.";
} else {

    // Procesar login
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        include "conexion.php";

        $usuario = trim($_POST['usuario']);
        $clave   = trim($_POST['clave']);

        // CONSULTA SEGURA A LA BD
        $sql = $conn->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $sql->bind_param("s", $usuario);
        $sql->execute();
        $resultado = $sql->get_result();

        if ($resultado->num_rows > 0) {
            $data = $resultado->fetch_assoc();

            // ==========================================
            // VERIFICAR CONTRASEÑA (password_hash)
            // ==========================================
            if (password_verify($clave, $data['password'])) {

                // Login correcto
                $_SESSION['id_usuario'] = $data['id_usuario'];
                $_SESSION['nombre'] = $data['nombre'];
                $_SESSION['rol'] = $data['rol'];

                $_SESSION['intentos'] = 0;

                header("Location: panel.php");
                exit();
            }

            // Contraseña incorrecta
            $_SESSION['intentos']++;
            $mensaje = "Contraseña incorrecta.";

        } else {
            // Usuario no existe
            $_SESSION['intentos']++;
            $mensaje = "El usuario no existe.";
        }

        // BLOQUEAR TRAS 3 INTENTOS
        if ($_SESSION['intentos'] >= 3) {
            $_SESSION['bloqueado_hasta'] = time() + 60;
            $mensaje = "Demasiados intentos. Quedas bloqueado por 1 minuto.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistema de Equipos</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(to right, #0f172a, #1e3a8a);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            font-family: 'Poppins', sans-serif;
        }

        .login-box {
            background: rgba(255,255,255,0.95);
            width: 90%;
            max-width: 420px;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            animation: fade 1s ease;
        }

        @keyframes fade {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .logo {
            width: 120px;
            margin-bottom: 10px;
            animation: bounce 2s infinite ease-in-out;
        }

        @keyframes bounce {
            0%,100% { transform: translateY(0); }
            50%     { transform: translateY(-8px); }
        }

        h2 {
            margin-bottom: 20px;
            color: #0f172a;
        }

        .input {
            width: 100%;
            padding: 12px;
            margin-top: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 16px;
        }

        .btn {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background: #1e3a8a;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 17px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            background: #0f172a;
        }

        .msg {
            margin-top: 15px;
            color: #b91c1c;
            font-weight: 600;
        }

        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%,100% { transform: translateY(0); }
            50%     { transform: translateY(-18px); }
        }

        .c1 { width: 160px; height: 160px; top: 8%; left: 6%; }
        .c2 { width: 220px; height: 220px; bottom: 10%; right: 10%; }
    </style>
</head>

<body>

    <div class="circle c1"></div>
    <div class="circle c2"></div>

    <div class="login-box">
        <img src="img/logo.png" class="logo" alt="Logo">

        <h2>Ingreso al Sistema</h2>

        <?php if (!empty($mensaje)) : ?>
            <div class="msg"><?= $mensaje ?></div>
        <?php endif; ?>

        <?php if (time() >= $_SESSION['bloqueado_hasta']) : ?>
        <form method="POST">

            <input type="text" name="usuario" class="input" placeholder="Usuario" required>

            <input type="password" name="clave" class="input" placeholder="Contraseña" required>

            <button type="submit" class="btn">Ingresar</button>

        </form>
        <?php endif; ?>
    </div>

</body>
</html>
