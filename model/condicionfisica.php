<?php
require_once 'conexion.php';

class condicionfisica
{
    private $conex;
    public $idCondicion;
    public $ciPaciente;
    public $peso;
    public $estatura;
    public $temperatura;
    public $presionArterial;
    public $estado;
    public $fechaRegistro;

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
            $stmt = $this->conex->prepare("SELECT * FROM vs_vista_condicionFisicas");
            $stmt->execute();
            $condiciones = $stmt->fetchAll(PDO::FETCH_OBJ);
            return $condiciones;  
        } catch (PDOException $e) {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }
    }
    
    public function f_ListarJson()
    {
        try {
            $stmt = $this->conex->prepare("SELECT * FROM vs_vista_condicionFisicas");
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
    
    public function ListarPacientes()
    {
        try {
            $stmt = $this->conex->prepare("SELECT ciPaciente, nombre, apaterno, apmaterno FROM paciente WHERE estado = 1 ORDER BY nombre");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("ListarPacientes Error: " . $e->getMessage());
            return [];
        }
    }
    
    
    public function Insertar($usr)
    {
        try {
            $stmt = $this->conex->prepare("CALL sp_insertar_condicion_fisica(
                :ciPaciente, :peso, :estatura, :temperatura, :presionArterial
            )");
            
            $stmt->bindParam(':ciPaciente', $usr->ciPaciente);
            $stmt->bindParam(':peso', $usr->peso);
            $stmt->bindParam(':estatura', $usr->estatura);
            $stmt->bindParam(':temperatura', $usr->temperatura);
            $stmt->bindParam(':presionArterial', $usr->presionArterial);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Insertar Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function Editar($usr)
    {
        try {
            if (!isset($usr->idCondicion) || empty($usr->idCondicion)) {
                error_log("Editar Error: ID de condición no proporcionado");
                return false;
            }
            
            $stmt = $this->conex->prepare("CALL sp_editar_condicion_fisica(
                :idCondicion, :peso, :estatura, :temperatura, :presionArterial
            )");
            
            $stmt->bindParam(':idCondicion', $usr->idCondicion);
            $stmt->bindParam(':peso', $usr->peso);
            $stmt->bindParam(':estatura', $usr->estatura);
            $stmt->bindParam(':temperatura', $usr->temperatura);
            $stmt->bindParam(':presionArterial', $usr->presionArterial);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Editar Error: " . $e->getMessage());
            return false;
        }
    }

    public function Eliminar($idCondicion)
    {
        try {
            $stmt = $this->conex->prepare("CALL sp_eliminar_condicion_fisica(:idCondicion)");
            $stmt->bindParam(':idCondicion', $idCondicion);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Eliminar Error: " . $e->getMessage());
            return false;
        }
    }
}