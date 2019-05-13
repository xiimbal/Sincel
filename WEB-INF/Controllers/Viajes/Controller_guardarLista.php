<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../../Classes/Catalogo.class.php");

$catalogo = new Catalogo();
$usuario = $_SESSION['user'];

$c=0;
$comen="";
$lista = $_POST['lista'];
$fecha = $_POST['fecha'];
$idCampania = $_POST['idCampania'];
$idTurno = $_POST['idTurno'];
$errores = false;
//print_r($_POST); //Ver que se resive por POST
$consulta = "INSERT INTO c_plantilla(idCampania, idTurno, idTicket, TipoEvento, Fecha, Hora, Estatus, Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla, IdUsuarioAutorizacion)
            VALUES('" . $idCampania . "','" . $idTurno . "',NULL,'0','" . $fecha . "','00:00:00',1,'1','" . $usuario . "',now(),'" . $usuario . "',now(),'Carga Excel',NULL);";

$idPlantilla = $catalogo->insertarRegistro($consulta);
if ($idPlantilla != NULL && $idPlantilla != 0) {
    echo "Se ha registrado con éxito la Plantilla ".$idPlantilla.".";

    foreach ($lista as $asistencia) {
        $idUsuario = $asistencia[0];
        $asistencia = $asistencia[1];
        $comen = $lista[$c][2];
        $c++;
        $query = $catalogo->obtenerLista("SELECT CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS Nombre FROM c_usuario AS cu WHERE cu.IdUsuario='" . $idUsuario . "'");
        $rs = mysql_fetch_array($query);
        $Nombre_Usuario = $rs['Nombre']; //Nombre para mensajes echo

        $consulta = "INSERT INTO k_plantilla(idPlantilla,idUsuario,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                    VALUES ('" . $idPlantilla . "','" . $idUsuario . "','1','" . $usuario . "',now(),'" . $usuario . "',now(),'Carga Excel')";

        $idK_Plantilla = $catalogo->insertarRegistro($consulta);
        if ($idK_Plantilla != NULL && $idK_Plantilla != 0) {
            //echo "<br/>El Usuario '" . $Nombre_Usuario . "' se registro correctamente";

            $insertarAsistencia = "INSERT INTO k_plantilla_asistencia(idK_Plantilla, Asistencia, Comentario, Activo, UsuarioCreacion, 
                            FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) values 
                            ($idK_Plantilla, $asistencia, '$comen','1','$usuario', NOW(), '$usuario', NOW(), 'Carga Excel')";
            $result = $catalogo->insertarRegistro($insertarAsistencia);
            if (empty($result)) {
                $errores = true;
            }
          }else {
                    echo "br/>El Usuario '" . $Nombre_Usuario . "' NO se registró correctamente";
                }
        }
        if (!$errores) {
            echo " Se han registrado con éxito las asistencias";
        } else {
            echo "Ocurrió un error al insertar las solicitudes";
        }
    } else {
        echo "Ocurrió un error al insertar la Plantilla";
    }    