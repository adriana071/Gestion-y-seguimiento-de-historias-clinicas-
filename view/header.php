<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang=""> <!--<![endif]-->

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Hospital Parroquial Santa Clara</title>
    <meta name="description" content="Hospital Parroquial Santa Clara">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="view/images/logo-santa-clara.png">
    <link rel="shortcut icon" href="view/images/logo-santa-clara.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/normalize.css@8.0.0/normalize.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lykmapipo/themify-icons@0.1.2/css/themify-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pixeden-stroke-7-icon@1.2.3/pe-icon-7-stroke/dist/pe-icon-7-stroke.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.2.0/css/flag-icon.min.css">
    <link rel="stylesheet" href="assets/css/cs-skin-elastic.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css"> <!-- CSS del template -->
    <link rel="stylesheet" href="assets/css/tema-santa-clara.css"> <!-- tu tema, va al final -->
    <!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/html5shiv/3.7.3/html5shiv.min.js"></script> -->
    <link href="https://cdn.jsdelivr.net/npm/chartist@0.11.0/dist/chartist.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/jqvmap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/weathericons@2.1.0/css/weather-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@3.9.0/dist/fullcalendar.min.css" rel="stylesheet" />

    <style>
        #weatherWidget .currentDesc {
            color: #ffffff !important;
        }

        .traffic-chart {
            min-height: 335px;
        }

        #flotPie1 {
            height: 150px;
        }

        #flotPie1 td {
            padding: 3px;
        }

        #flotPie1 table {
            top: 20px !important;
            right: -10px !important;
        }

        .chart-container {
            display: table;
            min-width: 270px;
            text-align: left;
            padding-top: 10px;
            padding-bottom: 10px;
        }

        #flotLine5 {
            height: 105px;
        }

        #flotBarChart {
            height: 150px;
        }

        #cellPaiChart {
            height: 160px;
        }
    </style>
</head>

<body>
    <!-- Left Panel -->
    <aside id="left-panel" class="left-panel">
        <nav class="navbar navbar-expand-sm navbar-default">
            <div id="main-menu" class="main-menu collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li class="active">
                        <a href="http://localhost:3000/index.php"><i class="menu-icon fa fa-home"></i>Dashboard </a>
                    </li>
                    <li class="menu-title">Registros</li><!-- /.menu-title -->
                    <li class="menu-item-has-children dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="menu-icon fa fa-folder-open"></i>Registros</a>
                        <ul class="sub-menu children dropdown-menu">
                            <li><i class="fa fa-check"></i><a href="http://localhost:3000/index.php?c=rol">Roles</a></li>
                            <li><i class="fa fa-user-md"></i><a href="http://localhost:3000/index.php?c=cargo">Cargos</a></li>
                            <li><i class="fa fa-stethoscope"></i><a href="http://localhost:3000/index.php?c=especialidad">Especialidades</a></li>
                            <li><i class="fa fa-id-card-o"></i><a href="http://localhost:3000/index.php?c=usuario">Usuarios</a></li>
                        </ul>
                    </li>

                    <li class="menu-item-has-children dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="menu-icon fa fa-hospital-o"></i>Administracion</a>
                        <ul class="sub-menu children dropdown-menu">
                            <li><i class="fa fa-user-md"></i><a href="http://localhost:3000/index.php?c=personalSalud">Personal de Salud</a></li>
                            <li><i class="fa fa-wheelchair"></i><a href="http://localhost:3000/index.php?c=paciente">Paciente</a></li>
                            <li><i class="fa fa-heartbeat"></i><a href="http://localhost:3000/index.php?c=condicionfisica">Triaje</a></li>
                        </ul>
                    </li>

                    <li class="menu-item-has-children dropdown">
                        <a href="http://localhost:3000/index.php?c=consulta" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="menu-icon fa fa-stethoscope"></i>Consultas</a>
                        <ul class="sub-menu children dropdown-menu">
                            <li><i class="menu-icon fa fa-stethoscope"></i><a href="http://localhost:3000/index.php?c=consulta">Consultas Medicas</a></li>
                            <li><i class="menu-icon fa fa-flask"></i><a href="http://localhost:3000/index.php?c=examenlaboratorio">Examenes de Laboratorios</a></li>
                            <li><i class="menu-icon fa fa-file-text-o"></i><a href="http://localhost:3000/index.php?c=resultado">Resultados de Laboratorios</a></li>
                        </ul>
                    </li>

                    <li class="menu-title">Recetas</li><!-- /.menu-title -->

                    <li class="menu-item-has-children dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="menu-icon fa fa-medkit"></i>Recetario</a>
                        <ul class="sub-menu children dropdown-menu">
                            <li><i class="menu-icon fa fa-medkit"></i><a href="http://localhost:3000/index.php?c=tratamiento">Tratamientos</a></li>
                            <li><i class="menu-icon fa fa-pills"></i><a href="http://localhost:3000/index.php?c=medicamento">Medicamentos</a></li>
                            <li><i class="menu-icon fa fa-file-medical"></i><a href="http://localhost:3000/index.php?c=receta">Receta</a></li>
                        </ul>
                    </li>

                    <li class="menu-title">Reportes</li><!-- /.menu-title -->
                    <li class="menu-item-has-children dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="menu-icon fa fa-bar-chart"></i>Reportes</a>
                        <ul class="sub-menu children dropdown-menu">
                            <li><i class="menu-icon fa fa-print"></i><a href="http://localhost:3000/index.php?c=examenlaboratorio&a=Reporte">Reporte de Exámenes</a></li>
                            <li><i class="menu-icon fa fa-print"></i><a href="http://localhost:3000/index.php?c=resultado&a=Reporte">Reporte de Resultados</a></li>
                            <li><i class="menu-icon fa fa-print"></i><a href="http://localhost:3000/index.php?c=consulta&a=Reporte">Reporte de Consultas</a></li>
                        </ul>
                    </li>
                    <li class="menu-title">Extras</li><!-- /.menu-title -->
                    <li class="menu-item-has-children dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="menu-icon fa fa-glass"></i>Pages</a>
                        <ul class="sub-menu children dropdown-menu">
                            <li><i class="menu-icon fa fa-sign-in"></i><a href="logout.php">Login</a></li>
                            <li><i class="menu-icon fa fa-sign-in"></i><a href="page-register.html">Register</a></li>
                            <li><i class="menu-icon fa fa-paper-plane"></i><a href="pages-forget.html">Forget Pass</a></li>
                        </ul>
                    </li>
                </ul>
            </div><!-- /.navbar-collapse -->
        </nav>
    </aside>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'acceso_denegado') : ?>
        <div class="alert alert-danger" style="margin:15px; text-align:center;">
            <i class="fa fa-lock"></i> Tu rol (<strong><?php echo htmlspecialchars(current_user_role()); ?></strong>) no tiene permiso para acceder a ese módulo.
        </div>
    <?php endif; ?>