<?php
require_once 'model/medicamento.php';

class MedicamentoController
{
    private $model;
    
    public function __construct()
    {
        $this->model = new medicamento();
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
        require_once 'view/vmedicamento.php';
        require_once 'view/footer.php';
    }
    
    // FLUTTER
    public function ListarJson()
    {
        header('Content-Type: application/json; charset=utf-8');
        $medicamentos = $this->model->f_ListarJson();
        if ($medicamentos) {
            echo json_encode([
                "success" => true,
                "medicamentos" => $medicamentos
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No se encontraron medicamentos"
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

        $usr = new medicamento();
        
        // ✅ ASIGNAR VALORES CORRECTAMENTE
        $usr->nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
        $usr->observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : null;
        $usr->estado = isset($_POST['estado']) ? $_POST['estado'] : 1;
        
        // ✅ CAPTURAR ID SI VIENE (para edición)
        $usr->idMedicamento = isset($_POST['idMedicamento']) && !empty($_POST['idMedicamento']) 
                              ? $_POST['idMedicamento'] 
                              : null;
        
        // ✅ VALIDAR CAMPOS OBLIGATORIOS
        if (empty($usr->nombre)) {
            echo "<script>
                    alert('El campo nombre es obligatorio');
                    window.history.back();
                  </script>";
            exit();
        }

        $accion = isset($_REQUEST['ac']) ? $_REQUEST['ac'] : '';
        $success = false;
        $message = "";

        try {
            if ($accion === 'nuevo') {
                // ✅ Para nuevo, asegurar que ID sea null
                $usr->idMedicamento = null;
                $success = $this->model->Insertar($usr);
                $message = $success ? "Medicamento registrado correctamente." : "Error al registrar.";
                
            } elseif ($accion === 'editar') {
                // ✅ Para editar, verificar que tenga ID
                if (empty($usr->idMedicamento)) {
                    echo "<script>
                            alert('Error: ID de medicamento no proporcionado');
                            window.history.back();
                          </script>";
                    exit();
                }
                $success = $this->model->Editar($usr);
                $message = $success ? "Medicamento actualizado correctamente." : "Error al actualizar.";
                
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
            header('Location: index.php?c=medicamento&msg=' . urlencode($message));
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
        if (!isset($_REQUEST['idMedicamento']) || empty($_REQUEST['idMedicamento'])) {
            header('Location: index.php?c=medicamento&msg=' . urlencode('ID no proporcionado'));
            exit();
        }
        
        $success = $this->model->Eliminar($_REQUEST['idMedicamento']);
        $message = $success ? "Medicamento eliminado correctamente." : "Error al eliminar.";
        
        header('Location: index.php?c=medicamento&msg=' . urlencode($message));
        exit();
    }
}