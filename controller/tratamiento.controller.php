<?php
require_once 'model/tratamiento.php';

class tratamientoController
{
    private $model;
    
    public function __construct()
    {
        $this->model = new tratamiento();
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
        require_once 'view/vtratamiento.php';
        require_once 'view/footer.php';
    }
    
    // FLUTTER
    public function ListarJson()
    {
        header('Content-Type: application/json; charset=utf-8');
        $tratamientos = $this->model->f_ListarJson();
        if ($tratamientos) {
            echo json_encode([
                "success" => true,
                "tratamientos" => $tratamientos
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No se encontraron tratamientos"
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

        $trat = new tratamiento();
        
        // ✅ ASIGNAR VALORES CORRECTAMENTE
        $trat->idConsulta = isset($_POST['idConsulta']) ? trim($_POST['idConsulta']) : null;
        $trat->nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
        $trat->descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;
        
        // ✅ CAPTURAR ID SI VIENE (para edición)
        $trat->idTratamiento = isset($_POST['idTratamiento']) && !empty($_POST['idTratamiento']) 
                              ? $_POST['idTratamiento'] 
                              : null;
        
        // ✅ VALIDAR CAMPOS OBLIGATORIOS
        if (empty($trat->nombre)) {
            echo "<script>
                    alert('El campo nombre es obligatorio');
                    window.history.back();
                  </script>";
            exit();
        }
        
        if (empty($trat->idConsulta)) {
            echo "<script>
                    alert('El campo ID de consulta es obligatorio');
                    window.history.back();
                  </script>";
            exit();
        }

        $accion = isset($_REQUEST['ac']) ? $_REQUEST['ac'] : '';
        $success = false;
        $message = "";
        $idGenerado = null;

        try {
            if ($accion === 'nuevo') {
                // ✅ Para nuevo, asegurar que ID sea null
                $trat->idTratamiento = null;
                $idGenerado = $this->model->Insertar($trat);
                $success = ($idGenerado !== false);
                $message = $success ? "Tratamiento registrado correctamente. ID: " . $idGenerado : "Error al registrar.";
                
            } elseif ($accion === 'editar') {
                // ✅ Para editar, verificar que tenga ID
                if (empty($trat->idTratamiento)) {
                    echo "<script>
                            alert('Error: ID de tratamiento no proporcionado');
                            window.history.back();
                          </script>";
                    exit();
                }
                $success = $this->model->Editar($trat);
                $message = $success ? "Tratamiento actualizado correctamente." : "Error al actualizar.";
                
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
                "message" => $message,
                "idTratamiento" => $idGenerado
            ]);
            return;
        }

        // 🔄 REDIRECCIÓN WEB
        if ($success) {
            header('Location: index.php?c=tratamiento&msg=' . urlencode($message));
            exit();
        } else {
            // 🔍 MOSTRAR ERROR PARA DEPURACIÓN
            echo "<h3>Error al guardar los datos</h3>";
            echo "<p>$message</p>";
            echo "<pre>";
            echo "=== DATOS ENVIADOS ===\n";
            print_r($trat);
            echo "\n=== POST RECIBIDO ===\n";
            print_r($_POST);
            echo "</pre>";
            exit();
        }
    }
    
    public function Eliminar()
    {
        if (!isset($_REQUEST['idTratamiento']) || empty($_REQUEST['idTratamiento'])) {
            header('Location: index.php?c=tratamiento&msg=' . urlencode('ID no proporcionado'));
            exit();
        }
        
        $success = $this->model->Eliminar($_REQUEST['idTratamiento']);
        $message = $success ? "Tratamiento eliminado correctamente." : "Error al eliminar.";
        
        header('Location: index.php?c=tratamiento&msg=' . urlencode($message));
        exit();
    }
}
?>