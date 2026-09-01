<?php
require_once('model/resultado.php');

class ResultadoController {
    private $model;

    public function __construct() {
        $this->model = new Resultado();
    }

    // ======================================================
    // PÁGINA PRINCIPAL - Listado de resultados
    // ======================================================
    public function Index()
    {
        require_once 'view/header.php';
        require_once 'view/dashboard.php';
        require_once 'view/footer.php';
    }

    public function IndexPage()
    {
        $resultados       = $this->model->Listar();
        $totalResultados  = $this->model->ContarTotal();
        $normales         = $this->model->ContarPorEstado('NORMAL');
        $anormales        = $this->model->ContarPorEstado('ANORMAL');
        $pendientes       = $this->model->ContarPorEstado('PENDIENTE');

        require_once 'view/header.php';
        require_once 'view/vresultado.php';
        require_once 'view/footer.php';
    }

    // ======================================================
    // REPORTE IMPRIMIBLE
    // ======================================================
    public function Reporte()
    {
        $filtros = [
            'fechaInicio'  => $_GET['fechaInicio'] ?? '',
            'fechaFin'     => $_GET['fechaFin'] ?? '',
            'estadoExamen' => $_GET['estadoExamen'] ?? '',
        ];

        $resultados = $this->model->ListarParaReporte($filtros);

        require_once 'view/header.php';
        require_once 'view/vreporte_resultados.php';
        require_once 'view/footer.php';
    }

    // ======================================================
    // REGISTRAR NUEVO RESULTADO (AJAX)
    // ======================================================
    public function InsertarAjax()
    {
        header('Content-Type: application/json; charset=utf-8');

        $data = new Resultado();
        $data->idExamen      = $_POST['idExamen'] ?? null;
        $data->resultado     = trim($_POST['resultado'] ?? '');
        $data->observaciones = trim($_POST['observaciones'] ?? '');
        $data->documento     = trim($_POST['documento'] ?? '');
        $data->estadoExamen  = $_POST['estadoExamen'] ?? 'PENDIENTE';

        if (!$data->idExamen || $data->resultado === '') {
            echo json_encode(['success' => false, 'message' => 'Complete los campos obligatorios.']);
            return;
        }

        $resultado = $this->model->Insertar($data);

        echo json_encode([
            'success'     => $resultado->codigoError === 0,
            'message'     => $resultado->mensaje,
            'idResultado' => $resultado->idResultado ?? null,
        ]);
    }

    // ======================================================
    // OBTENER UN RESULTADO (AJAX, para ver/editar en modal)
    // ======================================================
    public function ObtenerAjax()
    {
        header('Content-Type: application/json; charset=utf-8');

        $idResultado = $_GET['idResultado'] ?? null;

        if (!$idResultado) {
            echo json_encode(['success' => false, 'message' => 'ID de resultado no proporcionado.']);
            return;
        }

        $resultado = $this->model->Obtener($idResultado);

        if ($resultado) {
            echo json_encode(['success' => true, 'resultado' => $resultado]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Resultado no encontrado.']);
        }
    }

    // ======================================================
    // EDITAR RESULTADO (AJAX)
    // ======================================================
    public function EditarAjax()
    {
        header('Content-Type: application/json; charset=utf-8');

        $data = new Resultado();
        $data->idResultado   = $_POST['idResultado'] ?? null;
        $data->resultado     = trim($_POST['resultado'] ?? '');
        $data->observaciones = trim($_POST['observaciones'] ?? '');
        $data->documento     = trim($_POST['documento'] ?? '');
        $data->estadoExamen  = $_POST['estadoExamen'] ?? 'PENDIENTE';

        if (!$data->idResultado || $data->resultado === '') {
            echo json_encode(['success' => false, 'message' => 'Complete los campos obligatorios.']);
            return;
        }

        $resultado = $this->model->Editar($data);

        echo json_encode([
            'success' => $resultado->codigoError === 0,
            'message' => $resultado->mensaje,
        ]);
    }

    // ======================================================
    // ELIMINAR RESULTADO (AJAX)
    // ======================================================
    public function Eliminar() {
        header('Content-Type: application/json');
        
        $idResultado = isset($_GET['idResultado']) ? $_GET['idResultado'] : null;
        
        if (!$idResultado) {
            echo json_encode(['success' => false, 'message' => 'ID de resultado no proporcionado']);
            return;
        }

        $resultado = $this->model->Eliminar($idResultado);

        if ($resultado->codigoError == 0) {
            echo json_encode(['success' => true, 'message' => $resultado->mensaje]);
        } else {
            echo json_encode(['success' => false, 'message' => $resultado->mensaje]);
        }
    }

    // ======================================================
    // OBTENER RESULTADO POR EXAMEN (AJAX)
    // ======================================================
    public function ObtenerPorExamen() {
        header('Content-Type: application/json');
        
        $idExamen = isset($_GET['idExamen']) ? $_GET['idExamen'] : null;
        
        if (!$idExamen) {
            echo json_encode(['success' => false, 'message' => 'ID de examen no proporcionado']);
            return;
        }

        $resultado = $this->model->ObtenerPorExamen($idExamen);

        if ($resultado) {
            echo json_encode(['success' => true, 'resultado' => $resultado]);
        } else {
            echo json_encode(['success' => false, 'message' => 'El examen no tiene resultado']);
        }
    }
}
?>