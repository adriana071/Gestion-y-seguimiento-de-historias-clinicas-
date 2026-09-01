<?php
require_once 'model/sesion.php';
Sesion::iniciar();
Sesion::cerrar();
header("Location: login.php");
exit();
?>
