<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prácticas Profesionales | Instituto</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(to right, #0f172a, #1e3a8a);
            min-height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        /* Loader */
        #loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #0f172a;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }

        .spinner {
            width: 70px;
            height: 70px;
            border: 6px solid rgba(255,255,255,0.2);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            width: 90%;
            max-width: 700px;
            padding: 45px 35px;
            text-align: center;
            border-radius: 25px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Animación del logo */
        .logo {
            width: 155px;
            margin-bottom: 20px;
            animation: bounce 2s infinite ease-in-out;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        h1 {
            font-size: 32px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 12px;
        }

        h2 {
            font-size: 20px;
            font-weight: 400;
            color: #334155;
            margin-bottom: 25px;
        }

        .btn {
            display: inline-block;
            padding: 14px 35px;
            background: #1e3a8a;
            color: #fff;
            text-decoration: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 500;
            transition: 0.3s;
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
        }

        .btn:hover {
            background: #0f172a;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.25);
        }

        /* Footer */
        footer {
            width: 100%;
            color: #ffffffd4;
            text-align: center;
            padding: 15px 0;
            font-size: 14px;
            position: absolute;
            bottom: 0;
            left: 0;
        }

        footer span {
            font-weight: 600;
        }

        /* Fondos decorativos */
        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .circle.c1 { width: 180px; height: 180px; top: 10%; left: 5%; }
        .circle.c2 { width: 250px; height: 250px; bottom: 12%; right: 8%; }
        .circle.c3 { width: 120px; height: 120px; bottom: 25%; left: 15%; }

    </style>
</head>

<body>

    <!-- Loader -->
    <div id="loader">
        <div class="spinner"></div>
    </div>

    <!-- Fondos decorativos -->
    <div class="circle c1"></div>
    <div class="circle c2"></div>
    <div class="circle c3"></div>

    <div class="card">
        <img src="img/logo.png" class="logo" alt="Logo del Instituto">

        <h1>Sistema de Gestión de Mantenimiento de Equipos Institucionales (SGMEI)</h1>
        <h2>Sistema de Seguimiento Institucional</h2>

        <a href="login.php" class="btn">Ingresar al Sistema</a>
    </div>

    <!-- Footer -->
    <footer>
        © <span><?php echo date("Y"); ?></span> Instituto Superior Tecnológico | Sistema de Prácticas Profesionales
    </footer>

    <script>
        // Ocultar loader después de cargar
        window.onload = function() {
            const loader = document.getElementById("loader");
            loader.style.opacity = "0";
            setTimeout(() => loader.style.display = "none", 500);
        };
    </script>

</body>
</html>
