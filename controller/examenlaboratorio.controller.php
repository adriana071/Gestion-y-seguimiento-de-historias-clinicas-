<?php

require_once 'model/sesion.php';
Sesion::iniciar();

require_once 'model/examenlaboratorio.php';


class ExamenLaboratorioController
{
    private $model;


    // =========================================================
    // CONSTRUCTOR
    // =========================================================

    public function __construct()
    {
        $this->model = new examenLaboratorio();
    }


    // =========================================================
    // PÁGINA PRINCIPAL
    // =========================================================

    public function IndexPage()
    {
        // -----------------------------------------------------
        // Listar todos los exámenes
        // -----------------------------------------------------

        $examenes = $this->model->Listar();


        // -----------------------------------------------------
        // Estadísticas
        // -----------------------------------------------------

        $statPendientes = $this->model->ContarPendientes();

        $statResultados =
            $this->model->ContarResultadosDisponibles();


        // -----------------------------------------------------
        // Si viene desde una consulta específica
        // -----------------------------------------------------

        $idConsulta = $_GET['idConsulta'] ?? null;

        $consulta = null;

        $examenesConsulta = [];


        if ($idConsulta) {

            $consulta =
                $this->model->ObtenerConsulta($idConsulta);

            if ($consulta) {

                $examenesConsulta =
                    $this->model->ListarPorConsulta($idConsulta);
            }
        }


        // -----------------------------------------------------
        // Cargar vista
        // -----------------------------------------------------

        require_once 'view/header.php';

        require_once 'view/vexamenlaboratorio.php';

        require_once 'view/footer.php';
    }


    // =========================================================
    // REPORTE IMPRIMIBLE
    // =========================================================

    public function Reporte()
    {
        $filtros = [
            'fechaInicio'   => $_GET['fechaInicio'] ?? '',
            'fechaFin'      => $_GET['fechaFin'] ?? '',
            'estadoExamen'  => $_GET['estadoExamen'] ?? '',
        ];

        $examenes = $this->model->ListarParaReporte($filtros);

        require_once 'view/header.php';
        require_once 'view/vreporte_examenes.php';
        require_once 'view/footer.php';
    }


    // =========================================================
    // LISTAR JSON
    // =========================================================
    // Se utilizará posteriormente para AJAX / Flutter.
    // =========================================================

    public function ListarJson()
    {
        header(
            'Content-Type: application/json; charset=utf-8'
        );


        $examenes =
            $this->model->f_ListarJson();


        if ($examenes) {

            echo json_encode([
                "success" => true,
                "examenes" => $examenes
            ]);
        } else {

            echo json_encode([
                "success" => false,
                "message" => "No existen exámenes de laboratorio."
            ]);
        }
    }


    // =========================================================
    // OBTENER EXAMEN
    // =========================================================
    // Devuelve un examen específico en formato JSON.
    // =========================================================

    public function ObtenerAjax()
    {
        header(
            'Content-Type: application/json; charset=utf-8'
        );


        $idExamen =
            $_GET['idExamen'] ?? null;


        if (!$idExamen) {

            echo json_encode([
                "success" => false,
                "message" => "No se recibió el ID del examen."
            ]);

            exit();
        }


        $examen =
            $this->model->Obtener($idExamen);


        if (!$examen) {

            echo json_encode([
                "success" => false,
                "message" => "Examen no encontrado."
            ]);

            exit();
        }


        echo json_encode([
            "success" => true,
            "examen" => $examen
        ]);
    }


    // =========================================================
    // LISTAR EXÁMENES DE UNA CONSULTA
    // =========================================================
    // Permite consultar los exámenes asociados a una consulta.
    // =========================================================

    public function ListarPorConsultaAjax()
    {
        header(
            'Content-Type: application/json; charset=utf-8'
        );


        $idConsulta =
            $_GET['idConsulta'] ?? null;


        if (!$idConsulta) {

            echo json_encode([
                "success" => false,
                "message" => "No se recibió el ID de la consulta."
            ]);

            exit();
        }


        $consulta =
            $this->model->ObtenerConsulta($idConsulta);


        if (!$consulta) {

            echo json_encode([
                "success" => false,
                "message" => "La consulta no existe."
            ]);

            exit();
        }


        $examenes =
            $this->model->ListarPorConsulta($idConsulta);


        echo json_encode([
            "success" => true,
            "consulta" => $consulta,
            "examenes" => $examenes
        ]);
    }


    // =========================================================
    // REGISTRAR EXAMEN
    // =========================================================

    public function InsertarAjax()
    {
        header(
            'Content-Type: application/json; charset=utf-8'
        );


        // -----------------------------------------------------
        // Verificar datos recibidos
        // -----------------------------------------------------

        if (empty($_POST)) {

            echo json_encode([
                "success" => false,
                "message" => "No se recibieron datos."
            ]);

            exit();
        }


        // -----------------------------------------------------
        // Validar ID de consulta
        // -----------------------------------------------------

        if (
            !isset($_POST['idConsulta']) ||
            trim($_POST['idConsulta']) === ''
        ) {

            echo json_encode([
                "success" => false,
                "message" => "Debe seleccionar una consulta."
            ]);

            exit();
        }


        // -----------------------------------------------------
        // Validar tipo de examen
        // -----------------------------------------------------

        if (
            !isset($_POST['tipoExamen']) ||
            trim($_POST['tipoExamen']) === ''
        ) {

            echo json_encode([
                "success" => false,
                "message" => "Debe indicar el tipo de examen."
            ]);

            exit();
        }


        // -----------------------------------------------------
        // Fecha de solicitud
        // -----------------------------------------------------

        $fechaSolicitud =
            $_POST['fechaSolicitud']
            ?? date('Y-m-d');


        // -----------------------------------------------------
        // Crear objeto
        // -----------------------------------------------------

        $alm = new examenLaboratorio();


        $alm->idConsulta =
            trim($_POST['idConsulta']);


        $alm->tipoExamen =
            trim($_POST['tipoExamen']);


        $alm->fechaSolicitud =
            $fechaSolicitud;


        // -----------------------------------------------------
        // Registrar
        // -----------------------------------------------------

        $resultado =
            $this->model->Insertar($alm);


        if (
            !$resultado ||
            (int)$resultado->codigoError !== 0
        ) {

            $mensaje =
                $resultado->mensaje
                ?? "Ocurrió un error al registrar el examen.";


            echo json_encode([
                "success" => false,
                "message" => $mensaje
            ]);

            exit();
        }


        // -----------------------------------------------------
        // Respuesta exitosa
        // -----------------------------------------------------

        echo json_encode([

            "success" => true,

            "message" =>
            $resultado->mensaje
                ?? "Examen registrado correctamente.",

            "idExamen" =>
            $resultado->idExamen

        ]);
    }


    // =========================================================
    // EDITAR EXAMEN
    // =========================================================

    public function EditarAjax()
    {
        header(
            'Content-Type: application/json; charset=utf-8'
        );


        // -----------------------------------------------------
        // ID
        // -----------------------------------------------------

        if (
            !isset($_POST['idExamen']) ||
            empty($_POST['idExamen'])
        ) {

            echo json_encode([
                "success" => false,
                "message" => "No se recibió el ID del examen."
            ]);

            exit();
        }


        // -----------------------------------------------------
        // Tipo de examen
        // -----------------------------------------------------

        if (
            !isset($_POST['tipoExamen']) ||
            trim($_POST['tipoExamen']) === ''
        ) {

            echo json_encode([
                "success" => false,
                "message" => "Debe indicar el tipo de examen."
            ]);

            exit();
        }


        // -----------------------------------------------------
        // Fecha
        // -----------------------------------------------------

        $fechaSolicitud =
            $_POST['fechaSolicitud']
            ?? date('Y-m-d');


        // -----------------------------------------------------
        // Crear objeto
        // -----------------------------------------------------

        $alm = new examenLaboratorio();


        $alm->idExamen =
            $_POST['idExamen'];


        $alm->tipoExamen =
            trim($_POST['tipoExamen']);


        $alm->fechaSolicitud =
            $fechaSolicitud;


        // -----------------------------------------------------
        // Editar
        // -----------------------------------------------------

        $resultado =
            $this->model->Editar($alm);


        if (
            $resultado &&
            (int)$resultado->codigoError === 0
        ) {

            echo json_encode([
                "success" => true,
                "message" => $resultado->mensaje
            ]);
        } else {

            $mensaje =
                $resultado->mensaje
                ?? "Ocurrió un error al editar el examen.";


            echo json_encode([
                "success" => false,
                "message" => $mensaje
            ]);
        }
    }


    // =========================================================
    // ELIMINAR EXAMEN
    // =========================================================

    public function CancelarSolicitud()
    {
        header('Content-Type: application/json; charset=utf-8');

        $idConsulta = $_POST['idConsulta'] ?? null;

        if (!$idConsulta) {
            echo json_encode(['success' => false, 'message' => 'Falta el idConsulta.']);
            return;
        }

        $resultado = $this->model->CancelarPendientesPorConsulta($idConsulta);

        echo json_encode([
            'success' => $resultado->codigoError === 0,
            'message' => $resultado->mensaje
        ]);
    }

    public function Eliminar()
    {
        header(
            'Content-Type: application/json; charset=utf-8'
        );


        $idExamen =
            $_GET['idExamen'] ?? null;


        if (!$idExamen) {

            echo json_encode([
                "success" => false,
                "message" => "No se recibió el ID del examen."
            ]);

            exit();
        }


        // -----------------------------------------------------
        // Eliminar
        // -----------------------------------------------------

        $resultado =
            $this->model->Eliminar($idExamen);


        if (
            $resultado &&
            (int)$resultado->codigoError === 0
        ) {

            echo json_encode([
                "success" => true,
                "message" => $resultado->mensaje
            ]);
        } else {

            $mensaje =
                $resultado->mensaje
                ?? "Ocurrió un error al eliminar el examen.";


            echo json_encode([
                "success" => false,
                "message" => $mensaje
            ]);
        }
    }


    // =========================================================
    // COMPROBAR RESULTADO
    // =========================================================
    // Nos permite saber si el examen ya tiene resultado.
    // =========================================================

    public function TieneResultadoAjax()
    {
        header(
            'Content-Type: application/json; charset=utf-8'
        );


        $idExamen =
            $_GET['idExamen'] ?? null;


        if (!$idExamen) {

            echo json_encode([
                "success" => false,
                "message" => "No se recibió el ID del examen."
            ]);

            exit();
        }


        $tieneResultado =
            $this->model->TieneResultado($idExamen);


        echo json_encode([

            "success" => true,

            "tieneResultado" =>
            $tieneResultado

        ]);
    }
}
