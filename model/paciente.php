<?php
require_once 'conexion.php';

class paciente
{
    private $conex;
    public $ciPaciente;
    public $codigoPaciente;
    public $nombre;
    public $apaterno;
    public $apmaterno;
    public $fechaNacimiento;
    public $genero;  
    public $telefono; 
    public $email; 
    public $direccion;
    public $seguroSalud;
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
            $stmt = $this->conex->prepare("SELECT * FROM vs_vista_pacientes");
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
            $stmt = $this->conex->prepare("SELECT * FROM vs_vista_pacientes");
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

    public function ObtenerPorCI($ci)
    {
        try {
            $stmt = $this->conex->prepare("SELECT *, (YEAR(CURDATE()) - YEAR(fechaNacimiento)) AS edad FROM paciente WHERE ciPaciente = ? AND estado = 1");
            $stmt->execute([$ci]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("ObtenerPorCI Error: " . $e->getMessage());
            return null;
        }
    }

    public function Insertar(paciente $usr)
    {
        try {
           
            $stmt = $this->conex->prepare("CALL sp_insertar_paciente(:ciPaciente, :codigoPaciente, :nombre, :apaterno, :apmaterno, :fechaNacimiento, :genero, :telefono, :email, :direccion, :seguroSalud)");
            
            $stmt->bindParam(':ciPaciente', $usr->ciPaciente);
            $stmt->bindParam(':codigoPaciente', $usr->codigoPaciente);
            $stmt->bindParam(':nombre', $usr->nombre);
            $stmt->bindParam(':apaterno', $usr->apaterno);
            $stmt->bindParam(':apmaterno', $usr->apmaterno);
            $stmt->bindParam(':fechaNacimiento', $usr->fechaNacimiento);
            $stmt->bindParam(':genero', $usr->genero);
            $stmt->bindParam(':telefono', $usr->telefono);
            $stmt->bindParam(':email', $usr->email);
            $stmt->bindParam(':direccion', $usr->direccion);
            $stmt->bindParam(':seguroSalud', $usr->seguroSalud);
          
            return $stmt->execute(); 
            
        } catch (PDOException $e) {
            error_log("Insertar Error: " . $e->getMessage());
            return false;
        }
    }    

    public function Editar(paciente $usr)
    {
        try {
          
            $stmt = $this->conex->prepare("CALL sp_editar_paciente(:ciPaciente, :codigoPaciente, :nombre, :apaterno, :apmaterno, :fechaNacimiento, :genero, :telefono, :email, :direccion, :seguroSalud)");
            
            $stmt->bindParam(':ciPaciente', $usr->ciPaciente);
            $stmt->bindParam(':codigoPaciente', $usr->codigoPaciente);
            $stmt->bindParam(':nombre', $usr->nombre);
            $stmt->bindParam(':apaterno', $usr->apaterno);
            $stmt->bindParam(':apmaterno', $usr->apmaterno);
            $stmt->bindParam(':fechaNacimiento', $usr->fechaNacimiento);
            $stmt->bindParam(':genero', $usr->genero);
            $stmt->bindParam(':telefono', $usr->telefono);
            $stmt->bindParam(':email', $usr->email);
            $stmt->bindParam(':direccion', $usr->direccion);
            $stmt->bindParam(':seguroSalud', $usr->seguroSalud);
            
            return $stmt->execute(); 
            
        } catch (PDOException $e) {
            error_log("Editar Error: " . $e->getMessage());
            return false;
        }
    }

    public function Eliminar($ciPaciente)
    {
        try {
            // ✅ CORREGIDO - Usa el nombre correcto
            $stmt = $this->conex->prepare("CALL sp_eliminar_paciente(:ciPaciente)");
            $stmt->bindParam(':ciPaciente', $ciPaciente);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Eliminar Error: " . $e->getMessage());
            return false;
        }
    }
}