<?php

require_once 'conexion.php';

class examenlaboratorio
{
    private $conex;

    // =========================================================
    // PROPIEDADES
    // =========================================================

    public $idExamen;
    public $idConsulta;
    public $tipoExamen;
    public $fechaSolicitud;
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
    // LISTAR TODOS LOS EXÁMENES
    // =========================================================
    // Muestra los exámenes registrados junto con información
    // básica de la consulta y del paciente.
    // =========================================================

    public function Listar()
    {
        try {

            $sql = "
                SELECT el.idExamen,el.idConsulta,el.tipoExamen,el.fechaSolicitud,el.fechaRegistro,el.estado,

                    c.ciPaciente,
                    c.ciPersonal,
                    c.diagnostico,
                    c.motivo,

                    CONCAT(
                        p.nombre, ' ',
                        p.apaterno, ' ',
                        COALESCE(p.apmaterno, '')
                    ) AS paciente,

                    r.idResultado,
                    r.estadoExamen,
                    r.resultado

                FROM examenLaboratorio el

                INNER JOIN consulta c
                    ON c.idConsulta = el.idConsulta

                INNER JOIN paciente p
                    ON p.cipaciente = c.ciPaciente

                LEFT JOIN resultado r
                    ON r.idExamen = el.idExamen
                    AND r.estado = 1

                WHERE el.estado = 1

                ORDER BY el.idExamen DESC
            ";

            $stm = $this->conex->prepare($sql);

            $stm->execute();

            return $stm->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {

            error_log("Listar Exámenes: " . $e->getMessage());

            return [];
        }
    }


    // =========================================================
    // LISTAR EXÁMENES EN FORMATO JSON
    // =========================================================
    // Este método nos servirá posteriormente para AJAX
    // y también puede utilizarse desde Flutter.
    // =========================================================

    public function f_ListarJson()
    {
        try {

            $sql = "
                SELECT
                    el.idExamen,
                    el.idConsulta,
                    el.tipoExamen,
                    el.fechaSolicitud,
                    el.fechaRegistro,
                    el.estado,

                    c.ciPaciente,
                    c.ciPersonal,
                    c.diagnostico,
                    c.motivo,

                    CONCAT(
                        p.nombre, ' ',
                        p.apaterno, ' ',
                        COALESCE(p.apmaterno, '')
                    ) AS paciente,

                    r.idResultado,
                    r.estadoExamen,
                    r.resultado

                FROM examenLaboratorio el

                INNER JOIN consulta c
                    ON c.idConsulta = el.idConsulta

                INNER JOIN paciente p
                    ON p.cipaciente = c.ciPaciente

                LEFT JOIN resultado r
                    ON r.idExamen = el.idExamen
                    AND r.estado = 1

                WHERE el.estado = 1

                ORDER BY el.idExamen DESC
            ";

            $stm = $this->conex->prepare($sql);

            $stm->execute();

            $datos = [];

            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {

                $datos[] = $row;
            }

            return $datos;
        } catch (PDOException $e) {

            error_log("Listar Exámenes JSON: " . $e->getMessage());

            return [];
        }
    }


    // =========================================================
    // OBTENER UN EXAMEN
    // =========================================================
    // Busca un examen específico por su ID.
    // =========================================================

    public function Obtener($idExamen)
    {
        try {

            $sql = "
                SELECT
                    el.idExamen,
                    el.idConsulta,
                    el.tipoExamen,
                    el.fechaSolicitud,
                    el.fechaRegistro,
                    el.estado,

                    c.ciPaciente,
                    c.ciPersonal,
                    c.diagnostico,
                    c.motivo,
                    c.estadoConsulta,

                    CONCAT(
                        p.nombre, ' ',
                        p.apaterno, ' ',
                        COALESCE(p.apmaterno, '')
                    ) AS paciente,

                    p.fechaNacimiento,
                    p.genero,
                    p.telefono,

                    r.idResultado,
                    r.resultado,
                    r.observaciones,
                    r.documento,
                    r.estadoExamen,
                    r.fechaRegistro AS fechaResultado

                FROM examenLaboratorio el

                INNER JOIN consulta c
                    ON c.idConsulta = el.idConsulta

                INNER JOIN paciente p
                    ON p.cipaciente = c.ciPaciente

                LEFT JOIN resultado r
                    ON r.idExamen = el.idExamen
                    AND r.estado = 1

                WHERE el.idExamen = ?
                  AND el.estado = 1

                LIMIT 1
            ";

            $stm = $this->conex->prepare($sql);

            $stm->execute([$idExamen]);

            return $stm->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {

            error_log("Obtener Examen: " . $e->getMessage());

            return false;
        }
    }


    // =========================================================
    // LISTAR EXÁMENES DE UNA CONSULTA
    // =========================================================
    // Permite consultar todos los exámenes solicitados durante
    // una consulta médica específica.
    // =========================================================

    public function ListarPorConsulta($idConsulta)
    {
        try {

            $sql = "
                SELECT
                    el.idExamen,
                    el.idConsulta,
                    el.tipoExamen,
                    el.fechaSolicitud,
                    el.fechaRegistro,
                    el.estado,

                    r.idResultado,
                    r.resultado,
                    r.observaciones,
                    r.documento,
                    r.estadoExamen,
                    r.fechaRegistro AS fechaResultado

                FROM examenLaboratorio el

                LEFT JOIN resultado r
                    ON r.idExamen = el.idExamen
                    AND r.estado = 1

                WHERE el.idConsulta = ?
                  AND el.estado = 1

                ORDER BY el.idExamen DESC
            ";

            $stm = $this->conex->prepare($sql);

            $stm->execute([$idConsulta]);

            return $stm->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {

            error_log("Listar Exámenes por Consulta: " . $e->getMessage());

            return [];
        }
    }


    // =========================================================
    // OBTENER INFORMACIÓN DE UNA CONSULTA
    // =========================================================
    // Se utiliza antes de registrar un examen para mostrar
    // automáticamente el paciente y diagnóstico.
    // =========================================================

    public function ObtenerConsulta($idConsulta)
    {
        try {

            $sql = "
                SELECT
                    c.idConsulta,
                    c.ciPaciente,
                    c.ciPersonal,
                    c.codEspecialidad,
                    c.motivo,
                    c.diagnostico,
                    c.estadoConsulta,

                    CONCAT(
                        p.nombre, ' ',
                        p.apaterno, ' ',
                        COALESCE(p.apmaterno, '')
                    ) AS paciente,

                    p.fechaNacimiento,
                    p.genero,
                    p.telefono

                FROM consulta c

                INNER JOIN paciente p
                    ON p.cipaciente = c.ciPaciente

                WHERE c.idConsulta = ?
                  AND c.estado = 1

                LIMIT 1
            ";

            $stm = $this->conex->prepare($sql);

            $stm->execute([$idConsulta]);

            return $stm->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {

            error_log("Obtener Consulta para Examen: " . $e->getMessage());

            return false;
        }
    }


    // =========================================================
    // REGISTRAR EXAMEN
    // =========================================================
    // Registra la solicitud de un examen de laboratorio.
    // =========================================================

    public function Insertar(examenLaboratorio $data)
    {
        try {

            // -------------------------------------------------
            // Verificar que la consulta exista
            // -------------------------------------------------

            $consulta = $this->ObtenerConsulta($data->idConsulta);

            if (!$consulta) {

                return (object)[
                    "codigoError" => 1,
                    "mensaje" => "La consulta indicada no existe."
                ];
            }


            // -------------------------------------------------
            // Verificar si ya existe el mismo tipo de examen
            // para esa consulta
            // -------------------------------------------------

            $sqlVerificar = "
                SELECT COUNT(*) AS total
                FROM examenLaboratorio
                WHERE idConsulta = ?
                  AND tipoExamen = ?
                  AND estado = 1
            ";

            $stmVerificar = $this->conex->prepare($sqlVerificar);

            $stmVerificar->execute([
                $data->idConsulta,
                $data->tipoExamen
            ]);

            $existe = $stmVerificar->fetch(PDO::FETCH_OBJ);

            if ($existe && (int)$existe->total > 0) {

                return (object)[
                    "codigoError" => 1,
                    "mensaje" => "Este examen ya fue solicitado para esta consulta."
                ];
            }


            // -------------------------------------------------
            // Insertar examen
            // -------------------------------------------------

            $sql = "
                INSERT INTO examenLaboratorio
                (
                    idConsulta,
                    tipoExamen,
                    fechaSolicitud,
                    estado
                )
                VALUES
                (
                    :idConsulta,
                    :tipoExamen,
                    :fechaSolicitud,
                    1
                )
            ";

            $stm = $this->conex->prepare($sql);

            $stm->bindParam(
                ':idConsulta',
                $data->idConsulta
            );

            $stm->bindParam(
                ':tipoExamen',
                $data->tipoExamen
            );

            $stm->bindParam(
                ':fechaSolicitud',
                $data->fechaSolicitud
            );

            $stm->execute();

            $idExamen = $this->conex->lastInsertId();


            // -------------------------------------------------
            // Respuesta
            // -------------------------------------------------

            return (object)[
                "codigoError" => 0,
                "mensaje" => "Examen de laboratorio solicitado correctamente.",
                "idExamen" => $idExamen
            ];
        } catch (PDOException $e) {

            error_log("Insertar Examen Laboratorio: " . $e->getMessage());

            return (object)[
                "codigoError" => 1,
                "mensaje" => "Error al registrar el examen de laboratorio."
            ];
        }
    }


    // =========================================================
    // EDITAR EXAMEN
    // =========================================================
    // Permite modificar el tipo y fecha de solicitud mientras
    // todavía no tenga un resultado registrado.
    // =========================================================

    public function Editar(examenLaboratorio $data)
    {
        try {

            // -------------------------------------------------
            // Verificar si existe resultado
            // -------------------------------------------------

            $sqlResultado = "
                SELECT COUNT(*) AS total
                FROM resultado
                WHERE idExamen = ?
                  AND estado = 1
            ";

            $stmResultado = $this->conex->prepare($sqlResultado);

            $stmResultado->execute([
                $data->idExamen
            ]);

            $resultado = $stmResultado->fetch(PDO::FETCH_OBJ);

            if ($resultado && (int)$resultado->total > 0) {

                return (object)[
                    "codigoError" => 1,
                    "mensaje" => "No se puede editar el examen porque ya tiene un resultado registrado."
                ];
            }


            // -------------------------------------------------
            // Actualizar examen
            // -------------------------------------------------

            $sql = "
                UPDATE examenLaboratorio
                SET
                    tipoExamen = :tipoExamen,
                    fechaSolicitud = :fechaSolicitud
                WHERE idExamen = :idExamen
                  AND estado = 1
            ";

            $stm = $this->conex->prepare($sql);

            $stm->bindParam(
                ':tipoExamen',
                $data->tipoExamen
            );

            $stm->bindParam(
                ':fechaSolicitud',
                $data->fechaSolicitud
            );

            $stm->bindParam(
                ':idExamen',
                $data->idExamen
            );

            $stm->execute();


            if ($stm->rowCount() > 0) {

                return (object)[
                    "codigoError" => 0,
                    "mensaje" => "Examen actualizado correctamente."
                ];
            }

            return (object)[
                "codigoError" => 1,
                "mensaje" => "No se encontró el examen o no hubo cambios."
            ];
        } catch (PDOException $e) {

            error_log("Editar Examen Laboratorio: " . $e->getMessage());

            return (object)[
                "codigoError" => 1,
                "mensaje" => "Error al editar el examen de laboratorio."
            ];
        }
    }


    // =========================================================
    // ELIMINAR EXAMEN
    // =========================================================
    // Se realiza eliminación lógica mediante estado = 0.
    // No se elimina físicamente de la BD.
    // =========================================================

    public function Eliminar($idExamen)
    {
        try {

            // -------------------------------------------------
            // Verificar si tiene resultado
            // -------------------------------------------------

            $sqlResultado = "
                SELECT COUNT(*) AS total
                FROM resultado
                WHERE idExamen = ?
                  AND estado = 1
            ";

            $stmResultado = $this->conex->prepare($sqlResultado);

            $stmResultado->execute([
                $idExamen
            ]);

            $resultado = $stmResultado->fetch(PDO::FETCH_OBJ);

            if ($resultado && (int)$resultado->total > 0) {

                return (object)[
                    "codigoError" => 1,
                    "mensaje" => "No se puede eliminar el examen porque ya tiene un resultado registrado."
                ];
            }


            // -------------------------------------------------
            // Eliminación lógica
            // -------------------------------------------------

            $sql = "
                UPDATE examenLaboratorio
                SET estado = 0
                WHERE idExamen = ?
                  AND estado = 1
            ";

            $stm = $this->conex->prepare($sql);

            $stm->execute([
                $idExamen
            ]);


            if ($stm->rowCount() > 0) {

                return (object)[
                    "codigoError" => 0,
                    "mensaje" => "Examen eliminado correctamente."
                ];
            }

            return (object)[
                "codigoError" => 1,
                "mensaje" => "No se encontró el examen."
            ];
        } catch (PDOException $e) {

            error_log("Eliminar Examen Laboratorio: " . $e->getMessage());

            return (object)[
                "codigoError" => 1,
                "mensaje" => "Error al eliminar el examen de laboratorio."
            ];
        }
    }


    // =========================================================
    // COMPROBAR SI UN EXAMEN TIENE RESULTADO
    // =========================================================
    // Devuelve true si ya existe un resultado activo.
    // =========================================================

    public function TieneResultado($idExamen)
    {
        try {

            $sql = "
                SELECT COUNT(*) AS total
                FROM resultado
                WHERE idExamen = ?
                  AND estado = 1
            ";

            $stm = $this->conex->prepare($sql);

            $stm->execute([
                $idExamen
            ]);

            $resultado = $stm->fetch(PDO::FETCH_OBJ);

            return $resultado && (int)$resultado->total > 0;
        } catch (PDOException $e) {

            error_log("TieneResultado: " . $e->getMessage());

            return false;
        }
    }


    // =========================================================
    // CONTAR EXÁMENES PENDIENTES
    // =========================================================
    // Un examen se considera pendiente cuando todavía no
    // existe un resultado activo.
    // =========================================================

    public function ContarPendientes()
    {
        try {

            $sql = "
                SELECT COUNT(*) AS total

                FROM examenLaboratorio el

                LEFT JOIN resultado r
                    ON r.idExamen = el.idExamen
                    AND r.estado = 1

                WHERE el.estado = 1
                  AND r.idResultado IS NULL
            ";

            $stm = $this->conex->query($sql);

            $resultado = $stm->fetch(PDO::FETCH_OBJ);

            return (int)$resultado->total;
        } catch (PDOException $e) {

            error_log("Contar Exámenes Pendientes: " . $e->getMessage());

            return 0;
        }
    }


    // =========================================================
    // CONTAR EXÁMENES CON RESULTADO
    // =========================================================

    public function ContarResultadosDisponibles()
    {
        try {

            $sql = "
                SELECT COUNT(DISTINCT el.idExamen) AS total

                FROM examenLaboratorio el

                INNER JOIN resultado r
                    ON r.idExamen = el.idExamen
                    AND r.estado = 1

                WHERE el.estado = 1
            ";

            $stm = $this->conex->query($sql);

            $resultado = $stm->fetch(PDO::FETCH_OBJ);

            return (int)$resultado->total;
        } catch (PDOException $e) {

            error_log("Contar Resultados Disponibles: " . $e->getMessage());

            return 0;
        }
    }

    // =========================================================
    // REPORTE: listado de exámenes con filtros opcionales
    // =========================================================
    // $filtros puede traer: fechaInicio, fechaFin, estadoExamen
    // (Pendiente / Completado). Todos opcionales.
    // =========================================================

    public function ListarParaReporte(array $filtros = [])
    {
        try {
            $condiciones = ["el.estado = 1"];
            $params = [];

            if (!empty($filtros['fechaInicio'])) {
                $condiciones[] = "el.fechaSolicitud >= :fechaInicio";
                $params[':fechaInicio'] = $filtros['fechaInicio'];
            }
            if (!empty($filtros['fechaFin'])) {
                $condiciones[] = "el.fechaSolicitud <= :fechaFin";
                $params[':fechaFin'] = $filtros['fechaFin'];
            }
            if (!empty($filtros['estadoExamen'])) {
                if ($filtros['estadoExamen'] === 'Pendiente') {
                    $condiciones[] = "r.idResultado IS NULL";
                } else {
                    $condiciones[] = "r.estadoExamen = :estadoExamen";
                    $params[':estadoExamen'] = $filtros['estadoExamen'];
                }
            }

            $sql = "
                SELECT
                    el.idExamen,
                    el.tipoExamen,
                    el.fechaSolicitud,

                    c.idConsulta,

                    CONCAT(p.nombre, ' ', p.apaterno, ' ', COALESCE(p.apmaterno, '')) AS paciente,
                    CONCAT(ps.nombre, ' ', ps.apaterno, ' ', COALESCE(ps.apmaterno, '')) AS medico,

                    r.idResultado,
                    r.resultado,
                    r.estadoExamen

                FROM examenLaboratorio el
                INNER JOIN consulta c ON c.idConsulta = el.idConsulta
                INNER JOIN paciente p ON p.ciPaciente = c.ciPaciente
                INNER JOIN personalSalud ps ON ps.ciPersonal = c.ciPersonal
                LEFT JOIN resultado r ON r.idExamen = el.idExamen AND r.estado = 1

                WHERE " . implode(' AND ', $condiciones) . "
                ORDER BY el.idExamen DESC
            ";

            $stm = $this->conex->prepare($sql);
            $stm->execute($params);
            return $stm->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("ListarParaReporte (examen): " . $e->getMessage());
            return [];
        }
    }

    // =========================================================
    // CANCELAR SOLICITUD: elimina (lógicamente) solo los
    // exámenes de una consulta que TODAVÍA NO tienen resultado.
    // Los que ya tienen resultado se dejan intactos.
    // =========================================================

    public function CancelarPendientesPorConsulta($idConsulta)
    {
        try {
            $sql = "
                UPDATE examenLaboratorio el
                LEFT JOIN resultado r
                    ON r.idExamen = el.idExamen
                    AND r.estado = 1
                SET el.estado = 0
                WHERE el.idConsulta = ?
                  AND el.estado = 1
                  AND r.idResultado IS NULL
            ";

            $stm = $this->conex->prepare($sql);
            $stm->execute([$idConsulta]);

            return (object)[
                "codigoError" => 0,
                "mensaje" => "Se cancelaron " . $stm->rowCount() . " examen(es) pendiente(s).",
                "cancelados" => $stm->rowCount()
            ];
        } catch (PDOException $e) {
            error_log("CancelarPendientesPorConsulta: " . $e->getMessage());
            return (object)[
                "codigoError" => 1,
                "mensaje" => "Ocurrió un error al cancelar la solicitud."
            ];
        }
    }
}
