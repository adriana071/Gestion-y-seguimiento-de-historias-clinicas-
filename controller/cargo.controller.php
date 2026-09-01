<?php 
require_once 'model/cargo.php';

class CargoController
{
    private $model;

    public function __construct()
    {
        $this->model = new cargo();
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
        require_once 'view/vcargo.php';
        require_once 'view/footer.php';
    }

    // FLUTTER
    public function ListarJson()
    {
        header('Content-Type: application/json; charset=utf-8');
        $cargos = $this->model->f_ListarJson();

        if ($cargos) {
            echo json_encode([
                "success" => true,
                "cargos" => $cargos
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No se encontraron cargos"
            ]);
        }
    }

    public function InsEditar()
    {
        // ✅ VERIFICAR QUE LLEGUEN DATOS
        if (empty($_POST)) {
            echo "<script>
                    alert('No se recibieron datos del formulario');
                    window.history.back();
                  </script>";
            exit();
        }

        $usr = new cargo();
        
        // ✅ CAMPOS OBLIGATORIOS
        $campos = ["nombre"];
        
        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
                echo "<script>
                        alert('El campo $campo es obligatorio');
                        window.history.back();
                      </script>";
                exit();
            }
        }

        // ✅ ASIGNAR VALORES
        $usr->nombre = trim($_POST['nombre']);
        $usr->estado = $_POST['estado'] ?? 1;
        
        // ✅ CAPTURAR ID PARA EDICIÓN
        $accion = isset($_REQUEST['ac']) ? $_REQUEST['ac'] : '';
        
        if ($accion === 'editar') {
            // ✅ VERIFICAR QUE EL ID EXISTA
            if (!isset($_POST['codCargo']) || empty($_POST['codCargo'])) {
                echo "<script>
                        alert('Error: No se recibió el ID del cargo');
                        window.history.back();
                      </script>";
                exit();
            }
            $usr->codCargo = $_POST['codCargo'];
        }

        $success = false;
        $message = "";

        try {
            // 🔵 DIFERENCIAR NUEVO / EDITAR
            if ($accion === 'nuevo') {
                $success = $this->model->Insertar($usr);
                $message = $success ? "Cargo registrado correctamente." : "Error al registrar.";
            } elseif ($accion === 'editar') {
                $success = $this->model->Editar($usr);
                $message = $success ? "Cargo actualizado correctamente." : "Error al actualizar.";
            } else {
                $message = "Acción no válida";
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
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
            header('Location: index.php?c=cargo&msg=' . urlencode($message));
            exit();
        } else {
            // 🔍 MOSTRAR ERROR PARA DEPURACIÓN
            echo "<h3>Error al guardar los datos</h3>";
            echo "<p>$message</p>";
            echo "<pre>";
            echo "=== DATOS ENVIADOS ===\n";
            print_r($usr);
            echo "\n=== POST RECIBIDO ===\n";
            print_r($_POST);
            echo "</pre>";
            exit();
        }
    }

    public function Eliminar()
    {
        if (!isset($_REQUEST['codCargo'])) {
            header('Location: index.php?c=cargo&msg=' . urlencode('ID no proporcionado'));
            exit();
        }
        
        $success = $this->model->Eliminar($_REQUEST['codCargo']);
        $message = $success ? "Cargo eliminado correctamente." : "Error al eliminar.";
        
        header('Location: index.php?c=cargo&msg=' . urlencode($message));
        exit();
    }
}