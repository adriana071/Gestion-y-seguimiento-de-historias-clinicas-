<?php
require_once 'conexion.php';

class personalSalud
{
    private $conex;
    public $ciPersonal;
    public $nombre;
    public $apaterno;
    public $apmaterno;
    public $fechaNacimiento;
    public $genero;
    public $telefono;
    public $direccion;
    public $email;
    public $profesion;
    public $nacionalidad;
    public $tituloProfesional;
    public $anioTitulacion;
    public $universidad;
    public $tipoContrato;
    public $fechaIngreso;
    public $fechaFinContrato;
    public $afiliacionSeguro;
    public $nua;
    public $observaciones;
    public $foto;
    public $codCargo;
    public $codEspecialidad;
    public $estado;

    public function __construct()
    {
        try {
            $this->conex = conexion::Conectar();
        } catch (PDOException $e) {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }
    }
    
    public function Listar()
    {
        try {
            $stmt = $this->conex->prepare("SELECT * FROM vs_vista_personalsaluds");
            $stmt->execute();
            $personal_salud = $stmt->fetchAll(PDO::FETCH_OBJ);
            return $personal_salud;
        } catch (PDOException $e) {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }
    }
    
    public function f_ListarJson()
    {
        try {
            $stmt = $this->conex->prepare("SELECT * FROM vs_vista_personalsaluds");
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
        } catch (PDOException $e) {
            error_log("ListasDesplegable Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function Insertar($usr)
    {
        try {
            $stmt = $this->conex->prepare("CALL sp_insertar_personalSalud(
                :ciPersonal, :nombre, :apaterno, :apmaterno, :fechaNacimiento, :genero, 
                :telefono, :direccion, :email, :profesion, :nacionalidad, :tituloProfesional, 
                :anioTitulacion, :universidad, :tipoContrato, :fechaIngreso, :fechaFinContrato, 
                :afiliacionSeguro, :nua, :observaciones, :foto, :codCargo, :codEspecialidad
            )");
            
            $stmt->bindParam(':ciPersonal', $usr->ciPersonal);
            $stmt->bindParam(':nombre', $usr->nombre);
            $stmt->bindParam(':apaterno', $usr->apaterno);
            $stmt->bindParam(':apmaterno', $usr->apmaterno);
            $stmt->bindParam(':fechaNacimiento', $usr->fechaNacimiento);
            $stmt->bindParam(':genero', $usr->genero);
            $stmt->bindParam(':telefono', $usr->telefono);
            $stmt->bindParam(':direccion', $usr->direccion);
            $stmt->bindParam(':email', $usr->email);
            $stmt->bindParam(':profesion', $usr->profesion);
            $stmt->bindParam(':nacionalidad', $usr->nacionalidad);
            $stmt->bindParam(':tituloProfesional', $usr->tituloProfesional);
            $stmt->bindParam(':anioTitulacion', $usr->anioTitulacion);
            $stmt->bindParam(':universidad', $usr->universidad);
            $stmt->bindParam(':tipoContrato', $usr->tipoContrato);
            $stmt->bindParam(':fechaIngreso', $usr->fechaIngreso);
            $stmt->bindParam(':fechaFinContrato', $usr->fechaFinContrato);
            $stmt->bindParam(':afiliacionSeguro', $usr->afiliacionSeguro);
            $stmt->bindParam(':nua', $usr->nua);
            $stmt->bindParam(':observaciones', $usr->observaciones);
            $stmt->bindParam(':foto', $usr->foto);
            $stmt->bindParam(':codCargo', $usr->codCargo);
            $stmt->bindParam(':codEspecialidad', $usr->codEspecialidad);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            die("Error en Insertar: " . $e->getMessage());
        }
    }
    
    public function Editar($usr)
    {
        try {
            $stmt = $this->conex->prepare("CALL sp_editar_personalSalud(
                :ciPersonal, :nombre, :apaterno, :apmaterno, :fechaNacimiento, :genero, 
                :telefono, :direccion, :email, :profesion, :nacionalidad, :tituloProfesional, 
                :anioTitulacion, :universidad, :tipoContrato, :fechaIngreso, :fechaFinContrato, 
                :afiliacionSeguro, :nua, :observaciones, :foto, :codCargo, :codEspecialidad
            )");
            
            $stmt->bindParam(':ciPersonal', $usr->ciPersonal);
            $stmt->bindParam(':nombre', $usr->nombre);
            $stmt->bindParam(':apaterno', $usr->apaterno);
            $stmt->bindParam(':apmaterno', $usr->apmaterno);
            $stmt->bindParam(':fechaNacimiento', $usr->fechaNacimiento);
            $stmt->bindParam(':genero', $usr->genero);
            $stmt->bindParam(':telefono', $usr->telefono);
            $stmt->bindParam(':direccion', $usr->direccion);
            $stmt->bindParam(':email', $usr->email);
            $stmt->bindParam(':profesion', $usr->profesion);
            $stmt->bindParam(':nacionalidad', $usr->nacionalidad);
            $stmt->bindParam(':tituloProfesional', $usr->tituloProfesional);
            $stmt->bindParam(':anioTitulacion', $usr->anioTitulacion);
            $stmt->bindParam(':universidad', $usr->universidad);
            $stmt->bindParam(':tipoContrato', $usr->tipoContrato);
            $stmt->bindParam(':fechaIngreso', $usr->fechaIngreso);
            $stmt->bindParam(':fechaFinContrato', $usr->fechaFinContrato);
            $stmt->bindParam(':afiliacionSeguro', $usr->afiliacionSeguro);
            $stmt->bindParam(':nua', $usr->nua);
            $stmt->bindParam(':observaciones', $usr->observaciones);
            $stmt->bindParam(':foto', $usr->foto);
            $stmt->bindParam(':codCargo', $usr->codCargo);
            $stmt->bindParam(':codEspecialidad', $usr->codEspecialidad);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            die("Error en Editar: " . $e->getMessage());
        }
    }
    
    public function Eliminar($ciPersonal)
    {
        try {
            $stmt = $this->conex->prepare("CALL sp_eliminar_personalSalud(:ciPersonal)");
            $stmt->bindParam(':ciPersonal', $ciPersonal);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }
    }
}