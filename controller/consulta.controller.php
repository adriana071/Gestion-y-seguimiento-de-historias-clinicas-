<?php
require_once 'model/sesion.php';
Sesion::iniciar();

require_once 'model/consulta.php';


class ConsultaController
{
    private $model;

    public function __construct()
    {
        $this->model = new consulta();
    }

    public function Index()
    {
        require_once 'view/header.php';
        require_once 'view/dashboard.php';
        require_once 'view/footer.php';
    }

    public function IndexPage()
    {
        // Datos que la vista necesita y que antes no se preparaban:
        $consultas = $this->model->Listar();

        $medicoNombre       = $_SESSION['nombreCompleto'] ?? 'No disponible';
        $medicoCI            = $_SESSION['ciPersonal']     ?? '';
        $medicoEspecialidad  = $_SESSION['especialidad']   ?? 'No disponible';

        $statPacientesAtendidos = $this->model->ContarPacientesAtendidos();
        $statPendientes         = $this->model->ContarPendientes();
        $statConsultasHoy       = $this->model->ContarConsultasHoy();
        $statMedicosActivos     = $this->model->ContarMedicosActivos();

        require_once 'view/header.php';
        require_once 'view/vconsulta.php';
        require_once 'view/footer.php';
    }

    public function Reporte()
    {
        $filtros = [
            'fechaInicio'    => $_GET['fechaInicio'] ?? '',
            'fechaFin'       => $_GET['fechaFin'] ?? '',
            'ciPersonal'     => $_GET['ciPersonal'] ?? '',
            'estadoConsulta' => $_GET['estadoConsulta'] ?? '',
        ];

        $consultas = $this->model->ListarParaReporte($filtros);
        $personal = $this->model->ListarPersonalParaFiltro();

        require_once 'view/header.php';
        require_once 'view/vreporte_consultas.php';
        require_once 'view/footer.php';
    }

    public function ListarJson()
    {
        header('Content-Type: application/json; charset=utf-8');
        $consulta = $this->model->f_ListarJson();

        if ($consulta) {
            echo json_encode([
                "success" => true,
                "consultas" => $consulta
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No existen consultas."
            ]);
        }
    }

    public function BuscarPaciente()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_GET["ciPaciente"]) || empty($_GET["ciPaciente"])) {
            echo json_encode([
                "success" => false,
                "message" => "Debe ingresar el CI del paciente."
            ]);
            return;
        }

        $paciente = $this->model->BuscarPaciente($_GET["ciPaciente"]);

        if ($paciente) {
            echo json_encode([
                "success" => true,
                "paciente" => $paciente
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Paciente no encontrado."
            ]);
        }
    }

    /**
     * NUEVO: endpoint que el formulario de la vista realmente llama
     * (fetch a ?c=consulta&a=InsertarAjax). Responde siempre JSON.
     * Los medicamentos ya NO se capturan aquí: eso pasa al flujo de
     * "Emitir receta" que se habilita después de guardar la consulta.
     */
    public function InsertarAjax()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_POST)) {
            echo json_encode(["success" => false, "message" => "No se recibieron datos."]);
            exit();
        }

        $campos = ["ciPaciente", "motivo", "diagnostico", "peso", "estatura", "temperatura", "presionArterial"];
        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
                echo json_encode(["success" => false, "message" => "El campo $campo es obligatorio."]);
                exit();
            }
        }

        // El médico que atiende SIEMPRE es el que tiene la sesión abierta,
        // nunca se autoselecciona desde el formulario.
        $ciPersonal = $_SESSION["ciPersonal"] ?? null;
        if (!$ciPersonal) {
            echo json_encode(["success" => false, "message" => "No se identificó al personal de salud en la sesión."]);
            exit();
        }

        $estadosValidos = ['ATENDIDA', 'CANCELADA', 'NO ASISTIO'];
        $estadoConsulta = $_POST["estadoConsulta"] ?? 'ATENDIDA';
        if (!in_array($estadoConsulta, $estadosValidos, true)) {
            echo json_encode(["success" => false, "message" => "Estado de consulta inválido."]);
            exit();
        }

        $alm = new consulta();
        $alm->ciPaciente              = trim($_POST["ciPaciente"]);
        $alm->ciPersonal              = $ciPersonal;
        $alm->motivo                  = trim($_POST["motivo"]);
        $alm->diagnostico             = trim($_POST["diagnostico"]);
        $alm->estadoConsulta          = $estadoConsulta;
        $alm->peso                    = $_POST["peso"];
        $alm->estatura                = $_POST["estatura"];
        $alm->temperatura             = $_POST["temperatura"];
        $alm->presionArterial         = $_POST["presionArterial"];
        $alm->nombreTratamiento       = $_POST["nombreTratamiento"] ?? '';
        $alm->descripcionTratamiento  = $_POST["descripcionTratamiento"] ?? '';

        $resultado = $this->model->Insertar($alm);

        if (!$resultado || (int)$resultado->codigoError !== 0) {
            $mensaje = $resultado->mensaje ?? "Ocurrió un error al guardar la consulta.";
            echo json_encode(["success" => false, "message" => $mensaje]);
            exit();
        }

        echo json_encode([
            "success" => true,
            "message" => $resultado->mensaje ?? "Consulta registrada correctamente.",
            "idConsulta" => $resultado->idConsulta
        ]);
    }

    /**
     * NUEVO: edición vía AJAX (usada por el botón "Editar" de la tabla).
     * sp_editar_consulta bloquea la edición si la consulta ya tiene
     * tratamiento o exámenes de laboratorio registrados; ese mensaje
     * de error llega tal cual al SweetAlert de la vista.
     */
    public function EditarAjax()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_POST["idConsulta"])) {
            echo json_encode(["success" => false, "message" => "No se recibió el ID de la consulta."]);
            exit();
        }

        $campos = ["motivo", "diagnostico", "peso", "estatura", "temperatura", "presionArterial"];
        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
                echo json_encode(["success" => false, "message" => "El campo $campo es obligatorio."]);
                exit();
            }
        }

        $alm = new consulta();
        $alm->idConsulta      = $_POST["idConsulta"];
        $alm->motivo          = trim($_POST["motivo"]);
        $alm->diagnostico     = trim($_POST["diagnostico"]);
        $alm->estadoConsulta  = $_POST["estadoConsulta"] ?? 'ATENDIDA';
        $alm->peso            = $_POST["peso"];
        $alm->estatura        = $_POST["estatura"];
        $alm->temperatura     = $_POST["temperatura"];
        $alm->presionArterial = $_POST["presionArterial"];

        $resultado = $this->model->Editar($alm);

        if ($resultado && (int)$resultado->codigoError === 0) {
            echo json_encode(["success" => true, "message" => $resultado->mensaje]);
        } else {
            $mensaje = $resultado->mensaje ?? "Ocurrió un error al editar la consulta.";
            echo json_encode(["success" => false, "message" => $mensaje]);
        }
    }

    /**
     * NUEVO: trae la condición física ya registrada para el paciente
     * (p.ej. tomada por enfermería) y que aún no está ligada a ninguna
     * consulta, para que el médico no tenga que volver a digitarla.
     */
    public function CondicionFisicaAjax()
    {
        header('Content-Type: application/json; charset=utf-8');

        $ciPaciente = $_GET['ciPaciente'] ?? null;
        if (!$ciPaciente) {
            echo json_encode(["success" => false, "message" => "Debe indicar el CI del paciente."]);
            exit();
        }

        $condicion = $this->model->ObtenerCondicionFisicaDisponible($ciPaciente);

        if ($condicion) {
            echo json_encode(["success" => true, "condicion" => $condicion]);
        } else {
            echo json_encode(["success" => false, "message" => "No hay signos vitales registrados para este paciente."]);
        }
    }

    /**
     * NUEVO: usado por "Ver" y "Editar" en la tabla para traer los
     * datos de una consulta puntual (incluye medicamentos recetados).
     */
    public function ObtenerAjax()
    {
        header('Content-Type: application/json; charset=utf-8');

        $idConsulta = $_GET['idConsulta'] ?? null;
        if (!$idConsulta) {
            echo json_encode(["success" => false, "message" => "No se recibió el ID de la consulta."]);
            exit();
        }

        $consulta = $this->model->Obtener($idConsulta);

        if (!$consulta) {
            echo json_encode(["success" => false, "message" => "Consulta no encontrada."]);
            exit();
        }

        $medicamentos = $this->model->ListarMedicamentosPorConsulta($idConsulta);

        echo json_encode([
            "success" => true,
            "consulta" => $consulta,
            "medicamentos" => $medicamentos
        ]);
    }

    /**
     * NUEVO: la vista ya llamaba a esta acción (botón eliminar) pero
     * no existía en el controlador.
     */
    public function Eliminar()
    {
        header('Content-Type: application/json; charset=utf-8');

        $idConsulta = $_GET['idConsulta'] ?? null;
        if (!$idConsulta) {
            echo json_encode(["success" => false, "message" => "No se recibió el ID de la consulta."]);
            exit();
        }

        $resultado = $this->model->Eliminar($idConsulta);

        if ($resultado && (int)$resultado->codigoError === 0) {
            echo json_encode(["success" => true, "message" => $resultado->mensaje]);
        } else {
            $mensaje = $resultado->mensaje ?? "Ocurrió un error al eliminar la consulta.";
            echo json_encode(["success" => false, "message" => $mensaje]);
        }
    }

    /**
     * Se mantiene tal cual la tenías (registro tradicional con
     * redirección), por si la sigues usando en algún flujo sin AJAX.
     */
    public function InsEditar()
    {
        if (empty($_POST)) {
            echo "<script>
                alert('No se recibieron datos.');
                history.back();
             </script>";
            exit();
        }

        $alm = new consulta();

        $campos = [
            "ciPaciente",
            "motivo",
            "diagnostico",
            "peso",
            "estatura",
            "temperatura",
            "presionArterial"
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || trim($_POST[$campo]) == '') {
                echo "<script>
                    alert('El campo " . $campo . " es obligatorio');
                    history.back();
                  </script>";
                exit();
            }
        }

        $alm->ciPaciente = trim($_POST["ciPaciente"]);
        $alm->ciPersonal = $_SESSION["ciPersonal"];
        $alm->motivo = trim($_POST["motivo"]);
        $alm->diagnostico = trim($_POST["diagnostico"]);
        $alm->estadoConsulta = $_POST["estadoConsulta"];
        $alm->peso = $_POST["peso"];
        $alm->estatura = $_POST["estatura"];
        $alm->temperatura = $_POST["temperatura"];
        $alm->presionArterial = $_POST["presionArterial"];
        $alm->nombreTratamiento = $_POST["nombreTratamiento"] ?? '';
        $alm->descripcionTratamiento = $_POST["descripcionTratamiento"] ?? '';
        $alm->idTratamiento = $_POST["idTratamiento"] ?? null;

        $accion = $_REQUEST["ac"] ?? "";
        $resultado = null;

        try {
            if ($accion == "nuevo") {
                $resultado = $this->model->Insertar($alm);
            } elseif ($accion == "editar") {
                if (!isset($_POST["idConsulta"]) || empty($_POST["idConsulta"])) {
                    throw new Exception("No se recibió el ID de la consulta.");
                }
                $alm->idConsulta = $_POST["idConsulta"];
                $resultado = $this->model->Editar($alm);
            } else {
                throw new Exception("Acción no válida.");
            }
        } catch (Exception $e) {
            echo "<script>
                alert('" . $e->getMessage() . "');
                history.back();
              </script>";
            exit();
        }

        if ($resultado && $resultado->codigoError == 0) {
            header("Location:index.php?c=consulta&a=IndexPage&msg="
                . urlencode($resultado->mensaje));
            exit();
        } else {
            $mensaje = "Ocurrió un error.";
            if ($resultado) {
                $mensaje = $resultado->mensaje;
            }
            echo "<script>
                alert('" . $mensaje . "');
                history.back();
              </script>";
            exit();
        }
    }
}