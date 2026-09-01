<?php
require_once 'conexion.php';

class cargo
{
    private $conex;
    public $codCargo;
    public $nombre;
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
            $stmt = $this->conex->prepare("SELECT * FROM vs_vista_cargos");
            $stmt->execute();
            $cargos = $stmt->fetchAll(PDO::FETCH_OBJ);
            return $cargos;  
        } catch (PDOException $e) {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }
    }
    
    public function f_ListarJson()
    {
        try {
            $stmt = $this->conex->prepare("SELECT * FROM vs_vista_cargos");
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
    
    public function ListasDesplegable(string $tableName)
    {
        try {            
            $stmt = $this->conex->prepare("SELECT * FROM {$tableName} WHERE estado=1");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
            return $rows;
        } catch(PDOException $e) {
            error_log("ListasDesplegable Error: " . $e->getMessage());
            return false;
        }        
    }
    
    public function Insertar(cargo $usr)
    {
        try {
            $stmt = $this->conex->prepare("CALL sp_insertar_cargo(:nombre)");
            $stmt->bindParam(':nombre', $usr->nombre);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Insertar Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function Editar(cargo $usr)
    {
        try {
            // ✅ CORREGIDO - Asegurar que el ID exista
            if (!isset($usr->codCargo) || empty($usr->codCargo)) {
                error_log("Editar Error: ID de cargo no proporcionado");
                return false;
            }
            
            $stmt = $this->conex->prepare("CALL sp_editar_cargo(:codCargo, :nombre)");
            $stmt->bindParam(':codCargo', $usr->codCargo);
            $stmt->bindParam(':nombre', $usr->nombre);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Editar Error: " . $e->getMessage());
            return false;
        }
    }

    public function Eliminar($codCargo)
    {
        try {
            $stmt = $this->conex->prepare("CALL sp_eliminar_cargo(:codCargo)");
            $stmt->bindParam(':codCargo', $codCargo);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Eliminar Error: " . $e->getMessage());
            return false;
        }
    }
}