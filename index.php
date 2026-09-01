<?php
header("Access-Control-Allow-Origin: *");
require_once 'model/conexion.php';
require_once 'model/auth.php';
$controller = 'cargo';
// Verificar si la solicitud es de Flutter (se puede distinguir por el Content-Type o por un parámetro especial)

if (!isset($_REQUEST['c'])) {
    // Si la solicitud es desde Flutter, redirigir al método ListarJson
    $isFlutterRequest = isset($_REQUEST['dsn']) && $_REQUEST['dsn'] === 'flutter';
    if ($isFlutterRequest) {
        // Ruta usada por la app móvil: no exige sesión web (usa su propio
        // mecanismo), así que no se le aplica el control de rol de aquí.
        error_reporting(E_ALL);
          ini_set('display_errors', 1);
        require_once "controller/$controller.controller.php";
        $controller = ucwords($controller) . 'controller';
        $controller = new $controller;
        $controller->ListarJson();  // Llama al método para Flutter
    } else {
        // Página de inicio (dashboard): solo exige sesión iniciada.
        // OJO: no usar require_role_or_redirect_for_module() aquí, porque
        // el controlador por defecto es 'cargo' (restringido a Administrador)
        // y eso generaba un bucle de redirección para Médico/Enfermero.
        require_login_or_redirect();
        require_once "controller/$controller.controller.php";
        $controller = ucwords($controller) . 'controller';
        $controller = new $controller;
        $controller->Index();  // Llama al método Index() para la página web
    }
} else {
    // Obtiene el controlador y la acción a cargar
    $controller = strtolower($_REQUEST['c']);
    $accion = isset($_REQUEST['a']) ? $_REQUEST['a'] : 'IndexPage';

    // Exige sesión + rol permitido para el módulo solicitado
    require_role_or_redirect_for_module($controller);

    // Instancia el controlador
    require_once "controller/$controller.controller.php";
    $controller = ucwords($controller) . 'controller';
    $controller = new $controller;

    // Llama la acción correspondiente
    call_user_func(array($controller, $accion));
}