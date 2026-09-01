<?php
require_once 'conexion.php';

class Usuario
{
    private $conex;
    public $idUsuario;
    public $ciPersonal;
    public $login;
    public $password;
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
            $stmt = $this->conex->prepare("
                SELECT u.*, r.nombre AS nombreRol, p.nombre AS nombre_personal
                FROM usuario u
                INNER JOIN rol r ON u.codRol = r.codRol
                INNER JOIN personalSalud p ON u.ciPersonal = p.ciPersonal
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);  
        } catch (PDOException $e) {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }
    }

    public function f_ListarJson()
    {
        try {
            $stmt = $this->conex->prepare("SELECT * FROM usuario");
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
    
        if ($tableName == 'rol') {
            $stmt = $this->conex->prepare("SELECT codRol, nombre AS nombreRol FROM rol WHERE estado=1");
        } else {
            $stmt = $this->conex->prepare("SELECT * FROM {$tableName} WHERE estado=1");
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch(PDOException $e) {
        error_log("Error: " . $e->getMessage());
        return false;
    }        
}

    // ❌ VALIDAR LOGIN REPETIDO
    public function ExisteLogin($login, $idUsuario = null)
    {
        $sql = "SELECT COUNT(*) FROM usuario WHERE login = ?";

        if ($idUsuario) {
            $sql .= " AND idUsuario != ?";
        }

        $stmt = $this->conex->prepare($sql);

        if ($idUsuario) {
            $stmt->execute([$login, $idUsuario]);
        } else {
            $stmt->execute([$login]);
        }

        return $stmt->fetchColumn() > 0;
    }

    // ❌ VALIDAR PERSONAL YA TIENE USUARIO
    public function ExistePersonal($ciPersonal, $idUsuario = null)
    {
        $sql = "SELECT COUNT(*) FROM usuario WHERE ciPersonal = ?";  

        if ($idUsuario) {
            $sql .= " AND idUsuario != ?";
        }

        $stmt = $this->conex->prepare($sql);

        if ($idUsuario) {
            $stmt->execute([$ciPersonal, $idUsuario]);
        } else {
            $stmt->execute([$ciPersonal]);
        }

        return $stmt->fetchColumn() > 0;
    }

    public function Insertar($usr, $nombreRol)
    {
        try {
            $sql = "CALL sp_insertar_usuario(?, ?, ?, ?)";
            $stmt = $this->conex->prepare($sql);
            $stmt->bindParam(1, $usr->ciPersonal, PDO::PARAM_INT);
            $stmt->bindParam(2, $usr->login, PDO::PARAM_STR);
            $stmt->bindParam(3, $usr->password, PDO::PARAM_STR);
            $stmt->bindParam(4, $nombreRol, PDO::PARAM_STR);
            
            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Insertar Error: " . $e->getMessage());
            return false;
        }
    }
    public function Editar($usr, $nombreRol)
    {
        try {
            $sql = "CALL sp_editar_usuario(?, ?, ?, ?, ?)";
            $stmt = $this->conex->prepare($sql);
            $stmt->bindParam(1, $usr->idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(2, $usr->ciPersonal, PDO::PARAM_INT);
            $stmt->bindParam(3, $usr->login, PDO::PARAM_STR);
            $stmt->bindParam(4, $usr->password, PDO::PARAM_STR);
            $stmt->bindParam(5, $nombreRol, PDO::PARAM_STR);
            
            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Editar Error: " . $e->getMessage());
            return false;
        }
    }

    public function Eliminar($idUsuario)
    {
        try {
            $stmt = $this->conex->prepare("DELETE FROM usuario WHERE idUsuario = :idUsuario");
            $stmt->bindParam(':idUsuario', $idUsuario);
            $stmt->execute();
        } catch (PDOException $e) {
            die(json_encode(["success" => false, "message" => $e->getMessage()]));
        }
    }
}