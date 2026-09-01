<?php
require_once __DIR__ . '/sesion.php';

/**
 * Mapa de permisos por módulo (controlador).
 * Clave = valor de "c" en la URL (index.php?c=xxx), en minúsculas.
 * Valor = arreglo de roles (tal cual están en la tabla `rol`) que pueden
 *         entrar a ese módulo.
 *
 * Ajusta esta lista si agregas nuevos módulos o si quieres cambiar quién
 * ve qué. Un módulo que no aparece aquí solo exige estar logueado
 * (cualquier rol puede entrar), para no bloquear módulos nuevos por error.
 */
function obtener_mapa_permisos(): array
{
    return [
        // Configuración del sistema: solo Administrador
        'usuario'           => ['Administrador'],
        'rol'               => ['Administrador'],
        'cargo'             => ['Administrador'],
        'especialidad'      => ['Administrador'],
        'personalsalud'     => ['Administrador'],

        // Catálogo clínico / recetas: Administrador + Médico
        'medicamento'       => ['Administrador', 'Médico'],
        'receta'            => ['Administrador', 'Médico'],
        'tratamiento'       => ['Administrador', 'Médico'],

        // Consultas y resultados clínicos: Administrador + Médico
        // (el Enfermero registra el triaje, que alimenta la consulta,
        // pero no necesita entrar a Consultas/Exámenes/Resultados)
        'consulta'          => ['Administrador', 'Médico'],
        'examenlaboratorio' => ['Administrador', 'Médico'],
        'resultado'         => ['Administrador', 'Médico'],
        'resultdo'          => ['Administrador', 'Médico'],

        // Registro de pacientes y triaje: Administrador + Médico + Enfermero
        'paciente'          => ['Administrador', 'Médico', 'Enfermero'],
        'condicionfisica'   => ['Administrador', 'Médico', 'Enfermero'],
    ];
}

function is_logged_in(): bool
{
    Sesion::iniciar();
    return (bool) Sesion::obtenerIdUsuario();
}

function current_user_role(): string
{
    Sesion::iniciar();
    return Sesion::obtenerRol();
}

/** Exige solo que haya una sesión iniciada (sin importar el rol). */
function require_login_or_redirect(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

/** Exige sesión iniciada Y que el rol esté dentro de $rolesPermitidos. */
function require_role_or_redirect(array $rolesPermitidos): void
{
    require_login_or_redirect();
    if (!in_array(current_user_role(), $rolesPermitidos, true)) {
        header('Location: index.php?error=acceso_denegado');
        exit();
    }
}

/** Aplica el mapa de permisos al módulo (controlador) solicitado. */
function require_role_or_redirect_for_module(string $modulo): void
{
    $mapa = obtener_mapa_permisos();
    $modulo = strtolower($modulo);

    if (isset($mapa[$modulo])) {
        require_role_or_redirect($mapa[$modulo]);
    } else {
        require_login_or_redirect();
    }
}
