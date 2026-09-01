<?php
require_once 'conexion.php';

class receta
{
    private $conex;
    public $idReceta;
    public $idConsulta;
    public $idTratamiento;
    public $idMedicamento;
    public $dosis;
    public $cantidad;
    public $frecuencia;
    public $viaAdministracion;
    public $duracion;
    public $estado;

    public function __construct(){
        try {
            $this->conex = conexion::Conectar();
        } catch (PDOException $e) {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }       
    }
    
    /**
     * Listar todas las recetas
     */
    public function Listar()
    {
        try {
            $stmt = $this->conex->prepare("SELECT * FROM vs_vista_recetas");
            $stmt->execute();
            $recetas = $stmt->fetchAll(PDO::FETCH_OBJ);
            return $recetas;  
        } catch (PDOException $e) {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }
    }
    
    /**
     * Listar recetas por ID de consulta
     */
    public function ListarPorConsulta($idConsulta)
    {
        try {
            $stmt = $this->conex->prepare("SELECT * FROM vs_vista_recetas WHERE idConsulta = :idConsulta");
            $stmt->bindParam(':idConsulta', $idConsulta);
            $stmt->execute();
            $recetas = $stmt->fetchAll(PDO::FETCH_OBJ);
            return $recetas;  
        } catch (PDOException $e) {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }
    }
    
    /**
     * Listar recetas en formato JSON (para Flutter)
     */
    public function f_ListarJson()
    {
        try {
            $stmt = $this->conex->prepare("SELECT * FROM vs_vista_recetas");
            $stmt->execute();
            $recetas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $recetas[] = $row;                
            }          
            return $recetas;  
        } catch (PDOException $e) {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }
    }
    
    /**
     * Listar medicamentos activos para desplegable
     */
    public function ListarMedicamentos()
    {
        try {            
            $stmt = $this->conex->prepare("SELECT * FROM medicamento WHERE estado = 1");
            $stmt->execute();
            $medicamentos = $stmt->fetchAll(PDO::FETCH_OBJ);
            return $medicamentos;
        } catch(PDOException $e) {
            error_log("ListarMedicamentos Error: " . $e->getMessage());
            return [];
        }        
    }
    
    /**
     * Obtener un detalle de receta por ID de tratamiento y medicamento
     */
    public function ObtenerDetalle($idTratamiento, $idMedicamento)
    {
        try {
            $stmt = $this->conex->prepare("SELECT * FROM detalleReceta WHERE idTratamiento = :idTratamiento AND idMedicamento = :idMedicamento");
            $stmt->bindParam(':idTratamiento', $idTratamiento);
            $stmt->bindParam(':idMedicamento', $idMedicamento);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("ObtenerDetalle Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Insertar detalle de receta
     */
    public function InsertarDetalle(receta $receta)
    {
        try {
            $stmt = $this->conex->prepare("CALL sp_insertar_detalle_receta(
                :idTratamiento, :idMedicamento, :dosis, :cantidad, 
                :frecuencia, :viaAdministracion, :duracion
            )");
            
            $stmt->bindParam(':idTratamiento', $receta->idTratamiento);
            $stmt->bindParam(':idMedicamento', $receta->idMedicamento);
            $stmt->bindParam(':dosis', $receta->dosis);
            $stmt->bindParam(':cantidad', $receta->cantidad);
            $stmt->bindParam(':frecuencia', $receta->frecuencia);
            $stmt->bindParam(':viaAdministracion', $receta->viaAdministracion);
            $stmt->bindParam(':duracion', $receta->duracion);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("InsertarDetalle Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Editar detalle de receta
     */
    public function EditarDetalle(receta $receta)
    {
        try {
            if (!isset($receta->idTratamiento) || empty($receta->idTratamiento) ||
                !isset($receta->idMedicamento) || empty($receta->idMedicamento)) {
                error_log("EditarDetalle Error: ID de tratamiento o medicamento no proporcionado");
                return false;
            }
            
            $stmt = $this->conex->prepare("CALL sp_editar_detalle_receta(
                :idTratamiento, :idMedicamento, :dosis, :cantidad, 
                :frecuencia, :viaAdministracion, :duracion
            )");
            
            $stmt->bindParam(':idTratamiento', $receta->idTratamiento);
            $stmt->bindParam(':idMedicamento', $receta->idMedicamento);
            $stmt->bindParam(':dosis', $receta->dosis);
            $stmt->bindParam(':cantidad', $receta->cantidad);
            $stmt->bindParam(':frecuencia', $receta->frecuencia);
            $stmt->bindParam(':viaAdministracion', $receta->viaAdministracion);
            $stmt->bindParam(':duracion', $receta->duracion);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("EditarDetalle Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar detalle de receta (cambiar estado a 0)
     */
    public function EliminarDetalle($idTratamiento, $idMedicamento)
    {
        try {
            $stmt = $this->conex->prepare("UPDATE detalleReceta SET estado = 0 WHERE idTratamiento = :idTratamiento AND idMedicamento = :idMedicamento");
            $stmt->bindParam(':idTratamiento', $idTratamiento);
            $stmt->bindParam(':idMedicamento', $idMedicamento);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("EliminarDetalle Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar todos los detalles de una receta por tratamiento
     */
    public function EliminarPorTratamiento($idTratamiento)
    {
        try {
            $stmt = $this->conex->prepare("UPDATE detalleReceta SET estado = 0 WHERE idTratamiento = :idTratamiento");
            $stmt->bindParam(':idTratamiento', $idTratamiento);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("EliminarPorTratamiento Error: " . $e->getMessage());
            return false;
        }
    }
}