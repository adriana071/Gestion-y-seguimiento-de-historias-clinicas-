<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

class conexion {
    public static function Conectar()
    {
        try {
            $conex = new PDO("mysql:host=localhost;dbname=db_SantaClara;", "root", "");
            $conex->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // Asegura que se lancen excepciones si hay errores
            return $conex;
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos: " . $e->getMessage()]);
            exit; 
        }
    }
}
