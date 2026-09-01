<?php
require_once 'conexion.php';
class rol
{
    private $conex;
    public $codRol;
    public $nombre;
    public $estado;
   

    public function __construct(){
        try {
            $this->conex = conexion::Conectar();
        } catch (PDOException $e)
        {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }       
    }
    public function Listar()
    {
        try {
            $stmt = $this->conex->prepare("SELECT * FROM vs_vista_roles");
            $stmt->execute();
            $pacientes = $stmt->fetchAll(PDO::FETCH_OBJ);
            return $pacientes;  
        } catch (PDOException $e) {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }
    }
    public function f_ListarJson()
    {
        try {
            $stmt = $this->conex->prepare("SELECT * FROM vs_vista_roles");
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
        try
        {            
            $stmt = $this->conex->prepare("SELECT * FROM {$tableName} WHERE estado=1");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
            return $rows;
        }
        catch(PDOException $e)
        {
            error_log("Editar Error: " . $e->getMessage());
            return false;
        }        
    }
    public function Insertar(rol $usr)
    {
        try {
            // Llamada al procedimiento almacenado InsertarRol
            $stmt = $this->conex->prepare("CALL sp_insertar_rol(:nombre)");
            $stmt->bindParam(':nombre', $usr->nombre);
          
            return $stmt->execute(); 
        } catch (PDOException $e) {
            error_log("Editar Error: " . $e->getMessage());
            return false;
        }
    }    
    public function Editar(rol $usr)
    {
        try {
            // Llamada al procedimiento almacenado EditarRol
            $stmt = $this->conex->prepare("CALL sp_editar_rol(:codRol, :nombre)");
            $stmt->bindParam(':codRol', $usr->codRol);
            $stmt->bindParam(':nombre', $usr->nombre);
            return $stmt->execute(); 
        } catch (PDOException $e) {
            error_log("Editar Error: " . $e->getMessage());
            return false;
        }
    }

    public function Eliminar($codRol)
    {
        try {
            // Llamada al procedimiento almacenado EliminarROL
            $stmt = $this->conex->prepare("CALL sp_eliminar_rol(:codRol)");
            $stmt->bindParam(':codRol', $codRol);
            $stmt->execute();
        } catch (PDOException $e) {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }
    }


}