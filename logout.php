<?php
session_start();

// Destruir todas las variables de sesión
session_unset();

// Destruir la sesión
session_destroy();

// Evitar que el usuario regrese con el botón "atrás"
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Location: login.php");
exit();
