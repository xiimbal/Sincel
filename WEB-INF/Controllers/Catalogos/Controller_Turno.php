<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Turno.class.php");
if (isset($_POST['clave']) && $_POST['clave'] != "") {
    $turno = new Turno();
    if ($turno->deleteRegistro($_POST['clave'])) {
        echo "Se ha eliminado el turno exitosamente";
    } else {
        echo "Error: el turno  tiene valores dependientes";
    }
} else {
    $parametros = "";
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $tu = new Turno();
    $tu->setHoraEntrada($parametros['horaE'].":".$parametros['minutosE'].":00");
    $tu->setHoraSalida($parametros['horaS'].":".$parametros['minutosS'].":00");
    $tu->setDescripcion($parametros['descripcion']);
    if (isset($parametros['activo']) && $parametros['activo'] == "1") {
        $tu->setActivo($parametros['activo']);
    } else {
        $tu->setActivo(0);
    }
    $tu->setPantalla("PHP Controller_Turno");
    $tu->setUsuarioCreacion($_SESSION['user']);
    $tu->setUsuarioUltimaModificacion($_SESSION['user']);
    if (isset($parametros['id']) && $parametros['id'] != "") {
        $tu->setIdTurno($parametros['id']);
        if ($tu->editRegistro()) {
            echo "Se ha editado el turno exitosamente";
        } else {
            echo "Error: El turno no se logro editar";
        }
    } else {
        if ($tu->newRegistro()) {
            echo "Se ha registrado el nuevo turno";
        } else {
            echo "Error: El turno no se registro, verifique que la descripcion no se encuentre repetida";
        }
    }
}
?>



