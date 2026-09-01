-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 31-08-2026 a las 20:02:15
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `db_santaclara`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_editar_cargo` (IN `p_codCargo` INT, IN `p_nombre` VARCHAR(50))   BEGIN
    UPDATE cargo 
    SET nombre = p_nombre 
    WHERE codCargo = p_codCargo;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_editar_condicion_fisica` (IN `p_idCondicion` INT, IN `p_peso` DECIMAL(5,2), IN `p_estatura` DECIMAL(5,2), IN `p_temperatura` DECIMAL(4,1), IN `p_presionArterial` VARCHAR(20))   BEGIN
    UPDATE condicionFisica SET
        peso = p_peso,
        estatura = p_estatura,
        temperatura = p_temperatura,
        presionArterial = p_presionArterial
    WHERE idCondicion = p_idCondicion;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_editar_consulta` (IN `p_idConsulta` INT, IN `p_motivo` TEXT, IN `p_diagnostico` TEXT, IN `p_estadoConsulta` VARCHAR(30), IN `p_peso` DECIMAL(5,2), IN `p_estatura` DECIMAL(5,2), IN `p_temperatura` DECIMAL(4,1), IN `p_presionArterial` VARCHAR(20), OUT `p_codigoError` INT, OUT `p_mensaje` VARCHAR(255))   BEGIN

    DECLARE v_idCondicion INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_codigoError = 500;
        SET p_mensaje = 'Error al editar la consulta.';
    END;

    SET p_codigoError = 0;
    SET p_mensaje = '';

    IF NOT EXISTS(
        SELECT 1 FROM consulta
        WHERE idConsulta = p_idConsulta AND estado = 1
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='La consulta no existe o está inactiva';
    END IF;

    IF EXISTS(
        SELECT 1 FROM tratamiento
        WHERE idConsulta = p_idConsulta AND estado = 1
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='La consulta ya tiene un tratamiento registrado y no puede modificarse';
    END IF;

    IF EXISTS(
        SELECT 1 FROM examenLaboratorio
        WHERE idConsulta = p_idConsulta AND estado = 1
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='La consulta ya tiene exámenes registrados y no puede modificarse';
    END IF;

    IF TRIM(p_motivo)='' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='Debe ingresar el motivo de consulta';
    END IF;

    IF TRIM(p_diagnostico)='' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='Debe ingresar el diagnóstico';
    END IF;

    IF p_peso <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='Peso inválido';
    END IF;

    IF p_estatura <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='Estatura inválida';
    END IF;

    IF p_temperatura < 30 OR p_temperatura > 45 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='Temperatura fuera de rango';
    END IF;

    IF TRIM(p_presionArterial)='' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='Debe ingresar la presión arterial';
    END IF;

    IF p_estadoConsulta NOT IN ('ATENDIDA','CANCELADA','NO ASISTIO') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='Estado de consulta inválido';
    END IF;

    START TRANSACTION;

    SELECT idCondicion
    INTO v_idCondicion
    FROM consulta
    WHERE idConsulta = p_idConsulta;

    UPDATE condicionFisica
    SET
        peso = p_peso,
        estatura = p_estatura,
        temperatura = p_temperatura,
        presionArterial = p_presionArterial
    WHERE idCondicion = v_idCondicion;

    UPDATE consulta
    SET
        motivo = p_motivo,
        diagnostico = p_diagnostico,
        estadoConsulta = p_estadoConsulta
    WHERE idConsulta = p_idConsulta;

    COMMIT;

    SET p_codigoError = 0;
    SET p_mensaje = 'Consulta actualizada correctamente';

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_editar_detalle_receta` (IN `p_idTratamiento` INT, IN `p_idMedicamento` INT, IN `p_dosis` VARCHAR(50), IN `p_cantidad` INT, IN `p_frecuencia` TEXT, IN `p_viaAdministracion` VARCHAR(50), IN `p_duracion` VARCHAR(50))   BEGIN
    UPDATE detalleReceta SET
        dosis = p_dosis,
        cantidad = p_cantidad,
        frecuencia = p_frecuencia,
        viaAdministracion = p_viaAdministracion,
        duracion = p_duracion
    WHERE idTratamiento = p_idTratamiento 
      AND idMedicamento = p_idMedicamento;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_editar_especialidad` (IN `p_codEspecialidad` INT, IN `p_nombre` VARCHAR(50))   BEGIN
    UPDATE especialidad 
    SET nombre = p_nombre 
    WHERE codEspecialidad = p_codEspecialidad;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_editar_examen_laboratorio` (IN `p_idExamen` INT, IN `p_tipoExamen` VARCHAR(100), IN `p_fechaSolicitud` DATE)   BEGIN
    UPDATE examenLaboratorio SET
        tipoExamen = p_tipoExamen,
        fechaSolicitud = p_fechaSolicitud
    WHERE idExamen = p_idExamen;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_editar_medicamento` (IN `p_idMedicamento` INT, IN `p_nombre` VARCHAR(50), IN `p_observaciones` TEXT, IN `p_estado` TINYINT(1))   BEGIN
    UPDATE medicamento SET
        nombre = p_nombre,
        observaciones = p_observaciones,
        estado = p_estado
    WHERE idMedicamento = p_idMedicamento;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_editar_paciente` (IN `p_ciPaciente` INT, IN `p_codigoPaciente` INT, IN `p_nombre` VARCHAR(50), IN `p_apaterno` VARCHAR(50), IN `p_apmaterno` VARCHAR(50), IN `p_fechaNacimiento` DATE, IN `p_genero` VARCHAR(10), IN `p_telefono` VARCHAR(20), IN `p_email` VARCHAR(100), IN `p_direccion` VARCHAR(255), IN `p_seguroSalud` VARCHAR(50))   BEGIN
    IF p_fechaNacimiento >= CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error: La fecha de nacimiento debe ser menor a la fecha actual.';
    END IF;
    UPDATE paciente SET
        codigoPaciente = p_codigoPaciente,
        nombre = p_nombre,
        apaterno = p_apaterno,
        apmaterno = p_apmaterno,
        fechaNacimiento = p_fechaNacimiento,
        genero = p_genero,
        telefono = p_telefono,
        email = p_email,
        direccion = p_direccion,
        seguroSalud = p_seguroSalud
    WHERE ciPaciente = p_ciPaciente;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_editar_personalSalud` (IN `p_ciPersonal` INT, IN `p_nombre` VARCHAR(50), IN `p_apaterno` VARCHAR(50), IN `p_apmaterno` VARCHAR(50), IN `p_fechaNacimiento` DATE, IN `p_genero` VARCHAR(10), IN `p_telefono` VARCHAR(20), IN `p_direccion` VARCHAR(250), IN `p_email` VARCHAR(50), IN `p_profesion` VARCHAR(50), IN `p_nacionalidad` VARCHAR(30), IN `p_tituloProfesional` VARCHAR(100), IN `p_anioTitulacion` INT, IN `p_universidad` VARCHAR(100), IN `p_tipoContrato` VARCHAR(30), IN `p_fechaIngreso` DATE, IN `p_fechaFinContrato` DATE, IN `p_afiliacionSeguro` VARCHAR(50), IN `p_nua` VARCHAR(50), IN `p_observaciones` TEXT, IN `p_foto` VARCHAR(255), IN `p_codCargo` INT, IN `p_codEspecialidad` INT)   BEGIN
    IF p_fechaNacimiento >= CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error: La fecha de nacimiento debe ser menor a la fecha actual.';
    END IF;
    UPDATE personalSalud SET
        nombre = p_nombre,
        apaterno = p_apaterno,
        apmaterno = p_apmaterno,
        fechaNacimiento = p_fechaNacimiento,
        genero = p_genero,
        telefono = p_telefono,
        direccion = p_direccion,
        email = p_email,
        profesion = p_profesion,
        nacionalidad = p_nacionalidad,
        tituloProfesional = p_tituloProfesional,
        anioTitulacion = p_anioTitulacion,
        universidad = p_universidad,
        tipoContrato = p_tipoContrato,
        fechaIngreso = p_fechaIngreso,
        fechaFinContrato = p_fechaFinContrato,
        afiliacionSeguro = p_afiliacionSeguro,
        nua = p_nua,
        observaciones = p_observaciones,
        foto = p_foto,
        codCargo = p_codCargo,
        codEspecialidad = p_codEspecialidad
    WHERE ciPersonal = p_ciPersonal;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_editar_resultado` (IN `p_idResultado` INT, IN `p_resultado` TEXT, IN `p_observaciones` VARCHAR(255), IN `p_documento` VARCHAR(255), IN `p_estadoExamen` VARCHAR(30))   BEGIN
    UPDATE resultado SET
        resultado = p_resultado,
        observaciones = p_observaciones,
        documento = p_documento,
        estadoExamen = p_estadoExamen
    WHERE idResultado = p_idResultado;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_editar_rol` (IN `p_codRol` INT, IN `p_nombre` VARCHAR(50))   BEGIN
    UPDATE rol 
    SET nombre = p_nombre 
    WHERE codRol = p_codRol;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_editar_tratamiento` (IN `p_idTratamiento` INT, IN `p_nombre` VARCHAR(100), IN `p_descripcion` TEXT)   BEGIN
    UPDATE tratamiento SET
        nombre = p_nombre,
        descripcion = p_descripcion
    WHERE idTratamiento = p_idTratamiento;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_editar_usuario` (IN `p_idUsuario` INT, IN `p_ciPersonal` INT, IN `p_login` VARCHAR(20), IN `p_password` VARCHAR(60), IN `p_nombreRol` VARCHAR(50))   BEGIN
    DECLARE v_codRol INT DEFAULT 0;
    DECLARE v_existe_usuario INT DEFAULT 0;
    
    SELECT COUNT(*) INTO v_existe_usuario FROM usuario WHERE idUsuario = p_idUsuario;
    
    IF v_existe_usuario = 0 THEN
        SELECT 0 AS idUsuario, 
               CONCAT('El usuario con ID "', p_idUsuario, '" no existe') AS mensaje, 
               'error' AS estado;
    ELSE
        SELECT codRol INTO v_codRol FROM rol WHERE nombre = p_nombreRol LIMIT 1;
        
        IF v_codRol > 0 THEN
            UPDATE usuario 
            SET ciPersonal = p_ciPersonal,
                login = p_login,
                password = p_password,
                codRol = v_codRol
            WHERE idUsuario = p_idUsuario;
            
            SELECT p_idUsuario AS idUsuario, 
                   CONCAT('Usuario "', p_login, '" actualizado exitosamente') AS mensaje, 
                   'success' AS estado;
        ELSE
            SELECT 0 AS idUsuario, 
                   CONCAT('El rol "', p_nombreRol, '" no existe') AS mensaje, 
                   'error' AS estado;
        END IF;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminar_cargo` (IN `p_codCargo` INT)   BEGIN
    UPDATE cargo SET estado = 0 WHERE codCargo = p_codCargo;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminar_condicion_fisica` (IN `p_idCondicion` INT)   BEGIN
    UPDATE condicionFisica SET estado = 0 WHERE idCondicion = p_idCondicion;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminar_consulta` (IN `p_idConsulta` INT, OUT `p_codigoError` INT, OUT `p_mensaje` VARCHAR(255))   BEGIN

    DECLARE v_idCondicion INT;
    DECLARE v_estadoConsulta VARCHAR(30);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_codigoError = 500;
        SET p_mensaje = 'Error al eliminar la consulta.';
    END;

    SET p_codigoError = 0;
    SET p_mensaje = '';

    IF NOT EXISTS(
        SELECT 1 FROM consulta
        WHERE idConsulta = p_idConsulta AND estado = 1
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La consulta no existe o ya fue eliminada.';
    END IF;

    SELECT idCondicion, estadoConsulta
    INTO v_idCondicion, v_estadoConsulta
    FROM consulta
    WHERE idConsulta = p_idConsulta;

    IF v_estadoConsulta = 'ATENDIDA' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No se puede eliminar una consulta ya atendida.';
    END IF;

    IF EXISTS(
        SELECT 1 FROM tratamiento
        WHERE idConsulta = p_idConsulta AND estado = 1
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La consulta tiene tratamientos registrados.';
    END IF;

    IF EXISTS(
        SELECT 1 FROM examenLaboratorio
        WHERE idConsulta = p_idConsulta AND estado = 1
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La consulta tiene exámenes registrados.';
    END IF;

    START TRANSACTION;

    UPDATE consulta SET estado = 0 WHERE idConsulta = p_idConsulta;
    UPDATE condicionFisica SET estado = 0 WHERE idCondicion = v_idCondicion;

    COMMIT;

    SET p_codigoError = 0;
    SET p_mensaje = 'Consulta eliminada correctamente.';

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminar_detalle_receta` (IN `p_idTratamiento` INT, IN `p_idMedicamento` INT)   BEGIN
    UPDATE detalleReceta SET estado = 0 
    WHERE idTratamiento = p_idTratamiento 
      AND idMedicamento = p_idMedicamento;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminar_especialidad` (IN `p_codEspecialidad` INT)   BEGIN
    UPDATE especialidad 
    SET estado = 0 
    WHERE codEspecialidad = p_codEspecialidad;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminar_examen_laboratorio` (IN `p_idExamen` INT)   BEGIN
    UPDATE examenLaboratorio 
    SET estado = 0
    WHERE idExamen = p_idExamen;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminar_medicamento` (IN `p_idMedicamento` INT)   BEGIN
    UPDATE medicamento
    SET estado = 0
    WHERE idMedicamento = p_idMedicamento;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminar_paciente` (IN `p_ciPaciente` INT)   BEGIN
    UPDATE paciente
    SET estado = 0
    WHERE ciPaciente = p_ciPaciente;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminar_personalSalud` (IN `p_ciPersonal` INT)   BEGIN
    UPDATE personalSalud
    SET estado = 0
    WHERE ciPersonal = p_ciPersonal;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminar_resultado` (IN `p_idResultado` INT)   BEGIN
    UPDATE resultado
    SET estado = 0
    WHERE idResultado = p_idResultado;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminar_rol` (IN `p_codRol` INT)   BEGIN
    UPDATE rol SET estado = 0 WHERE codRol = p_codRol;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminar_tratamiento` (IN `p_idTratamiento` INT)   BEGIN
    UPDATE tratamiento SET estado = 0 WHERE idTratamiento = p_idTratamiento;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminar_usuario` (IN `p_idUsuario` INT)   BEGIN
    UPDATE usuario
    SET estado = 0
    WHERE idUsuario = p_idUsuario;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertar_cargo` (IN `p_nombre` VARCHAR(50))   BEGIN
    INSERT INTO cargo (nombre) VALUES (p_nombre);
    SELECT LAST_INSERT_ID() AS idCargo;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertar_condicion_fisica` (IN `p_ciPaciente` INT, IN `p_peso` DECIMAL(5,2), IN `p_estatura` DECIMAL(5,2), IN `p_temperatura` DECIMAL(4,1), IN `p_presionArterial` VARCHAR(20))   BEGIN
    INSERT INTO condicionFisica (
        ciPaciente, peso, estatura, temperatura, presionArterial
    ) VALUES (
        p_ciPaciente, p_peso, p_estatura, p_temperatura, p_presionArterial
    );
    SELECT LAST_INSERT_ID() AS idCondicion;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertar_consulta` (IN `p_ciPaciente` INT, IN `p_ciPersonal` INT, IN `p_motivo` TEXT, IN `p_diagnostico` TEXT, IN `p_estadoConsulta` VARCHAR(30), IN `p_peso` DECIMAL(5,2), IN `p_estatura` DECIMAL(5,2), IN `p_temperatura` DECIMAL(4,1), IN `p_presionArterial` VARCHAR(20), IN `p_nombreTratamiento` VARCHAR(100), IN `p_descripcionTratamiento` TEXT, OUT `p_idConsulta` INT, OUT `p_codigoError` INT, OUT `p_mensaje` VARCHAR(255))   BEGIN

    DECLARE v_idCondicion INT;
    DECLARE v_codEspecialidad INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_codigoError = 500;
        SET p_mensaje = 'Error interno al registrar la consulta.';
        SET p_idConsulta = 0;
    END;

    SET p_codigoError = 0;
    SET p_mensaje = '';
    SET p_idConsulta = 0;

    IF NOT EXISTS(
        SELECT 1 FROM paciente
        WHERE ciPaciente = p_ciPaciente AND estado = 1
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='Paciente no encontrado o inactivo';
    END IF;

    IF NOT EXISTS(
        SELECT 1 FROM personalSalud
        WHERE ciPersonal = p_ciPersonal AND estado = 1
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='Personal de salud no encontrado';
    END IF;

    SELECT codEspecialidad
    INTO v_codEspecialidad
    FROM personalSalud
    WHERE ciPersonal = p_ciPersonal;

    IF TRIM(p_motivo)='' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='Debe ingresar el motivo de consulta';
    END IF;

    IF TRIM(p_diagnostico)='' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='Debe ingresar el diagnóstico';
    END IF;

    IF p_peso<=0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='Peso incorrecto';
    END IF;

    IF p_estatura<=0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='Estatura incorrecta';
    END IF;

    IF p_temperatura<30 OR p_temperatura>45 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='Temperatura fuera de rango';
    END IF;

    IF p_presionArterial IS NULL OR TRIM(p_presionArterial)='' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='Debe ingresar la presión arterial';
    END IF;

    IF p_estadoConsulta NOT IN ('ATENDIDA','CANCELADA','NO ASISTIO') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='Estado de consulta inválido';
    END IF;

    START TRANSACTION;

    INSERT INTO condicionFisica (
        ciPaciente, peso, estatura, temperatura, presionArterial
    ) VALUES (
        p_ciPaciente, p_peso, p_estatura, p_temperatura, p_presionArterial
    );

    SET v_idCondicion = LAST_INSERT_ID();

    INSERT INTO consulta (
        ciPaciente, ciPersonal, idCondicion, codEspecialidad,
        motivo, diagnostico, estadoConsulta
    ) VALUES (
        p_ciPaciente, p_ciPersonal, v_idCondicion, v_codEspecialidad,
        p_motivo, p_diagnostico, p_estadoConsulta
    );

    SET p_idConsulta = LAST_INSERT_ID();

    IF p_nombreTratamiento IS NOT NULL AND TRIM(p_nombreTratamiento)<>'' THEN
        INSERT INTO tratamiento (idConsulta, nombre, descripcion)
        VALUES (p_idConsulta, p_nombreTratamiento, p_descripcionTratamiento);
    END IF;

    COMMIT;

    SET p_mensaje='Consulta registrada correctamente';
    SET p_codigoError=0;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertar_detalle_receta` (IN `p_idTratamiento` INT, IN `p_idMedicamento` INT, IN `p_dosis` VARCHAR(50), IN `p_cantidad` INT, IN `p_frecuencia` TEXT, IN `p_viaAdministracion` VARCHAR(50), IN `p_duracion` VARCHAR(50))   BEGIN
    INSERT INTO detalleReceta (
        idTratamiento, idMedicamento, dosis, cantidad,
        frecuencia, viaAdministracion, duracion
    ) VALUES (
        p_idTratamiento, p_idMedicamento, p_dosis, p_cantidad,
        p_frecuencia, p_viaAdministracion, p_duracion
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertar_especialidad` (IN `p_nombre` VARCHAR(50))   BEGIN
    INSERT INTO especialidad (nombre) VALUES (p_nombre);
    SELECT LAST_INSERT_ID() AS idEspecialidad;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertar_examen_laboratorio` (IN `p_idConsulta` INT, IN `p_tipoExamen` VARCHAR(100), IN `p_fechaSolicitud` DATE)   BEGIN
    INSERT INTO examenLaboratorio (idConsulta, tipoExamen, fechaSolicitud) 
    VALUES (p_idConsulta, p_tipoExamen, p_fechaSolicitud);
    SELECT LAST_INSERT_ID() AS idExamen;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertar_medicamento` (IN `p_nombre` VARCHAR(50), IN `p_observaciones` TEXT)   BEGIN
    INSERT INTO medicamento (nombre, observaciones) 
    VALUES (p_nombre, p_observaciones);
    SELECT LAST_INSERT_ID() AS idMedicamento;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertar_paciente` (IN `p_ciPaciente` INT, IN `p_codigoPaciente` INT, IN `p_nombre` VARCHAR(50), IN `p_apaterno` VARCHAR(50), IN `p_apmaterno` VARCHAR(50), IN `p_fechaNacimiento` DATE, IN `p_genero` VARCHAR(10), IN `p_telefono` VARCHAR(20), IN `p_email` VARCHAR(100), IN `p_direccion` VARCHAR(255), IN `p_seguroSalud` VARCHAR(50))   BEGIN
    IF p_fechaNacimiento >= CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La fecha de nacimiento no puede ser hoy ni futura';
    END IF;

    INSERT INTO paciente (
        ciPaciente, codigoPaciente, nombre, apaterno, apmaterno,
        fechaNacimiento, genero, telefono, email, direccion, seguroSalud
    ) VALUES (
        p_ciPaciente, p_codigoPaciente, p_nombre, p_apaterno, p_apmaterno,
        p_fechaNacimiento, p_genero, p_telefono, p_email, p_direccion, p_seguroSalud
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertar_personalSalud` (IN `p_ciPersonal` INT, IN `p_nombre` VARCHAR(50), IN `p_apaterno` VARCHAR(50), IN `p_apmaterno` VARCHAR(50), IN `p_fechaNacimiento` DATE, IN `p_genero` VARCHAR(10), IN `p_telefono` VARCHAR(20), IN `p_direccion` VARCHAR(250), IN `p_email` VARCHAR(50), IN `p_profesion` VARCHAR(50), IN `p_nacionalidad` VARCHAR(30), IN `p_tituloProfesional` VARCHAR(100), IN `p_anioTitulacion` INT, IN `p_universidad` VARCHAR(100), IN `p_tipoContrato` VARCHAR(30), IN `p_fechaIngreso` DATE, IN `p_fechaFinContrato` DATE, IN `p_afiliacionSeguro` VARCHAR(50), IN `p_nua` VARCHAR(50), IN `p_observaciones` TEXT, IN `p_foto` VARCHAR(255), IN `p_codCargo` INT, IN `p_codEspecialidad` INT)   BEGIN

    IF p_fechaNacimiento >= CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La fecha de nacimiento no puede ser hoy ni futura';
    END IF;

    IF p_fechaFinContrato <= p_fechaIngreso THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La fecha fin debe ser mayor a la fecha de ingreso';
    END IF;

    INSERT INTO personalSalud (
        ciPersonal, nombre, apaterno, apmaterno, fechaNacimiento, genero,
        telefono, direccion, email, profesion, nacionalidad,
        tituloProfesional, anioTitulacion, universidad,
        tipoContrato, fechaIngreso, fechaFinContrato,
        afiliacionSeguro, nua, observaciones, foto,
        codCargo, codEspecialidad
    ) VALUES (
        p_ciPersonal, p_nombre, p_apaterno, p_apmaterno, p_fechaNacimiento,
        p_genero, p_telefono, p_direccion, p_email, p_profesion, p_nacionalidad,
        p_tituloProfesional, p_anioTitulacion, p_universidad, p_tipoContrato,
        p_fechaIngreso, p_fechaFinContrato, p_afiliacionSeguro, p_nua,
        p_observaciones, p_foto, p_codCargo, p_codEspecialidad
    );
    SELECT p_ciPersonal;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertar_resultado` (IN `p_idExamen` INT, IN `p_resultado` TEXT, IN `p_observaciones` VARCHAR(255), IN `p_documento` VARCHAR(255), IN `p_estadoExamen` VARCHAR(30))   BEGIN
    INSERT INTO resultado (
        idExamen, resultado, observaciones, documento, estadoExamen
    ) VALUES (
        p_idExamen, p_resultado, p_observaciones, p_documento, p_estadoExamen
    );
    SELECT LAST_INSERT_ID() AS idResultado;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertar_rol` (IN `p_nombre` VARCHAR(50))   BEGIN
    INSERT INTO rol (nombre) VALUES (p_nombre);
    SELECT LAST_INSERT_ID() AS codRol;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertar_tratamiento` (IN `p_idConsulta` INT, IN `p_nombre` VARCHAR(100), IN `p_descripcion` TEXT)   BEGIN
    INSERT INTO tratamiento (idConsulta, nombre, descripcion) 
    VALUES (p_idConsulta, p_nombre, p_descripcion);
    SELECT LAST_INSERT_ID() AS idTratamiento;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertar_usuario` (IN `p_ciPersonal` INT, IN `p_login` VARCHAR(20), IN `p_password` VARCHAR(60), IN `p_nombreRol` VARCHAR(50))   BEGIN
    DECLARE v_codRol INT DEFAULT 0;
    
    SELECT codRol INTO v_codRol FROM rol WHERE nombre = p_nombreRol LIMIT 1;
    
    IF v_codRol > 0 THEN
        INSERT INTO usuario (ciPersonal, login, password, codRol) 
        VALUES (p_ciPersonal, p_login, p_password, v_codRol);
        
        SELECT LAST_INSERT_ID() AS idUsuario, 
               'Usuario creado exitosamente' AS mensaje, 
               'success' AS estado;
    ELSE
        SELECT 0 AS idUsuario, 
               CONCAT('El rol "', p_nombreRol, '" no existe') AS mensaje, 
               'error' AS estado;
    END IF;
END$$

--
-- Funciones
--
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_imc` (`p_peso` DECIMAL(5,2), `p_estatura` DECIMAL(5,2)) RETURNS DECIMAL(5,2) DETERMINISTIC BEGIN
    DECLARE v_imc DECIMAL(5,2);

    SET v_imc = p_peso / (p_estatura * p_estatura);

    RETURN v_imc;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargo`
--

CREATE TABLE `cargo` (
  `codCargo` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `fechaRegistro` timestamp NULL DEFAULT current_timestamp(),
  `fechaEdicion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cargo`
--

INSERT INTO `cargo` (`codCargo`, `nombre`, `fechaRegistro`, `fechaEdicion`, `estado`) VALUES
(1, 'Médico Jefe', '2026-08-27 04:15:20', '2026-08-27 04:15:20', 1),
(2, 'Médico Especialista', '2026-08-27 04:15:20', '2026-08-27 04:15:20', 1),
(3, 'Médico General', '2026-08-27 04:15:20', '2026-08-27 04:15:20', 1),
(4, 'Enfermero Jefe', '2026-08-27 04:15:20', '2026-08-27 04:15:20', 1),
(5, 'Enfermero', '2026-08-27 04:15:20', '2026-08-27 04:15:20', 1),
(6, 'Administrativo', '2026-08-27 04:15:20', '2026-08-27 04:15:20', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `condicionfisica`
--

CREATE TABLE `condicionfisica` (
  `idCondicion` int(11) NOT NULL,
  `ciPaciente` int(11) NOT NULL,
  `peso` decimal(5,2) NOT NULL,
  `estatura` decimal(5,2) NOT NULL,
  `temperatura` decimal(4,1) NOT NULL,
  `presionArterial` varchar(20) NOT NULL,
  `fechaEdicion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fechaRegistro` timestamp NULL DEFAULT current_timestamp(),
  `estado` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `condicionfisica`
--

INSERT INTO `condicionfisica` (`idCondicion`, `ciPaciente`, `peso`, `estatura`, `temperatura`, `presionArterial`, `fechaEdicion`, `fechaRegistro`, `estado`) VALUES
(1, 2001, 78.50, 1.75, 36.6, '120/80', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(2, 2002, 62.30, 1.62, 36.8, '110/70', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(3, 2003, 70.00, 1.68, 37.1, '130/85', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(4, 2004, 55.40, 1.58, 36.5, '115/75', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(5, 2005, 82.10, 1.70, 38.2, '140/90', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(6, 2004, 67.00, 1.76, 38.0, '140/90', '2026-08-30 22:19:53', '2026-08-30 22:19:53', 1),
(7, 2001, 59.00, 1.78, 38.0, '120/80', '2026-08-30 22:24:54', '2026-08-30 22:24:54', 1),
(8, 9581948, 69.00, 155.00, 37.0, '120/80', '2026-08-31 03:53:25', '2026-08-31 03:52:27', 0),
(9, 9581948, 69.00, 1.55, 37.0, '120/80', '2026-08-31 03:53:06', '2026-08-31 03:53:06', 1),
(10, 16894033, 25.00, 97.00, 38.0, '120/80', '2026-08-31 11:26:00', '2026-08-31 11:26:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consulta`
--

CREATE TABLE `consulta` (
  `idConsulta` int(11) NOT NULL,
  `ciPaciente` int(11) NOT NULL,
  `ciPersonal` int(11) NOT NULL,
  `idCondicion` int(11) NOT NULL,
  `codEspecialidad` int(11) NOT NULL,
  `motivo` text NOT NULL,
  `diagnostico` text NOT NULL,
  `estadoConsulta` varchar(30) NOT NULL,
  `fechaEdicion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fechaRegistro` timestamp NULL DEFAULT current_timestamp(),
  `estado` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `consulta`
--

INSERT INTO `consulta` (`idConsulta`, `ciPaciente`, `ciPersonal`, `idCondicion`, `codEspecialidad`, `motivo`, `diagnostico`, `estadoConsulta`, `fechaEdicion`, `fechaRegistro`, `estado`) VALUES
(1, 2001, 1001, 1, 2, 'Dolor en el pecho y palpitaciones', 'Arritmia leve, se recomienda control periódico', 'ATENDIDA', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(2, 2002, 1004, 2, 4, 'Control ginecológico de rutina', 'Sin hallazgos anormales', 'ATENDIDA', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(3, 2003, 1003, 3, 5, 'Dolor en rodilla derecha tras caída', 'Esguince de rodilla grado I', 'ATENDIDA', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(4, 2004, 1002, 4, 3, 'Fiebre y tos persistente', 'Infección respiratoria viral', 'ATENDIDA', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(5, 2005, 1005, 5, 1, 'Fiebre alta y malestar general', 'Cuadro febril en observación, revision cada 7 dias', 'NO ASISTIO', '2026-08-28 16:09:27', '2026-08-27 19:35:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detallereceta`
--

CREATE TABLE `detallereceta` (
  `idTratamiento` int(11) NOT NULL,
  `idMedicamento` int(11) NOT NULL,
  `dosis` varchar(50) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `frecuencia` text NOT NULL,
  `viaAdministracion` varchar(50) NOT NULL,
  `duracion` varchar(50) NOT NULL,
  `fechaEdicion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fechaRegistro` timestamp NULL DEFAULT current_timestamp(),
  `estado` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detallereceta`
--

INSERT INTO `detallereceta` (`idTratamiento`, `idMedicamento`, `dosis`, `cantidad`, `frecuencia`, `viaAdministracion`, `duracion`, `fechaEdicion`, `fechaRegistro`, `estado`) VALUES
(4, 1, '500mg', 15, 'Cada 8 horas', 'Oral', '5 días', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1),
(3, 2, '400mg', 20, 'Cada 8 horas', 'Oral', '7 días', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1),
(1, 4, '50mg', 30, 'Cada 24 horas', 'Oral', '30 días', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1),
(4, 5, '10mg', 10, 'Cada 24 horas', 'Oral', '10 días', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidad`
--

CREATE TABLE `especialidad` (
  `codEspecialidad` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `fechaRegistro` timestamp NULL DEFAULT current_timestamp(),
  `fechaEdicion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `especialidad`
--

INSERT INTO `especialidad` (`codEspecialidad`, `nombre`, `fechaRegistro`, `fechaEdicion`, `estado`) VALUES
(1, 'Medicina General', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(2, 'Cardiología', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(3, 'Pediatría', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(4, 'Ginecología', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(5, 'Traumatología', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(6, 'Cuidados Intensivos', '2026-08-27 19:35:00', '2026-08-27 19:42:38', 0),
(7, 'Odontologia', '2026-08-27 19:44:00', '2026-08-27 19:44:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examenlaboratorio`
--

CREATE TABLE `examenlaboratorio` (
  `idExamen` int(11) NOT NULL,
  `idConsulta` int(11) NOT NULL,
  `tipoExamen` varchar(100) NOT NULL,
  `fechaSolicitud` date NOT NULL,
  `fechaEdicion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fechaRegistro` timestamp NULL DEFAULT current_timestamp(),
  `estado` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `examenlaboratorio`
--

INSERT INTO `examenlaboratorio` (`idExamen`, `idConsulta`, `tipoExamen`, `fechaSolicitud`, `fechaEdicion`, `fechaRegistro`, `estado`) VALUES
(1, 1, 'Electrocardiograma', '2026-08-20', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1),
(2, 1, 'Perfil lipídico', '2026-08-20', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1),
(3, 4, 'Hemograma completo', '2026-08-22', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1),
(4, 3, 'homograma completo', '2026-08-31', '2026-08-31 03:39:33', '2026-08-31 03:39:33', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medicamento`
--

CREATE TABLE `medicamento` (
  `idMedicamento` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `observaciones` text NOT NULL,
  `fechaEdicion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fechaRegistro` timestamp NULL DEFAULT current_timestamp(),
  `estado` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `medicamento`
--

INSERT INTO `medicamento` (`idMedicamento`, `nombre`, `observaciones`, `fechaEdicion`, `fechaRegistro`, `estado`) VALUES
(1, 'Paracetamol 500mg', 'Analgésico y antipirético', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1),
(2, 'Ibuprofeno 400mg', 'Antiinflamatorio no esteroideo', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1),
(3, 'Amoxicilina 500mg', 'Antibiótico de amplio espectro', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1),
(4, 'Losartán 50mg', 'Antihipertensivo', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1),
(5, 'Loratadina 10mg', 'Antihistamínico', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paciente`
--

CREATE TABLE `paciente` (
  `ciPaciente` int(11) NOT NULL,
  `codigoPaciente` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apaterno` varchar(50) NOT NULL,
  `apmaterno` varchar(50) DEFAULT NULL,
  `fechaNacimiento` date DEFAULT NULL,
  `genero` varchar(10) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) NOT NULL,
  `seguroSalud` varchar(50) NOT NULL,
  `fechaEdicion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fechaRegistro` timestamp NULL DEFAULT current_timestamp(),
  `estado` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `paciente`
--

INSERT INTO `paciente` (`ciPaciente`, `codigoPaciente`, `nombre`, `apaterno`, `apmaterno`, `fechaNacimiento`, `genero`, `telefono`, `email`, `direccion`, `seguroSalud`, `fechaEdicion`, `fechaRegistro`, `estado`) VALUES
(2001, 5465, 'Louis', 'Stanley', 'Gómez', '1988-06-10', 'Masculino', '70011122', 'lstanley@mail.com', 'Av. Circunvalación #45', 'Ninguno', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(2002, 5466, 'Mary', 'Silva', 'Torres', '1995-01-22', 'Femenino', '70022233', 'msilva@mail.com', 'Calle Junín #78', 'CNS', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(2003, 5467, 'Catherine', 'Dixon', 'Ríos', '1979-09-03', 'Femenino', '70033344', 'cdixon@mail.com', 'Av. Villazón #12', 'Seguro Universitario', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(2004, 5468, 'Gregory', 'Dixon', 'Vargas', '2001-11-17', 'Masculino', '70044455', 'gdixon@mail.com', 'Calle Ayacucho #90', 'CNS', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(2005, 5469, 'Johnny', 'Stephens', 'Núñez', '1965-04-08', 'Masculino', '70055566', 'jstephens@mail.com', 'Av. Oquendo #200', 'Ninguno', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1),
(9581948, 1645, 'Adriana', 'Claure', 'Arias', '2000-09-08', 'FEMENINO', '67568976', 'Adriana@gmail.com', 'Distrito 128, Radial 17/2', 'SUS', '2026-08-31 03:43:58', '2026-08-31 03:43:58', 1),
(16237456, 1674, 'Zaireth', 'Figueroa', 'Claure', '2021-05-16', 'Masculino', '69112195', 'anamaria@gmail.com', 'Barrio Lindo', 'SUS', '2026-08-31 04:22:59', '2026-08-31 04:22:59', 1),
(16894033, 17834, 'Zaid', 'Figueroa', 'Claure', '2019-11-19', 'Masculino', '69112194', 'anamaria@gmail.com', 'Barrio Lindo', 'SUS', '2026-08-31 04:10:31', '2026-08-31 04:10:31', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personalsalud`
--

CREATE TABLE `personalsalud` (
  `ciPersonal` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apaterno` varchar(50) NOT NULL,
  `apmaterno` varchar(50) DEFAULT NULL,
  `fechaNacimiento` date DEFAULT NULL,
  `genero` varchar(10) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `direccion` varchar(250) NOT NULL,
  `email` varchar(50) NOT NULL,
  `profesion` varchar(50) NOT NULL,
  `nacionalidad` varchar(30) NOT NULL,
  `tituloProfesional` varchar(100) DEFAULT NULL,
  `anioTitulacion` int(11) NOT NULL,
  `universidad` varchar(100) DEFAULT NULL,
  `tipoContrato` varchar(30) NOT NULL,
  `fechaIngreso` date NOT NULL,
  `fechaFinContrato` date DEFAULT NULL,
  `afiliacionSeguro` varchar(50) NOT NULL,
  `nua` varchar(50) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `fechaRegistro` timestamp NULL DEFAULT current_timestamp(),
  `fechaEdicion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado` tinyint(4) DEFAULT 1,
  `codCargo` int(11) NOT NULL,
  `codEspecialidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personalsalud`
--

INSERT INTO `personalsalud` (`ciPersonal`, `nombre`, `apaterno`, `apmaterno`, `fechaNacimiento`, `genero`, `telefono`, `direccion`, `email`, `profesion`, `nacionalidad`, `tituloProfesional`, `anioTitulacion`, `universidad`, `tipoContrato`, `fechaIngreso`, `fechaFinContrato`, `afiliacionSeguro`, `nua`, `observaciones`, `foto`, `fechaRegistro`, `fechaEdicion`, `estado`, `codCargo`, `codEspecialidad`) VALUES
(1001, 'Juan', 'Pérez', 'Rojas', '1975-03-12', 'Masculino', '76512345', 'Av. Libertad #123, El Fortín', 'jperez@hospitalsantaclara.com', 'Médico Cirujano', 'Boliviana', 'Especialista en Cardiología', 2001, 'Universidad Mayor de San Simón', 'Planta', '2010-01-15', NULL, 'CNS', 'NUA-1001', 'Jefe del área de cardiología', 'admin.jpg', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1, 1, 2),
(1002, 'María', 'Fuentes', 'López', '1982-07-22', 'Femenino', '76523456', 'Calle Sucre #456, El Fortín', 'mfuentes@hospitalsantaclara.com', 'Médico Pediatra', 'Boliviana', 'Especialista en Pediatría', 2008, 'Universidad Católica Boliviana', 'Planta', '2012-03-01', NULL, 'CNS', 'NUA-1002', NULL, 'admin.jpg', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1, 2, 3),
(1003, 'Carlos', 'Salazar', 'Mendoza', '1978-11-05', 'Masculino', '76534567', 'Av. Panamericana #789', 'csalazar@hospitalsantaclara.com', 'Médico Traumatólogo', 'Boliviana', 'Especialista en Traumatología', 2005, 'UMSA', 'Planta', '2011-06-10', NULL, 'CNS', 'NUA-1003', NULL, 'admin.jpg', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1, 2, 5),
(1004, 'Ana', 'Chávez', 'Ortiz', '1985-02-18', 'Femenino', '76545678', 'Calle Bolívar #321', 'achavez@hospitalsantaclara.com', 'Médico Ginecólogo', 'Boliviana', 'Especialista en Ginecología', 2010, 'Universidad Mayor de San Simón', 'Planta', '2014-09-01', NULL, 'CNS', 'NUA-1004', NULL, 'admin.jpg', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1, 2, 4),
(1005, 'Roberto', 'Ramírez', 'Vega', '1980-05-30', 'MASCULINO', '76556789', 'Av. Blanco Galindo Km 5', 'rramirez@hospitalsantaclara.com', 'Médico General', 'Boliviana', 'Médico Cirujano General', 2007, 'UMSS', 'INDEFINIDO', '2013-01-20', NULL, 'CNS', 'NUA-1005', 'Excelente profesional', 'view/img/personalSalud_6a93a7e5cfd4a5.60670315.jpg', '2026-08-27 19:35:00', '2026-08-30 03:47:49', 1, 6, 1),
(1006, 'Lucía', 'Ortiz', 'Flores', '1990-09-14', 'Femenino', '76567890', 'Calle 6 de Agosto #100', 'lortiz@hospitalsantaclara.com', 'Enfermera', 'Boliviana', 'Licenciada en Enfermería', 2013, 'Universidad Católica Boliviana', 'Planta', '2015-04-11', NULL, 'CNS', 'NUA-1006', 'Jefa de enfermería', 'admin.jpg', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1, 4, 1),
(1007, 'Jack', 'Sanders', 'Miranda', '1992-12-01', 'Masculino', '76578901', 'Av. América #55', 'jsanders@hospitalsantaclara.com', 'Enfermero', 'Boliviana', 'Técnico Superior en Enfermería', 2015, 'Instituto Tecnológico Kolping', 'Contrato', '2017-02-15', NULL, 'CNS', 'NUA-1007', NULL, 'admin.jpg', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1, 5, 1),
(1008, 'Recepción', 'Gómez', 'Silva', '1995-04-25', 'Femenino', '76589012', 'Calle Nataniel Aguirre #22', 'recepcion@hospitalsantaclara.com', 'Auxiliar Administrativo', 'Boliviana', 'Técnico en Administración', 2018, 'Instituto Superior de Comercio', 'Contrato', '2019-05-01', NULL, 'CNS', 'NUA-1008', NULL, 'admin.jpg', '2026-08-27 19:35:00', '2026-08-27 19:35:00', 1, 6, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resultado`
--

CREATE TABLE `resultado` (
  `idResultado` int(11) NOT NULL,
  `idExamen` int(11) NOT NULL,
  `resultado` text NOT NULL,
  `observaciones` varchar(255) NOT NULL,
  `documento` varchar(255) DEFAULT NULL,
  `estadoExamen` varchar(30) NOT NULL DEFAULT 'Pendiente',
  `fechaEdicion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fechaRegistro` timestamp NULL DEFAULT current_timestamp(),
  `estado` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `resultado`
--

INSERT INTO `resultado` (`idResultado`, `idExamen`, `resultado`, `observaciones`, `documento`, `estadoExamen`, `fechaEdicion`, `fechaRegistro`, `estado`) VALUES
(1, 1, 'Ritmo sinusal con leve arritmia', 'Se recomienda control en 3 meses', NULL, 'Completado', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1),
(2, 2, 'Colesterol total: 210 mg/dL, LDL: 130 mg/dL', 'Ligeramente elevado, control dietético', NULL, 'Completado', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1),
(3, 3, 'Leucocitos: 11,200/mm3', 'Consistente con proceso infeccioso viral', NULL, 'Completado', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `codRol` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `fechaRegistro` timestamp NULL DEFAULT current_timestamp(),
  `fechaEdicion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`codRol`, `nombre`, `fechaRegistro`, `fechaEdicion`, `estado`) VALUES
(1, 'Administrador', '2026-08-27 04:15:20', '2026-08-27 04:15:20', 1),
(2, 'Médico', '2026-08-27 04:15:20', '2026-08-27 04:15:20', 1),
(3, 'Enfermero', '2026-08-27 04:15:20', '2026-08-27 04:15:20', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tratamiento`
--

CREATE TABLE `tratamiento` (
  `idTratamiento` int(11) NOT NULL,
  `idConsulta` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fechaEdicion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fechaRegistro` timestamp NULL DEFAULT current_timestamp(),
  `estado` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tratamiento`
--

INSERT INTO `tratamiento` (`idTratamiento`, `idConsulta`, `nombre`, `descripcion`, `fechaEdicion`, `fechaRegistro`, `estado`) VALUES
(1, 1, 'Control cardiológico', 'Seguimiento mensual con electrocardiograma', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1),
(2, 2, 'Control ginecológico anual', 'Próxima cita en 12 meses', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1),
(3, 3, 'Rehabilitación de rodilla', 'Fisioterapia 2 veces por semana durante 3 semanas', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1),
(4, 4, 'Tratamiento antigripal', 'Reposo, hidratación y medicación sintomática', '2026-08-27 19:35:01', '2026-08-27 19:35:01', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `idUsuario` int(11) NOT NULL,
  `ciPersonal` int(11) NOT NULL,
  `login` varchar(20) NOT NULL,
  `password` varchar(60) NOT NULL,
  `intentosFallidos` int(11) NOT NULL DEFAULT 0,
  `bloqueadoHasta` datetime DEFAULT NULL,
  `codRol` int(11) NOT NULL,
  `estado` tinyint(4) DEFAULT 1,
  `fechaRegistro` timestamp NULL DEFAULT current_timestamp(),
  `fechaEdicion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ultimoAcceso` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idUsuario`, `ciPersonal`, `login`, `password`, `intentosFallidos`, `bloqueadoHasta`, `codRol`, `estado`, `fechaRegistro`, `fechaEdicion`, `ultimoAcceso`) VALUES
(1, 1001, 'jperez', '123456', 0, NULL, 2, 1, '2026-08-27 19:35:00', '2026-08-30 03:45:25', '2026-08-30 03:45:25'),
(2, 1002, 'mfuentes', '123456', 0, NULL, 2, 1, '2026-08-27 19:35:00', '2026-08-27 19:35:00', NULL),
(3, 1003, 'csalazar', '123456', 0, NULL, 2, 1, '2026-08-27 19:35:00', '2026-08-27 19:35:00', NULL),
(4, 1004, 'achavez', '123456', 0, NULL, 2, 1, '2026-08-27 19:35:00', '2026-08-30 02:56:46', '2026-08-30 02:56:46'),
(5, 1005, 'rramirez', '123456', 0, NULL, 1, 1, '2026-08-27 19:35:00', '2026-08-31 11:06:41', '2026-08-31 11:06:41'),
(6, 1006, 'lortiz', '123456', 0, NULL, 3, 1, '2026-08-27 19:35:00', '2026-08-29 04:49:49', '2026-08-29 04:49:49'),
(7, 1007, 'jsanders', '123456', 0, NULL, 3, 1, '2026-08-27 19:35:00', '2026-08-30 02:55:14', '2026-08-30 02:55:14'),
(8, 1008, 'recepcion', '123456', 0, NULL, 1, 1, '2026-08-27 19:35:00', '2026-08-27 19:35:00', NULL);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vs_vista_cargos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vs_vista_cargos` (
`codCargo` int(11)
,`nombre` varchar(50)
,`fechaRegistro` timestamp
,`fechaEdicion` timestamp
,`estado` tinyint(4)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vs_vista_condicionfisicas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vs_vista_condicionfisicas` (
`idCondicion` int(11)
,`ciPaciente` int(11)
,`nombrePaciente` varchar(50)
,`apaternoPaciente` varchar(50)
,`apmaternoPaciente` varchar(50)
,`peso` decimal(5,2)
,`estatura` decimal(5,2)
,`temperatura` decimal(4,1)
,`presionArterial` varchar(20)
,`fechaRegistro` timestamp
,`estado` tinyint(4)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vs_vista_consultas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vs_vista_consultas` (
`idConsulta` int(11)
,`ciPaciente` int(11)
,`paciente` varchar(152)
,`edad` bigint(21)
,`genero` varchar(10)
,`ciPersonal` int(11)
,`medico` varchar(152)
,`especialidad` varchar(50)
,`idCondicion` int(11)
,`peso` decimal(5,2)
,`estatura` decimal(5,2)
,`imc` decimal(5,2)
,`estadoIMC` varchar(9)
,`temperatura` decimal(4,1)
,`presionArterial` varchar(20)
,`motivo` text
,`diagnostico` text
,`estadoConsulta` varchar(30)
,`fecha` varchar(10)
,`hora` varchar(10)
,`estado` tinyint(4)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vs_vista_especialidades`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vs_vista_especialidades` (
`codEspecialidad` int(11)
,`nombre` varchar(50)
,`fechaRegistro` timestamp
,`fechaEdicion` timestamp
,`estado` tinyint(4)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vs_vista_examenes`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vs_vista_examenes` (
`idExamen` int(11)
,`tipoExamen` varchar(100)
,`fechaSolicitud` date
,`pacienteNombre` varchar(50)
,`pacienteApaterno` varchar(50)
,`medicoNombre` varchar(50)
,`medicoApaterno` varchar(50)
,`resultado` text
,`observaciones` varchar(255)
,`estadoExamen` varchar(30)
,`fechaRegistro` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vs_vista_medicamentos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vs_vista_medicamentos` (
`idMedicamento` int(11)
,`nombre` varchar(50)
,`observaciones` text
,`fechaRegistro` timestamp
,`fechaEdicion` timestamp
,`estado` tinyint(4)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vs_vista_pacientes`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vs_vista_pacientes` (
`ciPaciente` int(11)
,`codigoPaciente` int(11)
,`nombre` varchar(50)
,`apaterno` varchar(50)
,`apmaterno` varchar(50)
,`fechaNacimiento` date
,`genero` varchar(10)
,`telefono` varchar(20)
,`email` varchar(100)
,`direccion` varchar(255)
,`seguroSalud` varchar(50)
,`fechaRegistro` timestamp
,`estado` tinyint(4)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vs_vista_personalsaluds`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vs_vista_personalsaluds` (
`ciPersonal` int(11)
,`nombre` varchar(50)
,`apaterno` varchar(50)
,`apmaterno` varchar(50)
,`fechaNacimiento` date
,`genero` varchar(10)
,`telefono` varchar(20)
,`direccion` varchar(250)
,`email` varchar(50)
,`profesion` varchar(50)
,`nacionalidad` varchar(30)
,`tituloProfesional` varchar(100)
,`anioTitulacion` int(11)
,`universidad` varchar(100)
,`tipoContrato` varchar(30)
,`fechaIngreso` date
,`fechaFinContrato` date
,`afiliacionSeguro` varchar(50)
,`nua` varchar(50)
,`observaciones` text
,`foto` varchar(255)
,`cargo` varchar(50)
,`especialidad` varchar(50)
,`fechaRegistro` timestamp
,`fechaEdicion` timestamp
,`estado` tinyint(4)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vs_vista_recetas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vs_vista_recetas` (
`idTratamiento` int(11)
,`tratamiento` varchar(100)
,`descripcion` text
,`idConsulta` int(11)
,`ciPaciente` int(11)
,`pacienteNombre` varchar(50)
,`pacienteApaterno` varchar(50)
,`idMedicamento` int(11)
,`medicamento` varchar(50)
,`dosis` varchar(50)
,`cantidad` int(11)
,`frecuencia` text
,`viaAdministracion` varchar(50)
,`duracion` varchar(50)
,`fechaRegistro` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vs_vista_roles`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vs_vista_roles` (
`codRol` int(11)
,`nombre` varchar(50)
,`fechaRegistro` timestamp
,`fechaEdicion` timestamp
,`estado` tinyint(4)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vs_vista_tratamientos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vs_vista_tratamientos` (
`idTratamiento` int(11)
,`tratamiento` varchar(100)
,`descripcion` text
,`idConsulta` int(11)
,`pacienteNombre` varchar(50)
,`pacienteApaterno` varchar(50)
,`medicamento` varchar(50)
,`dosis` varchar(50)
,`cantidad` int(11)
,`frecuencia` text
,`viaAdministracion` varchar(50)
,`duracion` varchar(50)
,`fechaRegistro` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vs_vista_usuarios`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vs_vista_usuarios` (
`idUsuario` int(11)
,`login` varchar(20)
,`estadoUsuario` tinyint(4)
,`rol` varchar(50)
,`ciPersonal` int(11)
,`nombrePersonal` varchar(50)
,`apaterno` varchar(50)
,`apmaterno` varchar(50)
,`email` varchar(50)
,`fechaRegistro` timestamp
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vs_vista_cargos`
--
DROP TABLE IF EXISTS `vs_vista_cargos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vs_vista_cargos`  AS SELECT `cargo`.`codCargo` AS `codCargo`, `cargo`.`nombre` AS `nombre`, `cargo`.`fechaRegistro` AS `fechaRegistro`, `cargo`.`fechaEdicion` AS `fechaEdicion`, `cargo`.`estado` AS `estado` FROM `cargo` WHERE `cargo`.`estado` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vs_vista_condicionfisicas`
--
DROP TABLE IF EXISTS `vs_vista_condicionfisicas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vs_vista_condicionfisicas`  AS SELECT `cf`.`idCondicion` AS `idCondicion`, `cf`.`ciPaciente` AS `ciPaciente`, `p`.`nombre` AS `nombrePaciente`, `p`.`apaterno` AS `apaternoPaciente`, `p`.`apmaterno` AS `apmaternoPaciente`, `cf`.`peso` AS `peso`, `cf`.`estatura` AS `estatura`, `cf`.`temperatura` AS `temperatura`, `cf`.`presionArterial` AS `presionArterial`, `cf`.`fechaRegistro` AS `fechaRegistro`, `cf`.`estado` AS `estado` FROM (`condicionfisica` `cf` join `paciente` `p` on(`cf`.`ciPaciente` = `p`.`ciPaciente`)) WHERE `cf`.`estado` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vs_vista_consultas`
--
DROP TABLE IF EXISTS `vs_vista_consultas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vs_vista_consultas`  AS SELECT `c`.`idConsulta` AS `idConsulta`, `p`.`ciPaciente` AS `ciPaciente`, concat(`p`.`nombre`,' ',`p`.`apaterno`,' ',ifnull(`p`.`apmaterno`,'')) AS `paciente`, timestampdiff(YEAR,`p`.`fechaNacimiento`,curdate()) AS `edad`, `p`.`genero` AS `genero`, `ps`.`ciPersonal` AS `ciPersonal`, concat(`ps`.`nombre`,' ',`ps`.`apaterno`,' ',ifnull(`ps`.`apmaterno`,'')) AS `medico`, `e`.`nombre` AS `especialidad`, `cf`.`idCondicion` AS `idCondicion`, `cf`.`peso` AS `peso`, `cf`.`estatura` AS `estatura`, `fn_imc`(`cf`.`peso`,`cf`.`estatura`) AS `imc`, CASE WHEN `fn_imc`(`cf`.`peso`,`cf`.`estatura`) < 18.5 THEN 'Bajo Peso' WHEN `fn_imc`(`cf`.`peso`,`cf`.`estatura`) < 25 THEN 'Normal' WHEN `fn_imc`(`cf`.`peso`,`cf`.`estatura`) < 30 THEN 'Sobrepeso' ELSE 'Obesidad' END AS `estadoIMC`, `cf`.`temperatura` AS `temperatura`, `cf`.`presionArterial` AS `presionArterial`, `c`.`motivo` AS `motivo`, `c`.`diagnostico` AS `diagnostico`, `c`.`estadoConsulta` AS `estadoConsulta`, date_format(`c`.`fechaRegistro`,'%d/%m/%Y') AS `fecha`, time_format(`c`.`fechaRegistro`,'%H:%i') AS `hora`, `c`.`estado` AS `estado` FROM ((((`consulta` `c` join `paciente` `p` on(`c`.`ciPaciente` = `p`.`ciPaciente`)) join `personalsalud` `ps` on(`c`.`ciPersonal` = `ps`.`ciPersonal`)) join `especialidad` `e` on(`c`.`codEspecialidad` = `e`.`codEspecialidad`)) join `condicionfisica` `cf` on(`c`.`idCondicion` = `cf`.`idCondicion`)) WHERE `c`.`estado` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vs_vista_especialidades`
--
DROP TABLE IF EXISTS `vs_vista_especialidades`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vs_vista_especialidades`  AS SELECT `especialidad`.`codEspecialidad` AS `codEspecialidad`, `especialidad`.`nombre` AS `nombre`, `especialidad`.`fechaRegistro` AS `fechaRegistro`, `especialidad`.`fechaEdicion` AS `fechaEdicion`, `especialidad`.`estado` AS `estado` FROM `especialidad` WHERE `especialidad`.`estado` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vs_vista_examenes`
--
DROP TABLE IF EXISTS `vs_vista_examenes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vs_vista_examenes`  AS SELECT `el`.`idExamen` AS `idExamen`, `el`.`tipoExamen` AS `tipoExamen`, `el`.`fechaSolicitud` AS `fechaSolicitud`, `p`.`nombre` AS `pacienteNombre`, `p`.`apaterno` AS `pacienteApaterno`, `ps`.`nombre` AS `medicoNombre`, `ps`.`apaterno` AS `medicoApaterno`, `r`.`resultado` AS `resultado`, `r`.`observaciones` AS `observaciones`, `r`.`estadoExamen` AS `estadoExamen`, `el`.`fechaRegistro` AS `fechaRegistro` FROM ((((`examenlaboratorio` `el` join `consulta` `c` on(`el`.`idConsulta` = `c`.`idConsulta`)) join `paciente` `p` on(`c`.`ciPaciente` = `p`.`ciPaciente`)) join `personalsalud` `ps` on(`c`.`ciPersonal` = `ps`.`ciPersonal`)) left join `resultado` `r` on(`el`.`idExamen` = `r`.`idExamen`)) WHERE `el`.`estado` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vs_vista_medicamentos`
--
DROP TABLE IF EXISTS `vs_vista_medicamentos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vs_vista_medicamentos`  AS SELECT `medicamento`.`idMedicamento` AS `idMedicamento`, `medicamento`.`nombre` AS `nombre`, `medicamento`.`observaciones` AS `observaciones`, `medicamento`.`fechaRegistro` AS `fechaRegistro`, `medicamento`.`fechaEdicion` AS `fechaEdicion`, `medicamento`.`estado` AS `estado` FROM `medicamento` WHERE `medicamento`.`estado` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vs_vista_pacientes`
--
DROP TABLE IF EXISTS `vs_vista_pacientes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vs_vista_pacientes`  AS SELECT `paciente`.`ciPaciente` AS `ciPaciente`, `paciente`.`codigoPaciente` AS `codigoPaciente`, `paciente`.`nombre` AS `nombre`, `paciente`.`apaterno` AS `apaterno`, `paciente`.`apmaterno` AS `apmaterno`, `paciente`.`fechaNacimiento` AS `fechaNacimiento`, `paciente`.`genero` AS `genero`, `paciente`.`telefono` AS `telefono`, `paciente`.`email` AS `email`, `paciente`.`direccion` AS `direccion`, `paciente`.`seguroSalud` AS `seguroSalud`, `paciente`.`fechaRegistro` AS `fechaRegistro`, `paciente`.`estado` AS `estado` FROM `paciente` WHERE `paciente`.`estado` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vs_vista_personalsaluds`
--
DROP TABLE IF EXISTS `vs_vista_personalsaluds`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vs_vista_personalsaluds`  AS SELECT `personalsalud`.`ciPersonal` AS `ciPersonal`, `personalsalud`.`nombre` AS `nombre`, `personalsalud`.`apaterno` AS `apaterno`, `personalsalud`.`apmaterno` AS `apmaterno`, `personalsalud`.`fechaNacimiento` AS `fechaNacimiento`, `personalsalud`.`genero` AS `genero`, `personalsalud`.`telefono` AS `telefono`, `personalsalud`.`direccion` AS `direccion`, `personalsalud`.`email` AS `email`, `personalsalud`.`profesion` AS `profesion`, `personalsalud`.`nacionalidad` AS `nacionalidad`, `personalsalud`.`tituloProfesional` AS `tituloProfesional`, `personalsalud`.`anioTitulacion` AS `anioTitulacion`, `personalsalud`.`universidad` AS `universidad`, `personalsalud`.`tipoContrato` AS `tipoContrato`, `personalsalud`.`fechaIngreso` AS `fechaIngreso`, `personalsalud`.`fechaFinContrato` AS `fechaFinContrato`, `personalsalud`.`afiliacionSeguro` AS `afiliacionSeguro`, `personalsalud`.`nua` AS `nua`, `personalsalud`.`observaciones` AS `observaciones`, `personalsalud`.`foto` AS `foto`, `cargo`.`nombre` AS `cargo`, `especialidad`.`nombre` AS `especialidad`, `personalsalud`.`fechaRegistro` AS `fechaRegistro`, `personalsalud`.`fechaEdicion` AS `fechaEdicion`, `personalsalud`.`estado` AS `estado` FROM ((`personalsalud` join `cargo` on(`personalsalud`.`codCargo` = `cargo`.`codCargo`)) join `especialidad` on(`personalsalud`.`codEspecialidad` = `especialidad`.`codEspecialidad`)) WHERE `personalsalud`.`estado` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vs_vista_recetas`
--
DROP TABLE IF EXISTS `vs_vista_recetas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vs_vista_recetas`  AS SELECT `t`.`idTratamiento` AS `idTratamiento`, `t`.`nombre` AS `tratamiento`, `t`.`descripcion` AS `descripcion`, `c`.`idConsulta` AS `idConsulta`, `p`.`ciPaciente` AS `ciPaciente`, `p`.`nombre` AS `pacienteNombre`, `p`.`apaterno` AS `pacienteApaterno`, `dr`.`idMedicamento` AS `idMedicamento`, `m`.`nombre` AS `medicamento`, `dr`.`dosis` AS `dosis`, `dr`.`cantidad` AS `cantidad`, `dr`.`frecuencia` AS `frecuencia`, `dr`.`viaAdministracion` AS `viaAdministracion`, `dr`.`duracion` AS `duracion`, `t`.`fechaRegistro` AS `fechaRegistro` FROM ((((`tratamiento` `t` join `consulta` `c` on(`t`.`idConsulta` = `c`.`idConsulta`)) join `paciente` `p` on(`c`.`ciPaciente` = `p`.`ciPaciente`)) join `detallereceta` `dr` on(`t`.`idTratamiento` = `dr`.`idTratamiento` and `dr`.`estado` = 1)) join `medicamento` `m` on(`dr`.`idMedicamento` = `m`.`idMedicamento`)) WHERE `t`.`estado` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vs_vista_roles`
--
DROP TABLE IF EXISTS `vs_vista_roles`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vs_vista_roles`  AS SELECT `rol`.`codRol` AS `codRol`, `rol`.`nombre` AS `nombre`, `rol`.`fechaRegistro` AS `fechaRegistro`, `rol`.`fechaEdicion` AS `fechaEdicion`, `rol`.`estado` AS `estado` FROM `rol` WHERE `rol`.`estado` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vs_vista_tratamientos`
--
DROP TABLE IF EXISTS `vs_vista_tratamientos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vs_vista_tratamientos`  AS SELECT `t`.`idTratamiento` AS `idTratamiento`, `t`.`nombre` AS `tratamiento`, `t`.`descripcion` AS `descripcion`, `c`.`idConsulta` AS `idConsulta`, `p`.`nombre` AS `pacienteNombre`, `p`.`apaterno` AS `pacienteApaterno`, `m`.`nombre` AS `medicamento`, `dr`.`dosis` AS `dosis`, `dr`.`cantidad` AS `cantidad`, `dr`.`frecuencia` AS `frecuencia`, `dr`.`viaAdministracion` AS `viaAdministracion`, `dr`.`duracion` AS `duracion`, `t`.`fechaRegistro` AS `fechaRegistro` FROM ((((`tratamiento` `t` join `consulta` `c` on(`t`.`idConsulta` = `c`.`idConsulta`)) join `paciente` `p` on(`c`.`ciPaciente` = `p`.`ciPaciente`)) left join `detallereceta` `dr` on(`t`.`idTratamiento` = `dr`.`idTratamiento`)) left join `medicamento` `m` on(`dr`.`idMedicamento` = `m`.`idMedicamento`)) WHERE `t`.`estado` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vs_vista_usuarios`
--
DROP TABLE IF EXISTS `vs_vista_usuarios`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vs_vista_usuarios`  AS SELECT `u`.`idUsuario` AS `idUsuario`, `u`.`login` AS `login`, `u`.`estado` AS `estadoUsuario`, `r`.`nombre` AS `rol`, `p`.`ciPersonal` AS `ciPersonal`, `p`.`nombre` AS `nombrePersonal`, `p`.`apaterno` AS `apaterno`, `p`.`apmaterno` AS `apmaterno`, `p`.`email` AS `email`, `u`.`fechaRegistro` AS `fechaRegistro` FROM ((`usuario` `u` join `rol` `r` on(`u`.`codRol` = `r`.`codRol`)) join `personalsalud` `p` on(`u`.`ciPersonal` = `p`.`ciPersonal`)) WHERE `u`.`estado` = 1 ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cargo`
--
ALTER TABLE `cargo`
  ADD PRIMARY KEY (`codCargo`);

--
-- Indices de la tabla `condicionfisica`
--
ALTER TABLE `condicionfisica`
  ADD PRIMARY KEY (`idCondicion`),
  ADD KEY `fk_condicionFisica_paciente` (`ciPaciente`);

--
-- Indices de la tabla `consulta`
--
ALTER TABLE `consulta`
  ADD PRIMARY KEY (`idConsulta`),
  ADD KEY `fk_consulta_paciente` (`ciPaciente`),
  ADD KEY `fk_consulta_personalSalud` (`ciPersonal`),
  ADD KEY `fk_consulta_condicionFisica` (`idCondicion`),
  ADD KEY `fk_consulta_especialidad` (`codEspecialidad`),
  ADD KEY `idx_consulta_estadoConsulta` (`estadoConsulta`),
  ADD KEY `idx_consulta_fechaRegistro` (`fechaRegistro`),
  ADD KEY `idx_consulta_estado` (`estado`);

--
-- Indices de la tabla `detallereceta`
--
ALTER TABLE `detallereceta`
  ADD PRIMARY KEY (`idMedicamento`,`idTratamiento`),
  ADD KEY `fk_detalleReceta_tratamiento` (`idTratamiento`);

--
-- Indices de la tabla `especialidad`
--
ALTER TABLE `especialidad`
  ADD PRIMARY KEY (`codEspecialidad`);

--
-- Indices de la tabla `examenlaboratorio`
--
ALTER TABLE `examenlaboratorio`
  ADD PRIMARY KEY (`idExamen`),
  ADD KEY `fk_examenLaboratorio_consulta` (`idConsulta`),
  ADD KEY `idx_examen_fechaSolicitud` (`fechaSolicitud`),
  ADD KEY `idx_examen_estado` (`estado`);

--
-- Indices de la tabla `medicamento`
--
ALTER TABLE `medicamento`
  ADD PRIMARY KEY (`idMedicamento`);

--
-- Indices de la tabla `paciente`
--
ALTER TABLE `paciente`
  ADD PRIMARY KEY (`ciPaciente`),
  ADD UNIQUE KEY `codigoPaciente` (`codigoPaciente`);

--
-- Indices de la tabla `personalsalud`
--
ALTER TABLE `personalsalud`
  ADD PRIMARY KEY (`ciPersonal`),
  ADD KEY `fk_personalSalud_cargo` (`codCargo`),
  ADD KEY `fk_personalSalud_especialidad` (`codEspecialidad`);

--
-- Indices de la tabla `resultado`
--
ALTER TABLE `resultado`
  ADD PRIMARY KEY (`idResultado`),
  ADD KEY `fk_resultado_examenLaboratorio` (`idExamen`),
  ADD KEY `idx_resultado_estadoExamen` (`estadoExamen`),
  ADD KEY `idx_resultado_estado` (`estado`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`codRol`);

--
-- Indices de la tabla `tratamiento`
--
ALTER TABLE `tratamiento`
  ADD PRIMARY KEY (`idTratamiento`),
  ADD KEY `fk_tratamiento_consulta` (`idConsulta`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idUsuario`),
  ADD UNIQUE KEY `ciPersonal` (`ciPersonal`),
  ADD UNIQUE KEY `login` (`login`),
  ADD KEY `fk_usuario_rol` (`codRol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cargo`
--
ALTER TABLE `cargo`
  MODIFY `codCargo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `condicionfisica`
--
ALTER TABLE `condicionfisica`
  MODIFY `idCondicion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `consulta`
--
ALTER TABLE `consulta`
  MODIFY `idConsulta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `especialidad`
--
ALTER TABLE `especialidad`
  MODIFY `codEspecialidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `examenlaboratorio`
--
ALTER TABLE `examenlaboratorio`
  MODIFY `idExamen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `medicamento`
--
ALTER TABLE `medicamento`
  MODIFY `idMedicamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `resultado`
--
ALTER TABLE `resultado`
  MODIFY `idResultado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `codRol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tratamiento`
--
ALTER TABLE `tratamiento`
  MODIFY `idTratamiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `condicionfisica`
--
ALTER TABLE `condicionfisica`
  ADD CONSTRAINT `fk_condicionFisica_paciente` FOREIGN KEY (`ciPaciente`) REFERENCES `paciente` (`ciPaciente`);

--
-- Filtros para la tabla `consulta`
--
ALTER TABLE `consulta`
  ADD CONSTRAINT `fk_consulta_condicionFisica` FOREIGN KEY (`idCondicion`) REFERENCES `condicionfisica` (`idCondicion`),
  ADD CONSTRAINT `fk_consulta_especialidad` FOREIGN KEY (`codEspecialidad`) REFERENCES `especialidad` (`codEspecialidad`),
  ADD CONSTRAINT `fk_consulta_paciente` FOREIGN KEY (`ciPaciente`) REFERENCES `paciente` (`ciPaciente`),
  ADD CONSTRAINT `fk_consulta_personalSalud` FOREIGN KEY (`ciPersonal`) REFERENCES `personalsalud` (`ciPersonal`);

--
-- Filtros para la tabla `detallereceta`
--
ALTER TABLE `detallereceta`
  ADD CONSTRAINT `fk_detalleReceta_medicamento` FOREIGN KEY (`idMedicamento`) REFERENCES `medicamento` (`idMedicamento`),
  ADD CONSTRAINT `fk_detalleReceta_tratamiento` FOREIGN KEY (`idTratamiento`) REFERENCES `tratamiento` (`idTratamiento`);

--
-- Filtros para la tabla `examenlaboratorio`
--
ALTER TABLE `examenlaboratorio`
  ADD CONSTRAINT `fk_examenLaboratorio_consulta` FOREIGN KEY (`idConsulta`) REFERENCES `consulta` (`idConsulta`);

--
-- Filtros para la tabla `personalsalud`
--
ALTER TABLE `personalsalud`
  ADD CONSTRAINT `fk_personalSalud_cargo` FOREIGN KEY (`codCargo`) REFERENCES `cargo` (`codCargo`),
  ADD CONSTRAINT `fk_personalSalud_especialidad` FOREIGN KEY (`codEspecialidad`) REFERENCES `especialidad` (`codEspecialidad`);

--
-- Filtros para la tabla `resultado`
--
ALTER TABLE `resultado`
  ADD CONSTRAINT `fk_resultado_examenLaboratorio` FOREIGN KEY (`idExamen`) REFERENCES `examenlaboratorio` (`idExamen`);

--
-- Filtros para la tabla `tratamiento`
--
ALTER TABLE `tratamiento`
  ADD CONSTRAINT `fk_tratamiento_consulta` FOREIGN KEY (`idConsulta`) REFERENCES `consulta` (`idConsulta`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_personalSalud` FOREIGN KEY (`ciPersonal`) REFERENCES `personalsalud` (`ciPersonal`),
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`codRol`) REFERENCES `rol` (`codRol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
