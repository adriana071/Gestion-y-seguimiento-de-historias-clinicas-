<?php
class Sesion
{
    public static function iniciar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function establecerDatos($usuario)
{
    $_SESSION['idUsuario'] = $usuario['idUsuario'] ?? null;

    $_SESSION['ciPersonal'] = $usuario['ciPersonal'] ?? null;
    $_SESSION['codEspecialidad'] = $usuario['codEspecialidad'] ?? null;
    $_SESSION['especialidad'] = $usuario['especialidad'] ?? '';

    $_SESSION['nombre'] = $usuario['nombre'] ?? '';
    $_SESSION['apaterno'] = $usuario['apaterno'] ?? '';
    $_SESSION['apmaterno'] = $usuario['apmaterno'] ?? '';
    $_SESSION['nombreCompleto'] = $usuario['nombreCompleto'] ?? '';

    $_SESSION['rol'] = $usuario['rol'] ?? '';
    $_SESSION['login'] = $usuario['email'] ?? '';
    $_SESSION['foto'] = $usuario['foto'] ?? '';
}

    public static function obtenerNombreCompleto()
    {
        return trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apaterno'] ?? '') . ' ' . ($_SESSION['apmaterno'] ?? ''));
    }

    public static function obtenerLogin()
    {
        return $_SESSION['login'] ?? '';
    }

    public static function obtenerRol()
    {
        return $_SESSION['rol'] ?? '';
    }

    public static function obtenerIdUsuario()
    {
        return $_SESSION['idUsuario'] ?? null;
    }
      // ✅ NUEVO: OBTENER FOTO
      public static function obtenerFoto()
      {
          return $_SESSION['foto'] ?? '';
      }

    public static function cerrar()
    {
        session_unset();
        session_destroy();
    }
}
?>