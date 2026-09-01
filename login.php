<?php
// Limpiar cualquier salida previa
ob_start();

require_once 'model/sesion.php';
require_once 'model/conexion.php';

Sesion::iniciar();

if (Sesion::obtenerIdUsuario()) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login']) && isset($_POST['password'])) {
    $login = trim($_POST['login']);
    $pwd = trim($_POST['password']);

    try {
        $conex = conexion::Conectar();

        $stmt = $conex->prepare("
            SELECT
                u.idUsuario,
                u.login,
                u.password,
                u.ciPersonal,
                u.intentosFallidos,
                u.bloqueadoHasta,
                p.nombre,
                p.apaterno,
                p.apmaterno,
                p.foto,
                p.codEspecialidad,
                e.nombre AS especialidad,
                r.nombre AS rol
            FROM usuario u
            INNER JOIN personalSalud p
                ON u.ciPersonal = p.ciPersonal
            INNER JOIN rol r
                ON u.codRol = r.codRol
            INNER JOIN especialidad e
                ON p.codEspecialidad = e.codEspecialidad
            WHERE u.login = :login
              AND u.estado = 1
              AND p.estado = 1
        ");
        $stmt->bindParam(':login', $login);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // ¿La cuenta está bloqueada todavía?
            $bloqueadoHasta = $user['bloqueadoHasta'];
            $siguebloqueado = !empty($bloqueadoHasta) && strtotime($bloqueadoHasta) > time();

            if ($siguebloqueado) {
                $minutosRestantes = (int) ceil((strtotime($bloqueadoHasta) - time()) / 60);
                $error = "Cuenta bloqueada por demasiados intentos fallidos. Intenta de nuevo en {$minutosRestantes} minuto(s).";
            } else {

                // Si el bloqueo ya venció, se limpia antes de seguir
                if (!empty($bloqueadoHasta)) {
                    $stmtReset = $conex->prepare("UPDATE usuario SET intentosFallidos = 0, bloqueadoHasta = NULL WHERE idUsuario = :idUsuario");
                    $stmtReset->bindParam(':idUsuario', $user['idUsuario']);
                    $stmtReset->execute();
                    $user['intentosFallidos'] = 0;
                }

                if (password_verify($pwd, $user['password']) || $pwd === $user['password']) {

                    // Login correcto: reiniciar intentos/bloqueo y registrar acceso
                    $stmtOk = $conex->prepare("UPDATE usuario SET intentosFallidos = 0, bloqueadoHasta = NULL, ultimoAcceso = NOW() WHERE idUsuario = :idUsuario");
                    $stmtOk->bindParam(':idUsuario', $user['idUsuario']);
                    $stmtOk->execute();

                    $usuario_datos = [
                        'idUsuario'       => $user['idUsuario'],
                        'ciPersonal'      => $user['ciPersonal'],
                        'codEspecialidad' => $user['codEspecialidad'],
                        'especialidad'    => $user['especialidad'],
                        'nombre'          => $user['nombre'],
                        'apaterno'        => $user['apaterno'],
                        'apmaterno'       => $user['apmaterno'],
                        'nombreCompleto'  => $user['nombre'] . ' ' . $user['apaterno'] . ' ' . $user['apmaterno'],
                        'rol'             => $user['rol'],
                        'email'           => $user['login'],
                        'foto'            => $user['foto']
                    ];

                    Sesion::establecerDatos($usuario_datos);

                    error_log("Usuario logueado: " . print_r($_SESSION, true));

                    header("Location: index.php");
                    exit();
                } else {

                    // Contraseña incorrecta: sumar intento fallido
                    $intentos = (int) $user['intentosFallidos'] + 1;

                    if ($intentos >= 3) {
                        $stmtBloq = $conex->prepare("UPDATE usuario SET intentosFallidos = 0, bloqueadoHasta = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE idUsuario = :idUsuario");
                        $stmtBloq->bindParam(':idUsuario', $user['idUsuario']);
                        $stmtBloq->execute();

                        $error = "Superaste el número de intentos permitidos. Tu cuenta quedó bloqueada por 15 minutos.";
                    } else {
                        $stmtInc = $conex->prepare("UPDATE usuario SET intentosFallidos = :intentos WHERE idUsuario = :idUsuario");
                        $stmtInc->bindParam(':intentos', $intentos);
                        $stmtInc->bindParam(':idUsuario', $user['idUsuario']);
                        $stmtInc->execute();

                        $restantes = 3 - $intentos;
                        $error = "Datos incorrectos. Te queda(n) {$restantes} intento(s) antes de que se bloquee tu cuenta.";
                    }
                }
            }
        } else {
            $error = 'Usuario no encontrado.';
        }

    } catch (PDOException $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Si hay error, limpiar el buffer y mostrar el HTML
ob_end_flush();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Parroquial Santa Clara - Login</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ==========================================
            VARIABLES - Paleta Hospital Santa Clara
            (azul marino, granate y dorado del logo)
        =========================================== */
        :root {
            --primary-color: #1b3a6b;   /* azul marino */
            --primary-dark: #14294d;    /* azul oscuro */
            --primary-light: #3a5f96;   /* azul claro (hover/gradientes) */
            --accent-color: #c9a227;    /* dorado */
            --accent-hover: #a9861d;    /* dorado oscuro */
            --danger-color: #9e1b32;    /* granate (para el ícono de emergencias, alertas) */
            --text-dark: #2d3748;
            --text-muted: #718096;
            --bg-light: #f0f4f8;
            --shadow-sm: 0 4px 20px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.15);
            --radius-lg: 24px;
            --radius-md: 16px;
            --radius-sm: 10px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #eef2f7 0%, #dde6ef 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* ==========================================
            CONTENEDOR PRINCIPAL
        =========================================== */
        .login-wrapper {
            width: 100%;
            max-width: 480px;
        }

        .login-container {
            display: flex;
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            transition: transform 0.3s ease;
            border-top: 4px solid var(--accent-color);
        }

        .login-container:hover {
            transform: translateY(-4px);
        }

        /* ==========================================
            FORMULARIO (única columna)
        =========================================== */
        .login-left {
            width: 100%;
            padding: 50px 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
            position: relative;
        }

        /* Logo del hospital - CON IMAGEN */
        .hospital-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .hospital-logo .logo-icon {
            width: 52px;
            height: 52px;
            background: white;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: 2px solid var(--primary-color);
        }

        .hospital-logo .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 4px;
        }

        .hospital-logo .logo-text {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-color);
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .hospital-logo .logo-text small {
            display: block;
            font-size: 11px;
            font-weight: 400;
            color: var(--text-muted);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Títulos */
        .welcome-section {
            margin-top: 24px;
            margin-bottom: 30px;
        }

        .welcome-section h2 {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .welcome-section h2 i {
            color: var(--danger-color);
        }

        .welcome-section p {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 0;
        }

        /* ==========================================
            FORMULARIO
        =========================================== */
        .form-group {
            margin-bottom: 16px;
            position: relative;
        }

        .form-group .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 18px;
            transition: color 0.3s ease;
            z-index: 2;
        }

        .form-group .form-control {
            height: 52px;
            padding: 0 16px 0 48px;
            border: 2px solid #e8edf2;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: var(--bg-light);
            width: 100%;
            color: var(--text-dark);
        }

        .form-group .form-control:focus {
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 4px rgba(27, 58, 107, 0.1);
            outline: none;
        }

        .form-group .form-control:focus ~ .input-icon {
            color: var(--primary-color);
        }

        .form-group .form-control::placeholder {
            color: #b0bec5;
            font-weight: 400;
            font-size: 13px;
        }

        /* Botón mostrar contraseña */
        .btn-toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            background: none;
            border: none;
            padding: 0;
            z-index: 3;
            font-size: 18px;
            cursor: pointer;
            transition: color 0.3s;
        }

        .btn-toggle-password:hover {
            color: var(--primary-color);
        }

        /* Opciones */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 4px 0 22px;
        }

        .form-options .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .form-options .form-check input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
            border-radius: 4px;
            cursor: pointer;
        }

        .form-options .form-check label {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            margin: 0;
        }

        .form-options .forgot-link {
            color: var(--primary-color);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.3s;
        }

        .form-options .forgot-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* ==========================================
            BOTÓN DE LOGIN
        =========================================== */
        .btn-login {
            width: 100%;
            height: 52px;
            border: none;
            border-radius: var(--radius-sm);
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(27, 58, 107, 0.35);
        }

        .btn-login:active {
            transform: scale(0.98);
        }

        .btn-login i {
            font-size: 18px;
        }

        /* ==========================================
            ALERTAS
        =========================================== */
        .alert-custom {
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 16px;
            animation: slideDown 0.4s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-custom.alert-danger {
            background: #fdf0f1;
            border: 1px solid #e2a6b0;
            color: var(--danger-color);
        }

        .alert-custom.alert-success {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            color: #276749;
        }

        .alert-custom i {
            font-size: 18px;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ==========================================
            FOOTER
        =========================================== */
        .login-footer {
            margin-top: 24px;
            text-align: center;
        }

        .login-footer p {
            color: var(--text-muted);
            font-size: 12px;
            margin-bottom: 2px;
        }

        .login-footer .hospital-name {
            color: var(--primary-color);
            font-weight: 600;
        }

        .login-footer .version {
            color: #b0bec5;
            font-size: 11px;
        }

        /* ==========================================
            RESPONSIVE
        =========================================== */
        @media (max-width: 992px) {
            .login-container {
                border-radius: var(--radius-md);
            }

            .login-left {
                padding: 35px 30px;
            }
        }

        @media (max-width: 480px) {
            .login-left {
                padding: 25px 18px;
            }

            .login-left .hospital-logo .logo-icon {
                width: 40px;
                height: 40px;
            }

            .login-left .hospital-logo .logo-text {
                font-size: 15px;
            }

            .welcome-section h2 {
                font-size: 20px;
            }

            .form-group .form-control {
                height: 44px;
                font-size: 13px;
                padding-left: 42px;
            }

            .form-group .input-icon {
                left: 14px;
                font-size: 16px;
            }

            .btn-login {
                height: 44px;
                font-size: 14px;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">

    <div class="login-container">

        <!-- ==========================================
            LADO IZQUIERDO - FORMULARIO
        =========================================== -->
        <div class="login-left">

            <!-- Logo del hospital con imagen -->
            <div class="hospital-logo">
                <div class="logo-icon">
                    <img src="view/images/logo-santa-clara.png" alt="Hospital Parroquial Santa Clara">
                </div>
                <div class="logo-text">
                    Hospital Parroquial
                    <small>Santa Clara</small>
                </div>
            </div>

            <!-- Bienvenida -->
            <div class="welcome-section">
                <h2>
                    <i class="bi bi-heart-pulse-fill"></i> Bienvenido
                </h2>
                <p>Ingrese sus credenciales para acceder al sistema</p>
            </div>

            <!-- Mostrar error -->
            <?php if (!empty($error)): ?>
                <div class="alert-custom alert-danger">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form method="POST">

                <div class="form-group">
                    <i class="bi bi-person-circle input-icon"></i>
                    <input
                        type="text"
                        class="form-control"
                        name="login"
                        placeholder="Usuario o correo electrónico"
                        value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <i class="bi bi-lock input-icon"></i>
                    <input
                        type="password"
                        class="form-control"
                        name="password"
                        placeholder="Contraseña"
                        required
                    >
                    <button
                        type="button"
                        class="btn-toggle-password"
                        onclick="togglePassword()"
                        title="Mostrar contraseña"
                    >
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>

                <div class="form-options">
                    <div class="form-check">
                        <input type="checkbox" id="recordar" name="recordar">
                        <label for="recordar">Recordarme</label>
                    </div>
                    <a href="#" class="forgot-link">¿Olvidó su contraseña?</a>
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                </button>

            </form>

            <!-- Footer -->
            <div class="login-footer">
                <p>
                    <i class="bi bi-shield-check"></i>
                    <span class="hospital-name">Hospital Parroquial Santa Clara</span>
                </p>
                <p class="version">
                    Sistema de Gestión de Historias Clínicas v1.0
                </p>
                <p class="version" style="margin-top: 4px;">
                    © <?php echo date('Y'); ?> - Todos los derechos reservados
                </p>
            </div>

        </div>

    </div>

</div>

<!-- ==========================================
    SCRIPTS
=========================================== -->
<script>
    // ==========================================
    // MOSTRAR/OCULTAR CONTRASEÑA
    // ==========================================
    function togglePassword() {
        const input = document.querySelector('input[name="password"]');
        const icon = document.getElementById('toggleIcon');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    // ==========================================
    // GUARDAR USUARIO EN LOCALSTORAGE
    // ==========================================
    document.addEventListener('DOMContentLoaded', function() {
        const recordar = document.getElementById('recordar');
        const usuarioInput = document.querySelector('input[name="login"]');

        const usuarioGuardado = localStorage.getItem('usuarioRecordado');
        if (usuarioGuardado) {
            usuarioInput.value = usuarioGuardado;
            recordar.checked = true;
        }

        recordar.addEventListener('change', function() {
            if (this.checked) {
                localStorage.setItem('usuarioRecordado', usuarioInput.value);
            } else {
                localStorage.removeItem('usuarioRecordado');
            }
        });

        usuarioInput.addEventListener('input', function() {
            if (recordar.checked) {
                localStorage.setItem('usuarioRecordado', this.value);
            }
        });
    });
</script>

</body>
</html>