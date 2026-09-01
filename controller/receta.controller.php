<?php 
require_once 'model/receta.php';

class RecetaController
{
    private $model;

    public function __construct()
    {
        $this->model = new receta();
    }    

    public function Index()
    {
        require_once 'view/header.php';
        require_once 'view/dashboard.php';
        require_once 'view/footer.php';
    }

    public function IndexPage()
    {
        $recetas = $this->model->Listar();
        $medicamentos = $this->model->ListarMedicamentos();
        
        require_once 'view/header.php';
        require_once 'view/vreceta.php';
        require_once 'view/footer.php';
    }

    /**
     * Página para gestionar recetas de una consulta específica
     */
    public function GestionarReceta()
    {
        $idConsulta = isset($_GET['idConsulta']) ? (int)$_GET['idConsulta'] : 0;
        
        if ($idConsulta <= 0) {
            header('Location: index.php?c=receta&msg=' . urlencode('ID de consulta no válido'));
            exit();
        }
        
        // Obtener datos
        $consulta = $this->getConsultaInfo($idConsulta);
        $recetas = $this->model->ListarPorConsulta($idConsulta);
        $medicamentos = $this->model->ListarMedicamentos();
        $tratamientos = $this->getTratamientosPorConsulta($idConsulta);
        
        // PASAR LAS VARIABLES A LA VISTA
        require_once 'view/header.php';
        require_once 'view/vreceta.php'; // <-- Aquí carga vreceta.php
        require_once 'view/footer.php';
    }

    /**
     * Lista los tratamientos registrados para una consulta (para poder
     * elegir a cuál tratamiento se le agrega cada medicamento).
     */
    private function getTratamientosPorConsulta($idConsulta)
    {
        try {
            $conex = conexion::Conectar();
            $stmt = $conex->prepare("SELECT idTratamiento, nombre FROM tratamiento WHERE idConsulta = :idConsulta AND estado = 1 ORDER BY idTratamiento");
            $stmt->bindParam(':idConsulta', $idConsulta);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("getTratamientosPorConsulta Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener información de la consulta
     */
    private function getConsultaInfo($idConsulta)
    {
        try {
            $conex = conexion::Conectar();
            $stmt = $conex->prepare("SELECT * FROM vs_vista_consultas WHERE idConsulta = :idConsulta");
            $stmt->bindParam(':idConsulta', $idConsulta);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("getConsultaInfo Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Listar recetas en formato JSON (para Flutter)
     */
    public function ListarJson()
    {
        header('Content-Type: application/json; charset=utf-8');
        $recetas = $this->model->f_ListarJson();

        if ($recetas) {
            echo json_encode([
                "success" => true,
                "recetas" => $recetas
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No se encontraron recetas"
            ]);
        }
    }
    
    /**
     * Listar recetas por consulta en JSON
     */
    public function ListarPorConsultaJson()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $idConsulta = isset($_GET['idConsulta']) ? (int)$_GET['idConsulta'] : 0;
        
        if ($idConsulta <= 0) {
            echo json_encode([
                "success" => false,
                "message" => "ID de consulta no válido"
            ]);
            return;
        }
        
        $recetas = $this->model->ListarPorConsulta($idConsulta);
        
        if ($recetas) {
            echo json_encode([
                "success" => true,
                "recetas" => $recetas
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No se encontraron recetas para esta consulta"
            ]);
        }
    }

    /**
     * Insertar o editar detalle de receta
     */
    public function InsEditar()
    {
        if (empty($_POST)) {
            echo "<script>
                    alert('No se recibieron datos del formulario');
                    window.history.back();
                  </script>";
            exit();
        }

        $usr = new receta();
        
        $campos = ["idTratamiento", "idMedicamento", "dosis", "cantidad", "frecuencia", "viaAdministracion", "duracion"];
        
        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
                echo "<script>
                        alert('El campo $campo es obligatorio');
                        window.history.back();
                      </script>";
                exit();
            }
        }

        $usr->idTratamiento = (int)$_POST['idTratamiento'];
        $usr->idMedicamento = (int)$_POST['idMedicamento'];
        $usr->dosis = trim($_POST['dosis']);
        $usr->cantidad = (int)$_POST['cantidad'];
        $usr->frecuencia = trim($_POST['frecuencia']);
        $usr->viaAdministracion = trim($_POST['viaAdministracion']);
        $usr->duracion = trim($_POST['duracion']);
        
        $accion = isset($_REQUEST['ac']) ? $_REQUEST['ac'] : '';
        
        $success = false;
        $message = "";

        try {
            if ($accion === 'nuevo') {
                $success = $this->model->InsertarDetalle($usr);
                $message = $success ? "Medicamento registrado en la receta correctamente." : "Error al registrar.";
            } elseif ($accion === 'editar') {
                $success = $this->model->EditarDetalle($usr);
                $message = $success ? "Medicamento actualizado correctamente." : "Error al actualizar.";
            } else {
                $message = "Acción no válida";
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }

        if (isset($_REQUEST['dsn']) && $_REQUEST['dsn'] === 'flutter') {
            header('Content-Type: application/json');
            echo json_encode([
                "success" => $success,
                "message" => $message
            ]);
            return;
        }

        if ($success) {
            header('Location: index.php?c=receta&a=GestionarReceta&idConsulta=' . $usr->idTratamiento . '&msg=' . urlencode($message));
            exit();
        } else {
            echo "<h3>Error al guardar los datos</h3>";
            echo "<p>$message</p>";
            echo "<pre>";
            print_r($usr);
            print_r($_POST);
            echo "</pre>";
            exit();
        }
    }

    /**
     * Eliminar detalle de receta
     */
    public function Eliminar()
    {
        $idTratamiento = isset($_GET['idTratamiento']) ? (int)$_GET['idTratamiento'] : 0;
        $idMedicamento = isset($_GET['idMedicamento']) ? (int)$_GET['idMedicamento'] : 0;
        $idConsulta = isset($_GET['idConsulta']) ? (int)$_GET['idConsulta'] : 0;
        
        if ($idTratamiento <= 0 || $idMedicamento <= 0) {
            header('Location: index.php?c=receta&a=GestionarReceta&idConsulta=' . $idConsulta . '&msg=' . urlencode('ID no válido'));
            exit();
        }
        
        $success = $this->model->EliminarDetalle($idTratamiento, $idMedicamento);
        $message = $success ? "Medicamento eliminado de la receta correctamente." : "Error al eliminar.";
        
        header('Location: index.php?c=receta&a=GestionarReceta&idConsulta=' . $idConsulta . '&msg=' . urlencode($message));
        exit();
    }
}