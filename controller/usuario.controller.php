<?php
require_once 'model/usuario.php';

class UsuarioController
{
    private $model;

    public function __construct()
    {
        $this->model = new usuario();
    }

    public function Index()
    {
        require_once 'view/header.php';
        require_once 'view/dashboard.php';
        require_once 'view/footer.php';
    }

    public function IndexPage()
    {
        require_once 'view/header.php';
        require_once 'view/vusuario.php';
        require_once 'view/footer.php';
    }

    // FLUTTER
    public function ListarJson()
    {
        header('Content-Type: application/json; charset=utf-8');
        $usuarios = $this->model->f_ListarJson();

        if ($usuarios) {
            echo json_encode([
                "success" => true,
                "usuarios" => $usuarios
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No se encontraron usuarios"
            ]);
        }
    }

    public function InsEditar()
    {
        $usr = new usuario();

        $campos = [
            "idUsuario",
            "ciPersonal",
            "login",
            "password"
            
        ];

        foreach ($campos as $campo) {
            $usr->$campo = $_POST[$campo] ?? '';
        }

        // ✅ OBTENER EL NOMBRE DEL ROL
        $nombreRol = $_POST['nombreRol'] ?? '';

        // 🔐 ENCRIPTAR PASSWORD SOLO SI SE ENVÍA
        if (!empty($usr->password)) {
            $usr->password = password_hash($usr->password, PASSWORD_DEFAULT);
        }

        // ============================
        // 🚨 VALIDACIONES
        // ============================

        // ❌ CAMPOS VACÍOS
        if (empty($usr->ciPersonal) || empty($usr->login) || empty($nombreRol)) {
            die("<h3>Todos los campos son obligatorios</h3>");
        }

        // ❌ VALIDAR LOGIN REPETIDO
        if ($this->model->ExisteLogin($usr->login, $usr->idUsuario)) {
            die("<h3>El usuario ya existe (login repetido)</h3>");
        }

        // ❌ VALIDAR PERSONAL YA TIENE USUARIO
        if ($this->model->ExistePersonal($usr->ciPersonal, $usr->idUsuario)) {
            die("<h3>Este personal ya tiene un usuario asignado</h3>");
        }

        // ============================

        $success = false;

        if (isset($_REQUEST['ac']) && $_REQUEST['ac'] === 'nuevo') {
            $success = $this->model->Insertar($usr, $nombreRol);
        } elseif (isset($_REQUEST['ac']) && $_REQUEST['ac'] === 'editar') {
            $success = $this->model->Editar($usr, $nombreRol);
        }

        if ($success) {
            header('Location: index.php?c=usuario');
            exit();
        } else {
            echo "<h3>Error al guardar los datos.</h3>";
            exit();
        }
    }

    public function Eliminar()
    {
        $this->model->Eliminar($_REQUEST['idUsuario']);
        header('Location: index.php?c=usuario');
    }
}