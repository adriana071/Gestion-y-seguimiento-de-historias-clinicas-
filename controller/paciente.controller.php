<?php 
require_once 'model/paciente.php';

class PacienteController
{
    private $model;

    public function __construct()
    {
        $this->model = new paciente(); // ← Cambiar a mayúscula
    }    

    public function Index()
    {
        require_once 'view/header.php';
        require_once 'view/dashboard.php';
        require_once 'view/footer.php';
    }

    public function IndexPage()
    {
        require_once 'view/header.php';
        require_once 'view/vpaciente.php';
        require_once 'view/footer.php';
    }

        // FLUTTER
        public function ListarJson()
        {
            header('Content-Type: application/json; charset=utf-8');
            $pacientes = $this->model->f_ListarJson();
    
            if ($pacientes) {
                echo json_encode([
                    "success" => true,
                    "pacientes" => $pacientes
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "No se encontraron pacientes"
                ]);
            }
        }

    public function InsEditar()
    {
        
        // ✅ 1. VERIFICAR QUE LLEGUEN DATOS
        if (empty($_POST)) {
            echo "<script>
                    alert('No se recibieron datos del formulario');
                    window.history.back();
                  </script>";
            exit();
        }

        // ✅ 2. VERIFICAR ACCIÓN
        $accion = isset($_REQUEST['ac']) ? $_REQUEST['ac'] : '';
        if (!in_array($accion, ['nuevo', 'editar'])) {
            echo "<script>
                    alert('Acción no válida');
                    window.history.back();
                  </script>";
            exit();
        }

        $usr = new paciente(); 

        // CAMPOS DEL FORMULARIO
        $campos = [
            "ciPaciente", "codigoPaciente", "nombre", "apaterno", "apmaterno",
            "fechaNacimiento", "genero", "telefono", "email", "direccion", "seguroSalud"
        ];

        //  VALIDACIÓN DE CAMPOS OBLIGATORIOS
        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                echo "<script>
                        alert('El campo $campo es obligatorio');
                        window.history.back();
                      </script>";
                exit();
            }
        }

        // VALIDACIÓN DE FORMATO (defensa en profundidad, por si se evita el JS)
        if (!preg_match('/^\d{5,10}$/', $_POST['ciPaciente'])) {
            echo "<script>alert('CI inválido (solo números, entre 5 y 10 dígitos)'); window.history.back();</script>";
            exit();
        }

        if (!preg_match('/^\d{7,8}$/', $_POST['telefono'])) {
            echo "<script>alert('Teléfono inválido (debe tener 7 u 8 dígitos)'); window.history.back();</script>";
            exit();
        }

        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('Correo electrónico inválido'); window.history.back();</script>";
            exit();
        }

        $hoy = date('Y-m-d');
        if ($_POST['fechaNacimiento'] >= $hoy) {
            echo "<script>alert('La fecha de nacimiento no puede ser hoy ni futura'); window.history.back();</script>";
            exit();
        }

        // ASIGNAR VALORES
        foreach ($campos as $campo) {
            $usr->$campo = $_POST[$campo];
        }

        //EJECUTAR ACCIÓN
        $success = false;
        $message = "";

        try {
            if ($accion === 'nuevo') {
                $success = $this->model->Insertar($usr);
                $message = $success ? "Paciente registrado correctamente." : "Error al registrar.";
            } elseif ($accion === 'editar') {
                $success = $this->model->Editar($usr);
                $message = $success ? "Paciente actualizado correctamente." : "Error al actualizar.";
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }

        // RESPUESTA PARA FLUTTER
        if (isset($_REQUEST['dsn']) && $_REQUEST['dsn'] === 'flutter') {
            header('Content-Type: application/json');
            echo json_encode([
                "success" => $success,
                "message" => $message
            ]);
            return;
        }

        //REDIRECCIÓN WEB CON MENSAJES
        header('Location: index.php?c=paciente&msg=' . urlencode($message));
        exit();
    }

    public function Eliminar()
    {
        if (!isset($_REQUEST['ciPaciente'])) {
            header('Location: index.php?c=paciente&msg=' . urlencode('CI no proporcionado'));
            exit();
        }
        
        $success = $this->model->Eliminar($_REQUEST['ciPaciente']);
        $message = $success ? "Paciente eliminado correctamente." : "Error al eliminar.";
        
        header('Location: index.php?c=paciente&msg=' . urlencode($message));
        exit();
    }
}