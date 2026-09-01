<?php
require_once 'conexion.php';

class Resultado
{
    private $conex;

    // =========================================================
    // PROPIEDADES
    // =========================================================

    public $idResultado;
    public $idExamen;
    public $resultado;
    public $observaciones;
    public $documento;
    public $estadoExamen;
    public $fechaRegistro;
    public $estado;

    // =========================================================
    // CONSTRUCTOR
    // =========================================================

    public function __construct()
    {
        try {
            $this->conex = conexion::Conectar();
        } catch (PDOException $e) {
            die(json_encode([
                "success" => false,
                "message" => $e->getMessage()
            ]));
        }
    }

    // =========================================================
    // LISTAR TODOS LOS RESULTADOS
    // =========================================================

    public function Listar()
    {
        try {
            $sql = "
                SELECT 
                    r.idResultado,
                    r.idExamen,
                    r.resultado,
                    r.observaciones,
                    r.documento,
                    r.estadoExamen,
                    r.fechaRegistro,
                    r.estado,
                    
                    el.tipoExamen,
                    el.fechaSolicitud,
                    
                    c.idConsulta,
                    c.diagnostico,
                    c.motivo,
                    
                    CONCAT(
                        p.nombre, ' ',
                        p.apaterno, ' ',
                        COALESCE(p.apmaterno, '')
                    ) AS paciente,
                    p.ciPaciente,
                    p.telefono
                    
                FROM resultado r
                
                INNER JOIN examenLaboratorio el
                    ON el.idExamen = r.idExamen
                
                INNER JOIN consulta c
                    ON c.idConsulta = el.idConsulta
                
                INNER JOIN paciente p
                    ON p.cipaciente = c.ciPaciente
                
                WHERE r.estado = 1
                
                ORDER BY r.idResultado DESC
            ";

            $stm = $this->conex->prepare($sql);
            $stm->execute();

            return $stm->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Listar Resultados: " . $e->getMessage());
            return [];
        }
    }

    // =========================================================
    // LISTAR RESULTADOS POR EXAMEN
    // =========================================================

    public function ListarPorExamen($idExamen)
    {
        try {
            $sql = "
                SELECT 
                    r.idResultado,
                    r.idExamen,
                    r.resultado,
                    r.observaciones,
                    r.documento,
                    r.estadoExamen,
                    r.fechaRegistro,
                    r.estado
                    
                FROM resultado r
                
                WHERE r.idExamen = ?
                  AND r.estado = 1
                
                ORDER BY r.idResultado DESC
            ";

            $stm = $this->conex->prepare($sql);
            $stm->execute([$idExamen]);

            return $stm->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Listar Resultados por Examen: " . $e->getMessage());
            return [];
        }
    }

    // =========================================================
    // OBTENER UN RESULTADO
    // =========================================================

    public function Obtener($idResultado)
    {
        try {
            $sql = "
                SELECT 
                    r.idResultado,
                    r.idExamen,
                    r.resultado,
                    r.observaciones,
                    r.documento,
                    r.estadoExamen,
                    r.fechaRegistro,
                    r.estado,
                    
                    el.tipoExamen,
                    el.fechaSolicitud,
                    
                    c.idConsulta,
                    c.diagnostico,
                    c.motivo,
                    
                    CONCAT(
                        p.nombre, ' ',
                        p.apaterno, ' ',
                        COALESCE(p.apmaterno, '')
                    ) AS paciente,
                    p.ciPaciente,
                    p.telefono
                    
                FROM resultado r
                
                INNER JOIN examenLaboratorio el
                    ON el.idExamen = r.idExamen
                
                INNER JOIN consulta c
                    ON c.idConsulta = el.idConsulta
                
                INNER JOIN paciente p
                    ON p.cipaciente = c.ciPaciente
                
                WHERE r.idResultado = ?
                  AND r.estado = 1
                
                LIMIT 1
            ";

            $stm = $this->conex->prepare($sql);
            $stm->execute([$idResultado]);

            return $stm->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Obtener Resultado: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // OBTENER RESULTADO POR ID DE EXAMEN
    // =========================================================

    public function ObtenerPorExamen($idExamen)
    {
        try {
            $sql = "
                SELECT 
                    r.idResultado,
                    r.idExamen,
                    r.resultado,
                    r.observaciones,
                    r.documento,
                    r.estadoExamen,
                    r.fechaRegistro,
                    r.estado
                    
                FROM resultado r
                
                WHERE r.idExamen = ?
                  AND r.estado = 1
                
                LIMIT 1
            ";

            $stm = $this->conex->prepare($sql);
            $stm->execute([$idExamen]);

            return $stm->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Obtener Resultado por Examen: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // VERIFICAR SI UN EXAMEN TIENE RESULTADO
    // =========================================================

    public function ExisteResultado($idExamen)
    {
        try {
            $sql = "
                SELECT COUNT(*) AS total
                FROM resultado
                WHERE idExamen = ?
                  AND estado = 1
            ";

            $stm = $this->conex->prepare($sql);
            $stm->execute([$idExamen]);

            $resultado = $stm->fetch(PDO::FETCH_OBJ);

            return (int)$resultado->total > 0;
        } catch (PDOException $e) {
            error_log("Existe Resultado: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // INSERTAR RESULTADO
    // =========================================================

    public function Insertar(Resultado $data)
    {
        try {
            // Verificar si el examen existe
            $sqlExamen = "SELECT idExamen FROM examenLaboratorio WHERE idExamen = ? AND estado = 1";
            $stmExamen = $this->conex->prepare($sqlExamen);
            $stmExamen->execute([$data->idExamen]);
            
            if (!$stmExamen->fetch()) {
                return (object)[
                    "codigoError" => 1,
                    "mensaje" => "El examen no existe o no está activo."
                ];
            }

            // Verificar si ya tiene resultado
            if ($this->ExisteResultado($data->idExamen)) {
                return (object)[
                    "codigoError" => 1,
                    "mensaje" => "Este examen ya tiene un resultado registrado."
                ];
            }

            // Insertar resultado
            $sql = "
                INSERT INTO resultado
                (
                    idExamen,
                    resultado,
                    observaciones,
                    documento,
                    estadoExamen,
                    estado
                )
                VALUES
                (
                    :idExamen,
                    :resultado,
                    :observaciones,
                    :documento,
                    :estadoExamen,
                    1
                )
            ";

            $stm = $this->conex->prepare($sql);

            $stm->bindParam(':idExamen', $data->idExamen);
            $stm->bindParam(':resultado', $data->resultado);
            $stm->bindParam(':observaciones', $data->observaciones);
            $stm->bindParam(':documento', $data->documento);
            $stm->bindParam(':estadoExamen', $data->estadoExamen);

            $stm->execute();

            $idResultado = $this->conex->lastInsertId();

            return (object)[
                "codigoError" => 0,
                "mensaje" => "Resultado registrado correctamente.",
                "idResultado" => $idResultado
            ];
        } catch (PDOException $e) {
            error_log("Insertar Resultado: " . $e->getMessage());
            return (object)[
                "codigoError" => 1,
                "mensaje" => "Error al registrar el resultado."
            ];
        }
    }

    // =========================================================
    // EDITAR RESULTADO
    // =========================================================

    public function Editar(Resultado $data)
    {
        try {
            $sql = "
                UPDATE resultado
                SET
                    resultado = :resultado,
                    observaciones = :observaciones,
                    documento = :documento,
                    estadoExamen = :estadoExamen
                WHERE idResultado = :idResultado
                  AND estado = 1
            ";

            $stm = $this->conex->prepare($sql);

            $stm->bindParam(':resultado', $data->resultado);
            $stm->bindParam(':observaciones', $data->observaciones);
            $stm->bindParam(':documento', $data->documento);
            $stm->bindParam(':estadoExamen', $data->estadoExamen);
            $stm->bindParam(':idResultado', $data->idResultado);

            $stm->execute();

            if ($stm->rowCount() > 0) {
                return (object)[
                    "codigoError" => 0,
                    "mensaje" => "Resultado actualizado correctamente."
                ];
            }

            return (object)[
                "codigoError" => 1,
                "mensaje" => "No se encontró el resultado o no hubo cambios."
            ];
        } catch (PDOException $e) {
            error_log("Editar Resultado: " . $e->getMessage());
            return (object)[
                "codigoError" => 1,
                "mensaje" => "Error al editar el resultado."
            ];
        }
    }

    // =========================================================
    // ELIMINAR RESULTADO (LÓGICO)
    // =========================================================

    public function Eliminar($idResultado)
    {
        try {
            $sql = "
                UPDATE resultado
                SET estado = 0
                WHERE idResultado = ?
                  AND estado = 1
            ";

            $stm = $this->conex->prepare($sql);
            $stm->execute([$idResultado]);

            if ($stm->rowCount() > 0) {
                return (object)[
                    "codigoError" => 0,
                    "mensaje" => "Resultado eliminado correctamente."
                ];
            }

            return (object)[
                "codigoError" => 1,
                "mensaje" => "No se encontró el resultado."
            ];
        } catch (PDOException $e) {
            error_log("Eliminar Resultado: " . $e->getMessage());
            return (object)[
                "codigoError" => 1,
                "mensaje" => "Error al eliminar el resultado."
            ];
        }
    }

    // =========================================================
    // CONTAR RESULTADOS TOTALES
    // =========================================================

    public function ContarTotal()
    {
        try {
            $sql = "SELECT COUNT(*) AS total FROM resultado WHERE estado = 1";
            $stm = $this->conex->query($sql);
            $resultado = $stm->fetch(PDO::FETCH_OBJ);

            return (int)$resultado->total;
        } catch (PDOException $e) {
            error_log("Contar Resultados: " . $e->getMessage());
            return 0;
        }
    }

    // =========================================================
    // CONTAR RESULTADOS POR ESTADO
    // =========================================================

    public function ContarPorEstado($estadoExamen)
    {
        try {
            $sql = "
                SELECT COUNT(*) AS total
                FROM resultado
                WHERE estadoExamen = ?
                  AND estado = 1
            ";

            $stm = $this->conex->prepare($sql);
            $stm->execute([$estadoExamen]);

            $resultado = $stm->fetch(PDO::FETCH_OBJ);

            return (int)$resultado->total;
        } catch (PDOException $e) {
            error_log("Contar Resultados por Estado: " . $e->getMessage());
            return 0;
        }
    }

    // =========================================================
    // REPORTE: listado de resultados con filtros opcionales
    // =========================================================
    // $filtros puede traer: fechaInicio, fechaFin, estadoExamen
    // =========================================================

    public function ListarParaReporte(array $filtros = [])
    {
        try {
            $condiciones = ["r.estado = 1"];
            $params = [];

            if (!empty($filtros['fechaInicio'])) {
                $condiciones[] = "DATE(r.fechaRegistro) >= :fechaInicio";
                $params[':fechaInicio'] = $filtros['fechaInicio'];
            }
            if (!empty($filtros['fechaFin'])) {
                $condiciones[] = "DATE(r.fechaRegistro) <= :fechaFin";
                $params[':fechaFin'] = $filtros['fechaFin'];
            }
            if (!empty($filtros['estadoExamen'])) {
                $condiciones[] = "r.estadoExamen = :estadoExamen";
                $params[':estadoExamen'] = $filtros['estadoExamen'];
            }

            $sql = "
                SELECT
                    r.idResultado,
                    r.resultado,
                    r.observaciones,
                    r.estadoExamen,
                    r.fechaRegistro,

                    el.tipoExamen,

                    CONCAT(p.nombre, ' ', p.apaterno, ' ', COALESCE(p.apmaterno, '')) AS paciente

                FROM resultado r
                INNER JOIN examenLaboratorio el ON el.idExamen = r.idExamen
                INNER JOIN consulta c ON c.idConsulta = el.idConsulta
                INNER JOIN paciente p ON p.ciPaciente = c.ciPaciente

                WHERE " . implode(' AND ', $condiciones) . "
                ORDER BY r.idResultado DESC
            ";

            $stm = $this->conex->prepare($sql);
            $stm->execute($params);
            return $stm->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("ListarParaReporte (resultado): " . $e->getMessage());
            return [];
        }
    }
}
?>