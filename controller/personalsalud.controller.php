<?php
require_once 'model/personalSalud.php';

class PersonalSaludController
{
    private $model;
    
    public function __construct()
    {
        $this->model = new personalSalud();
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
        require_once 'view/vpersonalSalud.php';
        require_once 'view/footer.php';
    }
    
    // FLUTTER
    public function ListarJson()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personalSaluds = $this->model->f_ListarJson();
        if ($personalSaluds) {
            echo json_encode([
                "success" => true,
                "personalSaluds" => $personalSaluds
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No se encontraron personal de salud"
            ]);
        }
    }
    
    public function InsEditar()
    {
        $usr = new personalSalud();
        
        // ✅ CAMPOS OBLIGATORIOS (EXCLUYENDO FOTO)
        $camposObligatorios = [
            "ciPersonal", "nombre", "apaterno", "apmaterno", "fechaNacimiento", "genero",
            "telefono", "direccion", "email", "profesion", "nacionalidad",
            "tituloProfesional", "anioTitulacion", "universidad",
            "tipoContrato", "fechaIngreso", "afiliacionSeguro", "codCargo", "codEspecialidad"
        ];
        
        // 🔴 VALIDACIÓN DE CAMPOS OBLIGATORIOS
        foreach ($camposObligatorios as $campo) {
            if (empty($_POST[$campo])) {
                echo "<script>
                        alert('El campo $campo es obligatorio');
                        window.history.back();
                      </script>";
                exit();
            }
        }
        
        // ✅ CAMPO OPCIONAL - Fecha Fin Contrato
        $usr->fechaFinContrato = !empty($_POST['fechaFinContrato']) ? $_POST['fechaFinContrato'] : null;
        
        // ✅ CAMPO OPCIONAL - NUA
        $usr->nua = !empty($_POST['nua']) ? $_POST['nua'] : null;
        
        // ✅ CAMPO OPCIONAL - Observaciones
        $usr->observaciones = !empty($_POST['observaciones']) ? $_POST['observaciones'] : null;
        
        // 🔴 VALIDACIÓN DE EDAD (>= 18 años)
        $fecha = $_POST['fechaNacimiento'];
        $hoy = date("Y-m-d");
        $edad = date_diff(date_create($fecha), date_create($hoy))->y;
        
        if ($edad < 18) {
            echo "<script>alert('El personal debe ser mayor de 18 años'); window.history.back();</script>";
            exit();
        }

        // VALIDACIÓN DE FORMATO (defensa en profundidad, por si se evita el JS)
        if (!preg_match('/^\d{6,10}$/', $_POST['ciPersonal'])) {
            echo "<script>alert('CI inválido (solo números, entre 6 y 10 dígitos)'); window.history.back();</script>";
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
        
        // 🟢 ASIGNAR VALORES OBLIGATORIOS
        foreach ($camposObligatorios as $campo) {
            $usr->$campo = $_POST[$campo];
        }
        
      // 🟡 MANEJO DE FOTO
      if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nombreUnico = uniqid('personalSalud_', true) . '.' . $ext;
        $ruta = "view/img/" . $nombreUnico;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta)) {
            $usr->foto = $ruta;
        } else {
            $usr->foto = "view/img/default.png";
        }
    } else {
        // ⚠️ IMPORTANTE: en EDITAR no sobrescribir foto si no suben nueva
        if (isset($_REQUEST['ac']) && $_REQUEST['ac'] === 'editar') {
            $usr->foto = $_POST['foto_actual'] ?? "view/img/default.png";
        } else {
            $usr->foto = "view/img/default.png";
        }
    }
        $success = false;
        $message = "";
        
        // 🔵 DIFERENCIAR NUEVO / EDITAR
        if (isset($_REQUEST['ac']) && $_REQUEST['ac'] === 'nuevo') {
            $success = $this->model->Insertar($usr);
            $message = $success ? "Personal registrado correctamente." : "Error al registrar.";
        } 
        elseif (isset($_REQUEST['ac']) && $_REQUEST['ac'] === 'editar') {
            $success = $this->model->Editar($usr);
            $message = $success ? "Personal actualizado correctamente." : "Error al actualizar.";
        }
        
        // 🔄 REDIRECCIÓN
        if ($success) {
            header('Location: index.php?c=personalSalud&msg=' . urlencode($message));
            exit();
        } else {
            echo "<h3>Error al guardar los datos.</h3>";
            echo "<p>$message</p>";
            exit();
        }
    }
    
    public function Eliminar()
    {
        if (!isset($_REQUEST['ciPersonal'])) {
            header('Location: index.php?c=personalSalud&msg=' . urlencode('CI no proporcionado'));
            exit();
        }
        
        $success = $this->model->Eliminar($_REQUEST['ciPersonal']);
        $message = $success ? "Personal eliminado correctamente." : "Error al eliminar.";
        
        header('Location: index.php?c=personalSalud&msg=' . urlencode($message));
        exit();
    }
}