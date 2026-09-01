<?php 
require_once 'model/condicionfisica.php';

class CondicionfisicaController
{
    private $model;

    public function __construct()
    {
        $this->model = new condicionfisica();
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
        require_once 'view/vcondicionfisica.php';
        require_once 'view/footer.php';
    }

    // FLUTTER
    public function ListarJson()
    {
        header('Content-Type: application/json; charset=utf-8');
        $condiciones = $this->model->f_ListarJson();

        if ($condiciones) {
            echo json_encode([
                "success" => true,
                "condiciones" => $condiciones
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No se encontraron condiciones físicas"
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

        $usr = new condicionfisica();
        
        // ✅ CAMPOS OBLIGATORIOS
        $camposObligatorios = [
            "ciPaciente", "peso", "estatura", "temperatura", "presionArterial"
        ];
        
        foreach ($camposObligatorios as $campo) {
            if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
                echo "<script>
                        alert('El campo $campo es obligatorio');
                        window.history.back();
                      </script>";
                exit();
            }
        }

        // ✅ ASIGNAR VALORES
        $usr->ciPaciente = $_POST['ciPaciente'];
        $usr->peso = $_POST['peso'];
        $usr->estatura = $_POST['estatura'];
        $usr->temperatura = $_POST['temperatura'];
        $usr->presionArterial = $_POST['presionArterial'];
        $usr->estado = $_POST['estado'] ?? 1;
        
        // ✅ CAPTURAR ID PARA EDICIÓN
        $accion = isset($_REQUEST['ac']) ? $_REQUEST['ac'] : '';
        
        if ($accion === 'editar') {
            if (!isset($_POST['idCondicion']) || empty($_POST['idCondicion'])) {
                echo "<script>
                        alert('Error: No se recibió el ID de la condición');
                        window.history.back();
                      </script>";
                exit();
            }
            $usr->idCondicion = $_POST['idCondicion'];
        }

        $success = false;
        $message = "";

        try {
            if ($accion === 'nuevo') {
                $success = $this->model->Insertar($usr);
                $message = $success ? "Condición física registrada correctamente." : "Error al registrar.";
            } elseif ($accion === 'editar') {
                $success = $this->model->Editar($usr);
                $message = $success ? "Condición física actualizada correctamente." : "Error al actualizar.";
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
            header('Location: index.php?c=condicionfisica&msg=' . urlencode($message));
            exit();
        } else {
            echo "<h3>Error al guardar los datos</h3>";
            echo "<p>$message</p>";
            echo "<pre>";
            print_r($_POST);
            echo "</pre>";
            exit();
        }
    }

    public function Eliminar()
    {
        if (!isset($_REQUEST['idCondicion'])) {
            header('Location: index.php?c=condicionfisica&msg=' . urlencode('ID no proporcionado'));
            exit();
        }
        
        $success = $this->model->Eliminar($_REQUEST['idCondicion']);
        $message = $success ? "Condición física eliminada correctamente." : "Error al eliminar.";
        
        header('Location: index.php?c=condicionfisica&msg=' . urlencode($message));
        exit();
    }
}