<?php
require_once 'conexion.php';

class consulta
{
    private $conex;

    public $idConsulta;

    public $ciPaciente;
    public $ciPersonal;

    public $idCondicion;
    public $codEspecialidad;

    public $motivo;
    public $diagnostico;
    public $estadoConsulta;

    public $peso;
    public $estatura;
    public $temperatura;
    public $presionArterial;
    public $idTratamiento;
    public $nombreTratamiento;
    public $descripcionTratamiento;

    public $estado;

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

    public function Listar()
    {
        try {
            $stm = $this->conex->prepare("SELECT * FROM vs_vista_consultas ORDER BY idConsulta DESC");
            $stm->execute();
            return $stm->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            die(json_encode([
                "success" => false,
                "message" => $e->getMessage()
            ]));
        }
    }

    public function f_ListarJson()
    {
        try {
            $stm = $this->conex->prepare("SELECT * FROM vs_vista_consultas ORDER BY idConsulta DESC");
            $stm->execute();

            $datos = [];
            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                $datos[] = $row;
            }
            return $datos;
        } catch (PDOException $e) {
            die(json_encode([
                "success" => false,
                "message" => $e->getMessage()
            ]));
        }
    }

    public function BuscarPaciente($ciPaciente)
    {
        try {
            $stm = $this->conex->prepare("
                SELECT *
                FROM paciente
                WHERE cipaciente = ?
                AND estado = 1
            ");
            $stm->execute(array($ciPaciente));
            return $stm->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            die(json_encode([
                "success" => false,
                "message" => $e->getMessage()
            ]));
        }
    }

    public function Insertar(consulta $data)
    {
        try {
            // El orden real de los OUT en el SP es:
            // p_idConsulta, p_codigoError, p_mensaje
            // (antes estaba @idConsulta, @mensaje, @codigoError -> invertido,
            // eso hacía que el código de error y el mensaje llegaran cruzados)
            $sql = "CALL sp_insertar_consulta(:ciPaciente,:ciPersonal,:motivo,:diagnostico,:estadoConsulta,:peso,:estatura,
                    :temperatura,:presionArterial,:nombreTratamiento,:descripcionTratamiento, @idConsulta, @codigoError, @mensaje)";

            $stm = $this->conex->prepare($sql);

            $stm->bindParam(':ciPaciente', $data->ciPaciente);
            $stm->bindParam(':ciPersonal', $data->ciPersonal);
            $stm->bindParam(':motivo', $data->motivo);
            $stm->bindParam(':diagnostico', $data->diagnostico);
            $stm->bindParam(':estadoConsulta', $data->estadoConsulta);

            $stm->bindParam(':peso', $data->peso);
            $stm->bindParam(':estatura', $data->estatura);
            $stm->bindParam(':temperatura', $data->temperatura);
            $stm->bindParam(':presionArterial', $data->presionArterial);

            $stm->bindParam(':nombreTratamiento', $data->nombreTratamiento);
            $stm->bindParam(':descripcionTratamiento', $data->descripcionTratamiento);

            $stm->execute();
            $stm->closeCursor();

            $resultado = $this->conex->query("
                SELECT
                    @idConsulta AS idConsulta,
                    @codigoError AS codigoError,
                    @mensaje AS mensaje
            ")->fetch(PDO::FETCH_OBJ);

            return $resultado;
        } catch (PDOException $e) {
            error_log("Insertar Consulta: " . $e->getMessage());
            return false;
        }
    }

    public function Editar(consulta $data)
    {
        try {
            // sp_editar_consulta solo recibe: idConsulta, motivo, diagnostico,
            // estadoConsulta, peso, estatura, temperatura, presionArterial.
            // NO recibe ciPaciente/ciPersonal/tratamiento: el propio SP
            // bloquea la edición si la consulta ya tiene tratamiento o
            // exámenes de laboratorio registrados.
            $sql = "CALL sp_editar_consulta(:idConsulta,:motivo,:diagnostico,:estadoConsulta,
                    :peso,:estatura,:temperatura,:presionArterial, @codigoError, @mensaje)";

            $stm = $this->conex->prepare($sql);

            $stm->bindParam(':idConsulta', $data->idConsulta);
            $stm->bindParam(':motivo', $data->motivo);
            $stm->bindParam(':diagnostico', $data->diagnostico);
            $stm->bindParam(':estadoConsulta', $data->estadoConsulta);

            $stm->bindParam(':peso', $data->peso);
            $stm->bindParam(':estatura', $data->estatura);
            $stm->bindParam(':temperatura', $data->temperatura);
            $stm->bindParam(':presionArterial', $data->presionArterial);

            $stm->execute();
            $stm->closeCursor();

            $resultado = $this->conex->query("
                SELECT
                    @codigoError AS codigoError,
                    @mensaje AS mensaje
            ")->fetch(PDO::FETCH_OBJ);

            return $resultado;
        } catch (PDOException $e) {
            error_log("Editar Consulta: " . $e->getMessage());
            return false;
        }
    }

    public function Obtener($idConsulta)
    {
        try {
            $stm = $this->conex->prepare("
                SELECT *
                FROM vs_vista_consultas
                WHERE idConsulta = ?
            ");
            $stm->execute(array($idConsulta));
            return $stm->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            die(json_encode([
                "success" => false,
                "message" => $e->getMessage()
            ]));
        }
    }

    public function Eliminar($idConsulta)
    {
        try {
            $sql = "CALL sp_eliminar_consulta(
                        :idConsulta,
                        @codigoError,
                        @mensaje
                    )";

            $stm = $this->conex->prepare($sql);
            $stm->bindParam(':idConsulta', $idConsulta);
            $stm->execute();
            $stm->closeCursor();

            $resultado = $this->conex->query("
                SELECT
                    @mensaje AS mensaje,
                    @codigoError AS codigoError
            ")->fetch(PDO::FETCH_OBJ);

            return $resultado;
        } catch (PDOException $e) {
            error_log("Eliminar Consulta: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // NUEVO: Condición física ya registrada (tomada previamente,
    // p.ej. por enfermería) y aún no ligada a ninguna consulta.
    // =========================================================
    public function ObtenerCondicionFisicaDisponible($ciPaciente)
    {
        try {
            $stm = $this->conex->prepare("
                SELECT cf.*
                FROM condicionFisica cf
                WHERE cf.ciPaciente = ?
                  AND cf.estado = 1
                  AND NOT EXISTS (
                      SELECT 1 FROM consulta c WHERE c.idCondicion = cf.idCondicion
                  )
                ORDER BY cf.idCondicion DESC
                LIMIT 1
            ");
            $stm->execute([$ciPaciente]);
            return $stm->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("ObtenerCondicionFisicaDisponible: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // NUEVO: Medicamentos / detalleReceta
    // La tabla detalleReceta exige cantidad, frecuencia,
    // viaAdministracion y duracion como NOT NULL, así que la vista
    // ahora los captura y los mandamos completos.
    // =========================================================

    /** Busca el tratamiento más reciente asociado a una consulta. */
    public function ObtenerIdTratamiento($idConsulta)
    {
        try {
            $stm = $this->conex->prepare("
                SELECT idTratamiento
                FROM tratamiento
                WHERE idConsulta = ?
                ORDER BY idTratamiento DESC
                LIMIT 1
            ");
            $stm->execute([$idConsulta]);
            $row = $stm->fetch(PDO::FETCH_OBJ);
            return $row ? $row->idTratamiento : null;
        } catch (PDOException $e) {
            error_log("ObtenerIdTratamiento: " . $e->getMessage());
            return null;
        }
    }

    /** Busca un medicamento por nombre; si no existe, lo crea. Devuelve el idMedicamento. */
    public function BuscarOCrearMedicamento($nombre)
    {
        try {
            $stm = $this->conex->prepare("
                SELECT idMedicamento FROM medicamento
                WHERE nombre = ? AND estado = 1
                LIMIT 1
            ");
            $stm->execute([$nombre]);
            $row = $stm->fetch(PDO::FETCH_OBJ);
            if ($row) {
                return $row->idMedicamento;
            }

            $ins = $this->conex->prepare("
                INSERT INTO medicamento (nombre, observaciones)
                VALUES (?, 'Registrado automáticamente desde consulta')
            ");
            $ins->execute([$nombre]);
            return $this->conex->lastInsertId();
        } catch (PDOException $e) {
            error_log("BuscarOCrearMedicamento: " . $e->getMessage());
            return null;
        }
    }

    /** Inserta una línea de receta (medicamento asociado a un tratamiento). */
    public function InsertarDetalleReceta($idTratamiento, $idMedicamento, $dosis, $cantidad, $frecuencia, $via, $duracion)
    {
        try {
            $stm = $this->conex->prepare("
                INSERT INTO detalleReceta
                    (idTratamiento, idMedicamento, dosis, cantidad, frecuencia, viaAdministracion, duracion)
                VALUES
                    (:idTratamiento, :idMedicamento, :dosis, :cantidad, :frecuencia, :via, :duracion)
            ");
            return $stm->execute([
                ':idTratamiento' => $idTratamiento,
                ':idMedicamento' => $idMedicamento,
                ':dosis'         => $dosis,
                ':cantidad'      => $cantidad,
                ':frecuencia'    => $frecuencia,
                ':via'           => $via,
                ':duracion'      => $duracion,
            ]);
        } catch (PDOException $e) {
            error_log("InsertarDetalleReceta: " . $e->getMessage());
            return false;
        }
    }

    /** Trae los medicamentos recetados en una consulta (para la ficha / ver detalle). */
    public function ListarMedicamentosPorConsulta($idConsulta)
    {
        try {
            $stm = $this->conex->prepare("
                SELECT m.nombre, dr.dosis, dr.cantidad, dr.frecuencia, dr.viaAdministracion, dr.duracion
                FROM detalleReceta dr
                INNER JOIN tratamiento t ON t.idTratamiento = dr.idTratamiento
                INNER JOIN medicamento m ON m.idMedicamento = dr.idMedicamento
                WHERE t.idConsulta = ? AND dr.estado = 1
            ");
            $stm->execute([$idConsulta]);
            return $stm->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("ListarMedicamentosPorConsulta: " . $e->getMessage());
            return [];
        }
    }

    // =========================================================
    // NUEVO: Estadísticas del dashboard
    // =========================================================

    public function ContarPacientesAtendidos()
    {
        $stm = $this->conex->query("
            SELECT COUNT(DISTINCT ciPaciente) AS total
            FROM consulta
            WHERE estado = 1 AND estadoConsulta = 'ATENDIDA'
        ");
        return (int) $stm->fetch(PDO::FETCH_OBJ)->total;
    }

    public function ContarPendientes()
    {
        $stm = $this->conex->query("
            SELECT COUNT(*) AS total
            FROM consulta
            WHERE estado = 1 AND estadoConsulta = 'NO ASISTIO'
        ");
        return (int) $stm->fetch(PDO::FETCH_OBJ)->total;
    }

    public function ContarConsultasHoy()
    {
        $stm = $this->conex->query("
            SELECT COUNT(*) AS total
            FROM consulta
            WHERE estado = 1 AND DATE(fechaRegistro) = CURDATE()
        ");
        return (int) $stm->fetch(PDO::FETCH_OBJ)->total;
    }

    public function ContarMedicosActivos()
    {
        $stm = $this->conex->query("
            SELECT COUNT(*) AS total
            FROM personalSalud
            WHERE estado = 1
        ");
        return (int) $stm->fetch(PDO::FETCH_OBJ)->total;
    }

    // =========================================================
    // REPORTE: listado de consultas con filtros opcionales
    // =========================================================

    /**
     * Lista consultas para el reporte, aplicando filtros opcionales.
     * $filtros puede traer: fechaInicio, fechaFin, ciPersonal, estadoConsulta.
     * Todos los filtros son opcionales; si no se envían, trae todo.
     */
    public function ListarParaReporte(array $filtros = [])
    {
        try {
            $condiciones = ["v.estado = 1"];
            $params = [];

            if (!empty($filtros['fechaInicio'])) {
                $condiciones[] = "STR_TO_DATE(v.fecha, '%d/%m/%Y') >= :fechaInicio";
                $params[':fechaInicio'] = $filtros['fechaInicio'];
            }
            if (!empty($filtros['fechaFin'])) {
                $condiciones[] = "STR_TO_DATE(v.fecha, '%d/%m/%Y') <= :fechaFin";
                $params[':fechaFin'] = $filtros['fechaFin'];
            }
            if (!empty($filtros['ciPersonal'])) {
                $condiciones[] = "v.ciPersonal = :ciPersonal";
                $params[':ciPersonal'] = $filtros['ciPersonal'];
            }
            if (!empty($filtros['estadoConsulta'])) {
                $condiciones[] = "v.estadoConsulta = :estadoConsulta";
                $params[':estadoConsulta'] = $filtros['estadoConsulta'];
            }

            $sql = "SELECT * FROM vs_vista_consultas v WHERE " . implode(' AND ', $condiciones)
                 . " ORDER BY v.idConsulta DESC";

            $stm = $this->conex->prepare($sql);
            $stm->execute($params);
            return $stm->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("ListarParaReporte: " . $e->getMessage());
            return [];
        }
    }

    /** Lista de médicos/personal para el filtro desplegable del reporte. */
    public function ListarPersonalParaFiltro()
    {
        try {
            $stm = $this->conex->prepare("
                SELECT ciPersonal, CONCAT(nombre, ' ', apaterno, ' ', IFNULL(apmaterno, '')) AS nombreCompleto
                FROM personalSalud
                WHERE estado = 1
                ORDER BY nombre
            ");
            $stm->execute();
            return $stm->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("ListarPersonalParaFiltro: " . $e->getMessage());
            return [];
        }
    }
}