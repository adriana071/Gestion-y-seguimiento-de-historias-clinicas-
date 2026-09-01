<?php
require_once 'conexion.php';

class medicamento
{
    private $conex;
    public $idMedicamento;
    public $nombre;
    public $observaciones;
    public $estado;

    public function __construct(){
        try {
            $this->conex = conexion::Conectar();
        } catch (PDOException $e) {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }       
    }
    
    public function Listar()
    {
        try {
            $stmt = $this->conex->prepare("SELECT * FROM medicamento WHERE estado = 1");
            $stmt->execute();
            $medicamentos = $stmt->fetchAll(PDO::FETCH_OBJ);
            return $medicamentos;  
        } catch (PDOException $e) {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }
    }
    
    public function f_ListarJson()
    {
        try {
            $stmt = $this->conex->prepare("SELECT * FROM medicamento WHERE estado = 1");
            $stmt->execute();
            $users = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $users[] = $row;                
            }          
            return $users;  
        } catch (PDOException $e) {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }
    }
    
    public function Insertar($usr)
    {
        try {
            $stmt = $this->conex->prepare("CALL sp_insertar_medicamento(:nombre, :observaciones)");
            
            $stmt->bindParam(':nombre', $usr->nombre);
            $stmt->bindParam(':observaciones', $usr->observaciones);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error en Insertar: " . $e->getMessage());
        }
    }
    
    public function Editar($usr)
    {
        try {
            if (!isset($usr->idMedicamento) || empty($usr->idMedicamento)) {
                return false;
            }
            
            // ✅ USAR CONSULTA DIRECTA PARA PROBAR (TEMPORAL)
            $sql = "UPDATE medicamento SET 
                        nombre = :nombre, 
                        observaciones = :observaciones, 
                        estado = :estado 
                    WHERE idMedicamento = :idMedicamento";
            
            $stmt = $this->conex->prepare($sql);
            $stmt->bindParam(':idMedicamento', $usr->idMedicamento);
            $stmt->bindParam(':nombre', $usr->nombre);
            $stmt->bindParam(':observaciones', $usr->observaciones);
            $stmt->bindParam(':estado', $usr->estado);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            die("Error en Editar: " . $e->getMessage());
        }
    }

    public function Eliminar($idMedicamento)
    {
        try {
            // ✅ USAR CONSULTA DIRECTA (TEMPORAL)
            $sql = "UPDATE medicamento SET estado = 0 WHERE idMedicamento = :idMedicamento";
            $stmt = $this->conex->prepare($sql);
            $stmt->bindParam(':idMedicamento', $idMedicamento);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            die("Error en Eliminar: " . $e->getMessage());
        }
    }
}