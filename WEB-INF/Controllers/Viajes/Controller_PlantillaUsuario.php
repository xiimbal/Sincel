<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../../Classes/Conexion.class.php");
include_once("../../Classes/AutorizarPlantilla.class.php");
include_once("../../Classes/Catalogo.class.php");
//print_r($_GET);
$obj = new AutorizarPlantilla();
$catalogo = new Catalogo;
//print_r($_POST);
if (isset($_GET['idelim'])) {/* Para eliminar el registro con el id recibido por get */
    //print_r($_GET);
    $idKPA = $_GET['idelim'];
    $query = $catalogo->obtenerLista("SELECT idK_Plantilla AS idKP FROM k_plantilla_asistencia WHERE idK_Plantilla_asistencia='" . $idKPA . "';");
    $rs = mysql_fetch_array($query);
    $idKP = $rs['idKP'];
    $query = $catalogo->obtenerLista("SELECT idPlantilla AS idP, idUsuario FROM k_plantilla WHERE idK_Plantilla='" . $idKP . "';");
    $rs = mysql_fetch_array($query);
    $idP = $rs['idP'];
    $idUsuario = $rs['idUsuario'];

    $catalogo = new Catalogo();
    $query = $catalogo->obtenerLista("DELETE FROM k_plantilla_asistencia WHERE idK_Plantilla_asistencia = " . $idKPA . ";");
    if ($query == 1) {
        $query = $catalogo->obtenerLista("DELETE FROM k_plantilla WHERE idK_Plantilla = " . $idKP . ";");
        if ($query == 1) {
            $query = $catalogo->obtenerLista("DELETE FROM c_cambio_plantilla WHERE IdPlantilla = " . $idP . " AND IdUsuario = " . $idUsuario . ";");
            if ($query == 1) {
                echo "El usuario se eliminó correctamente";
            } else {

                echo "El usuario no se pudo eliminar, ya que contiene datos asociados.";
            }
        } else {

            echo "El usuario no se pudo eliminar, ya que contiene datos asociados.";
        }
    } else {

        echo "La usuario no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['IdPlantillaUsuarios']) && $_POST['IdPlantillaUsuarios'] > 0) {
        $idPlantilla = $_POST['IdPlantillaUsuarios'];
        $idCampania = $_POST['CampaniaUsuario'];
        $idTurno = $_POST['TurnoUsuario'];
        $idUsuario = $_POST['IdUsuarios'];

        $total = count($idUsuario);
        $obj->setUsuarioCreacion($_SESSION['user']);
        $obj->setUsuarioModificacion($_SESSION['user']);
        $obj->setPantalla('Actualziar Plantilla Agregar Usuario');
        $comentario = "Usuario Agregado";
        $Nombre_UsuarioRepetidos = array();
        $repetidos = 0;
        $error = 0;
        
        for ($i = 0; $i < $total; $i++) {
            $query = $catalogo->obtenerLista("SELECT Loggin, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS Nombre FROM c_usuario AS cu WHERE cu.IdUsuario='" . $idUsuario[$i] . "'");
            $rs = mysql_fetch_array($query);
            $Nombre_Usuario = $rs['Nombre']; //Nombre para mensajes echo

            $query = $catalogo->obtenerLista("SELECT * FROM k_plantilla WHERE IdUsuario='" . $idUsuario[$i] . "' AND IdPlantilla=" . $idPlantilla . ";");
            $rs = mysql_fetch_array($query);
            if (mysql_num_rows($query) > 0) {
                array_push($Nombre_UsuarioRepetidos, $Nombre_Usuario);
                $repetidos++;
            } else {

                $consulta = "INSERT INTO k_plantilla(idPlantilla,idUsuario,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                    VALUES ";
                $consulta .= "('" . $idPlantilla . "','" . $idUsuario[$i] . "',1,'" . $obj->getUsuarioCreacion() . "',now(),'" . $obj->getUsuarioModificacion() . "',now(),'" . $obj->getPantalla() . "')";

                $idK_Plantilla = $catalogo->insertarRegistro($consulta);
                if ($idK_Plantilla != NULL && $idK_Plantilla != 0) {
                    //echo "<br/>El Usuario '" . $Nombre_Usuario . "' se registro correctamente";

                    $consulta = "INSERT INTO k_plantilla_asistencia(idK_Plantilla,Asistencia,Prioridad,Comentario,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                                VALUES ";
                    $consulta .= "('" . $idK_Plantilla . "',1,0,'" . $comentario . "',1,'" . $obj->getUsuarioCreacion() . "',now(),'" . $obj->getUsuarioModificacion() . "',now(),'" . $obj->getPantalla() . "')";

                    $idK_Plantilla_asistencia = $catalogo->insertarRegistro($consulta);
                    if ($idK_Plantilla_asistencia != NULL && $idK_Plantilla_asistencia != 0) {

                        $consulta = "INSERT INTO c_cambio_plantilla(IdUsuario,IdPlantilla,UsuarioCreacion,FechaCreacion,Pantalla)
                                VALUES ";
                        $consulta .= "('" . $idUsuario[$i] . "','" . $idPlantilla . "','" . $obj->getUsuarioCreacion() . "',now(),'" . $obj->getPantalla() . "')";

                        $idCambioPlantilla = $catalogo->insertarRegistro($consulta);
                        if ($idCambioPlantilla != NULL && $idCambioPlantilla != 0) {
                            echo "";
                        } else {
                            $error++;
                        }
                        //echo "<br/>La asistencia, comentario '" . $i . "' del usuario '" . $Nombre_Usuario . "' se registraron correctamente";
                    } else {
                        $error++;
                        //echo "<br/>La asistencia, comentario '" . $i . "' del usuario '" . $Nombre_Usuario . "' NO se registraron correctamente";
                    }
                } else {
                    $error++;
                    //echo "br/>El Usuario '" . $Nombre_Usuario . "' NO se registró correctamente";
                }
                //$consulta .= ($i < $total - 1) ? "," : "";
            }
        }

        if ($error > 0) {
            echo "<br/>Ocurrio un problema al agragar usuarios";
        } else {
            if(($total-$repetidos) == 0){
                echo "";
            }else{
            echo "<br/>La Plantilla (" . $idPlantilla . ") se registró correctamente con " . ($total-$repetidos) . " Nuevo(s) Usuario(s)";
            }
            if(!empty($Nombre_UsuarioRepetidos)){
                echo "<br/>Usuario(s) <b>".implode(", ", $Nombre_UsuarioRepetidos)."</b> ya registrado(s) en ésta Plantilla";
            }
        }
    } else {
        echo "Usuarios No Agregados";
    }
}
?>