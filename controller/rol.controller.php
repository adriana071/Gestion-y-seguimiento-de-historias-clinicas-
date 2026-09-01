<?php 
require_once 'model/rol.php';

class RolController
{
    private $model;

    public function __construct()
    {
        $this->model = new rol();
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
        require_once 'view/vrol.php';
        require_once 'view/footer.php';
    }

    // FLUTTER
    public function ListarJson()
    {
        header('Content-Type: application/json; charset=utf-8');
        $roles = $this->model->f_ListarJson();

        if ($roles) {
            echo json_encode([
                "success" => true,
                "roles" => $roles
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No se encontraron roles"
            ]);
        }
    }

    public function InsEditar()
    {
        $usr = new rol();
        $campos = [
            "nombre",
            "estado"        // ✅ AGREGADO: capturar estado
        ];
        foreach ($campos as $campo) {
            $usr->$campo = $_POST[$campo] ?? '';
        }

        // ✅ AGREGADO: Capturar codRol para edición (fuera del foreach porque no está en $campos)
        if (isset($_POST['codRol']) && !empty($_POST['codRol'])) {
            $usr->codRol = $_POST['codRol'];
        }

        $success = false;
        $message = "";

        // 🔵 DIFERENCIAR NUEVO / EDITAR
        if (isset($_REQUEST['ac']) && $_REQUEST['ac'] === 'nuevo') {
            // ✅ AGREGADO: Asegurar que no tenga ID para nuevo
            unset($usr->codRol);
            $success = $this->model->Insertar($usr);
            $message = $success ? "Rol registrado correctamente." : "Error al registrar.";
        } 
        elseif (isset($_REQUEST['ac']) && $_REQUEST['ac'] === 'editar') {
            // ✅ AGREGADO: Verificar que el ID exista para editar
            if (!isset($usr->codRol) || empty($usr->codRol)) {
                header('Location: index.php?c=rol&error=ID no proporcionado');
                return;
            }
            $success = $this->model->Editar($usr);
            $message = $success ? "Rol actualizado correctamente." : "Error al actualizar.";
        }

        // 📱 RESPUESTA PARA FLUTTER
        if (isset($_REQUEST['dsn']) && $_REQUEST['dsn'] === 'flutter') {
            header('Content-Type: application/json');
            echo json_encode([
                "success" => $success,
                "message" => $message
            ]);
            return;
        }

        // 🔄 REDIRECCIÓN WEB
        if ($success) {
            header('Location: index.php?c=rol');
        } else {
            // ✅ AGREGADO: Mostrar error si falla
            echo "<pre>";
            echo "=== DATOS ENVIADOS ===\n";
            print_r($usr);
            echo "\n=== POST RECIBIDO ===\n";
            print_r($_POST);
            echo "</pre>";
            echo "<h3>Error al guardar los datos</h3>";
            exit();
        }
    }

    public function Eliminar()
    {
        $this->model->Eliminar($_REQUEST['codRol']);
        header('Location: index.php?c=rol');
    }
}
?>