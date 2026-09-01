<?php
require_once 'conexion.php';

class tratamiento
{
    private $conex;
    public $idTratamiento;
    public $idConsulta;
    public $nombre;
    public $descripcion;

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
            $stmt = $this->conex->prepare("SELECT * FROM tratamiento");
            $stmt->execute();
            $tratamientos = $stmt->fetchAll(PDO::FETCH_OBJ);
            return $tratamientos;  
        } catch (PDOException $e) {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }
    }
    
    public function f_ListarJson()
    {
        try {
            $stmt = $this->conex->prepare("SELECT * FROM tratamiento");
            $stmt->execute();
            $tratamientos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tratamientos[] = $row;                
            }          
            return $tratamientos;  
        } catch (PDOException $e) {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }
    }
    
    public function Insertar($trat)
    {
        try {
            $stmt = $this->conex->prepare("CALL sp_insertar_tratamiento(:idConsulta, :nombre, :descripcion)");
            
            $stmt->bindParam(':idConsulta', $trat->idConsulta);
            $stmt->bindParam(':nombre', $trat->nombre);
            $stmt->bindParam(':descripcion', $trat->descripcion);
            
            // Para obtener el ID insertado
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['idTratamiento'] : false;
            
        } catch (PDOException $e) {
            die("Error en Insertar: " . $e->getMessage());
        }
    }
    
    public function Editar($trat)
    {
        try {
            if (!isset($trat->idTratamiento) || empty($trat->idTratamiento)) {
                return false;
            }
            
            $stmt = $this->conex->prepare("CALL sp_editar_tratamiento(:idTratamiento, :nombre, :descripcion)");
            
            $stmt->bindParam(':idTratamiento', $trat->idTratamiento);
            $stmt->bindParam(':nombre', $trat->nombre);
            $stmt->bindParam(':descripcion', $trat->descripcion);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            die("Error en Editar: " . $e->getMessage());
        }
    }

    public function Eliminar($idTratamiento)
    {
        try {
            // Si tienes un campo estado, usa este:
            // $sql = "UPDATE tratamiento SET estado = 0 WHERE idTratamiento = :idTratamiento";
            
            // Si no tienes campo estado y quieres eliminar físicamente:
            $sql = "DELETE FROM tratamiento WHERE idTratamiento = :idTratamiento";
            
            $stmt = $this->conex->prepare($sql);
            $stmt->bindParam(':idTratamiento', $idTratamiento);
            return $stmt->execute();
            
        } catch (PDOException $e) {
            die("Error en Eliminar: " . $e->getMessage());
        }
    }
}
?>