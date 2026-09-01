<?php 
require_once 'model/especialidad.php';

class EspecialidadController
{
    private $model;

    public function __construct()
    {
        $this->model = new especialidad();
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
        require_once 'view/vespecialidad.php';
        require_once 'view/footer.php';
    }

    // FLUTTER
    public function ListarJson()
    {
        header('Content-Type: application/json; charset=utf-8');
        $especialidades = $this->model->f_ListarJson();

        if ($especialidades) {
            echo json_encode([
                "success" => true,
                "especialidades" => $especialidades
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No se encontraron especialidades"
            ]);
        }
    }

    public function InsEditar()
    {
        $usr = new especialidad();
        $campos = [
            "nombre",
            "estado"        // ✅ AGREGADO: capturar estado
        ];
        foreach ($campos as $campo) {
            $usr->$campo = $_POST[$campo] ?? '';
        }

        // ✅ AGREGADO: Capturar codRol para edición (fuera del foreach porque no está en $campos)
        if (isset($_POST['codEspecialidad']) && !empty($_POST['codEspecialidad'])) {
            $usr->codEspecialidad = $_POST['codEspecialidad'];
        }

        $success = false;
        $message = "";

        // 🔵 DIFERENCIAR NUEVO / EDITAR
        if (isset($_REQUEST['ac']) && $_REQUEST['ac'] === 'nuevo') {
            // ✅ AGREGADO: Asegurar que no tenga ID para nuevo
            unset($usr->codEspecialidad);
            $success = $this->model->Insertar($usr);
            $message = $success ? "Especialidad registrada correctamente." : "Error al registrar.";
        } 
        elseif (isset($_REQUEST['ac']) && $_REQUEST['ac'] === 'editar') {
            // ✅ AGREGADO: Verificar que el ID exista para editar
            if (!isset($usr->codEspecialidad) || empty($usr->codEspecialidad)) {
                header('Location: index.php?c=especialidad&error=ID no proporcionado');
                return;
            }
            $success = $this->model->Editar($usr);
            $message = $success ? "Especialidad actualizada correctamente." : "Error al actualizar.";
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
            header('Location: index.php?c=especialidad');
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
        $this->model->Eliminar($_REQUEST['codEspecialidad']);
        header('Location: index.php?c=especialidad');
    }
}
?>