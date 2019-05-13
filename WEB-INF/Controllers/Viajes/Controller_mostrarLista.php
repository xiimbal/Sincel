<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../../Classes/Catalogo.class.php");

$catalogo = new Catalogo();
$iniciarCeldas = false;
$filaValida = true;
$archivoError = false;
$tabla = ""; //Se guardaran todos los datos de la tabla
$rutaArchivo = "../../../viajes/listaAsistenciaPlantilla";
move_uploaded_file($_FILES['file']['tmp_name'], $rutaArchivo);
$cabecera = array("No. Empleado/Nombre", "Campaña", "Turno", "Asistencia", "Autoriza", "Comentario");

$handle = fopen($rutaArchivo, "r");
echo "<script type='text/javascript' language='javascript' src='resources/js/paginas/viajes/Controller_mostrarLista.js'></script>";
echo "<table id='mostrarLista'>";
echo "<form id='formLista'>";
echo "<thead>";
echo "<tr>";
for ($i = 0; $i < (count($cabecera)); $i++) {
    echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabecera[$i] . "</th>";
}
echo "</tr>";
echo "</thead>";
echo "<tbody>";
$i = 0;
$error = 0;
$p = 0;
$ca = 0;
$idUs = array();
$Campanias = array();
$Turnos = array();
while (!feof($handle)) {
    $data = fgetcsv($handle);
    $filaValida = true;
    if (empty($data[0]) && empty($data[1]) && empty($data[2]) && empty($data[3]) && empty($data[4])) {
        $filaValida = false;
    }
    if ($data[0] == "Nombre Completo" || $data[4] == "Fecha") {
        //Buscaremos en el excel las celdas de inicio de la asistencia
        $iniciarCeldas = true;
        continue;
    } else {
        if ($iniciarCeldas && $filaValida) {
            if (!isset($data[0]) || $data[0] == "" || !isset($data[1]) || $data[1] == "" || !isset($data[2]) || $data[2] == "" || !isset($data[3]) || $data[3] == "" || !isset($data[4])) {
                $archivoError = true;   //Si hace falta algún dato indicamos que hay error y terminamos el análisis del archivo
                break;
            }
            //Comenzaremos a obtener los datos para mostrarlos en la tabla
            echo "<tr>";
            $idUsuario = null;
            /* ------- Obtenemos el Id del usuario ------- */
            $nombreCompleto = $data[0];
            $nombre = explode(" ", utf8_encode($nombreCompleto));
            $where = "";
            $where = "WHERE c.nombre LIKE '%$nombre[0]%' ";
            if (isset($nombre[1]) && $nombre[1] != "") {
                $where .= "AND c.apellidoPaterno LIKE '%$nombre[1]%' ";
            }
            if (isset($nombre[2]) && $nombre[2] != "") {
                $where .= "AND c.apellidoMaterno LIKE '%$nombre[2]%' ";
            }
            $queryObtenerEmpleado = "SELECT IdUsuario FROM c_usuario AS c $where ORDER BY IdUsuario ASC LIMIT 1";
            $result = $catalogo->obtenerLista($queryObtenerEmpleado);
            if ($rs = mysql_fetch_array($result)) {
                $idUsuario = $rs['IdUsuario'];  //Obtenemos el Id del usuario
            }
            echo "<td>" . $idUsuario . " " . $nombreCompleto . "</td>";    //No. Empleado
            /* ------- Fin de la obtención del id del usuario -------- */
            $campana = utf8_encode($data[2]);
            echo "<td>$campana</td>"; //Campaña
            echo "<td>$data[1]</td>"; //Turno
            echo "<td>$data[3]</td>"; //Asistencia
            //Obtenemos el nombre de la persona que autoriza a través del turno y la asistencia
            $fecha = substr($data[4], 6) . "-" . substr($data[4], 3, 2) . "-" . substr($data[4], 0, 2);
            /* $queryCampana = "Select u.Nombre, u.ApellidoPaterno, d.IdDomicilio from c_domicilio_usturno d 
              LEFT JOIN c_usuario AS u ON p.IdUsuarioAutorizacion = u.IdUsuario
              LEFT JOIN c_turno AS t ON p.idTurno = t.IdTurno
              LEFT JOIN c_area AS a ON p.idCampania = a.IdArea
              WHERE
              t.descripcion LIKE '$data[1]' AND a.Descripcion LIKE '$campana'
              AND p.Fecha = '$fecha'";
              $result = $catalogo->obtenerLista($queryCampana);
              $nombreAutoriza = null;
              $idPlantilla = null;
              if($rs = mysql_fetch_array($result)){
              $nombreAutoriza = $rs['Nombre']." ".$rs['ApellidoPaterno'];  //Obtenemos el Id del usuario
              $idPlantilla = $rs['idPlantilla'];
              }
              echo "<td>$nombreAutoriza</td>"; //Autoriza */
            //Fin de la obtención del nombre del usuario que autoriza la campania
            //Verificar que corresponden el turno, campaña y usuario
            echo "<td>$idUsuario</td>";

            $comentario = $data[5];

            $queryv = "SELECT d.IdCampania AS idcampus, d.IdTurno AS idturus, a.IdArea AS idcampexc, t.idTurno AS idturexc FROM c_domicilio_usturno AS d,
                    c_area AS a, c_turno AS t WHERE d.IdUsuario=$idUsuario AND t.descripcion LIKE '$data[1]' AND a.Descripcion LIKE '$campana';";
            $result = $catalogo->obtenerLista($queryv);
            $idCampanaUs = null;
            $idTurnoUS = null;
            $idCampanaEx = null;
            $idTurnoEx = null;
            if (!(is_null($idUsuario))) {
                if ($rs = mysql_fetch_array($result)) {
                    $idCampanaUs = $rs['idcampus'];  //Obtenemos el Id de la Campanña
                    $idTurnoUS = $rs['idturus'];
                    $idCampanaEx = $rs['idcampexc'];
                    $idTurnoEx = $rs['idturexc'];

                    $igual = 0;
                    if (!empty($idUs)) {
                        for ($l = 0; $l < (count($idUs)); $l++) {
                            if ($idUs[$l] == $idUsuario) {
                                $igual++;
                            }
                        }
                    }
                    if ($igual == 0) {
                        $idUs[$p] = $idUsuario;
                        $p++;
                    } else {
                        $comentario = "<font color='red'> * El usuario " . $nombreCompleto . " se repite, verifique su Excel para registrar su asistencia</font>";
                        $error++;
                    }
                }
            }

            if (is_null($idUsuario)) {
                $comentario = "<font color='red'> * Verifique el nombre de usuario " . $nombreCompleto . " para registrar su asistencia </font>";
                $error++;
            } else if (is_null($idCampanaEx)) {
                $comentario = "<font color='red'> * Verifique el nombre de la campaña y turno para poder registrar su asistencia</font>";
                $error++;
            } else if ($idCampanaUs != $idCampanaEx) {
                $comentario = "<font color='red'> * Verifique el nombre de la campaña, NO corresponde al usuario</font>";
                $error++;
            } else if ($idTurnoUS != $idTurnoEx) {
                $comentario = "<font color='red'> * Verifique el turno, NO corresponde al usuario</font>";
                $error++;
            } else {
                /* ------------Verificamos que el empleado se encuentre dentro de la plantilla --------- */
                /* $queryVerificarUsuarioPlantilla = "SELECT * from k_plantilla WHERE idUsuario = $idUsuario
                  AND idPlantilla = $idPlantilla";
                  $result = $catalogo->obtenerLista($queryVerificarUsuarioPlantilla);
                  if($rs = mysql_fetch_array($result)){ */
                //Todos los datos son correctos
                $i++;
                $asistencia = 0;
                if ($data[3] == 'S' || $data[3] == 's') {
                    $asistencia = 1;
                }
                //$idKPlantilla = $rs['idK_Plantilla'];
                echo "<input type='hidden' name='idUsuario_$i' id='idUsuario_$i' value='$idUsuario' />";
                echo "<input type='hidden' name='asistencia_$i' id='asistencia_$i' value='$asistencia' />";
                echo "<input type='hidden' name='comen_$i' id='comen_$i' value='$data[5]' />";
                /* }else{
                  $comentario = "<font color='red'> * ".$data[5]. " El usuario no pertenece a está plantilla </font>";
                  } */
                /* ------------ Fin verificación  --------- */
            }
            echo "<td>$comentario</td>"; //Comentario
            echo "</tr>";

            if (!empty($Campanias)) {
                for ($c = 0; $c < count($Campanias); $c++) {
                    if ($Campanias[$c] != $idCampanaEx) {
                        echo "<font color='red'>Las campañas deben ser similares para todos los usuarios. </font>";
                        $archivoError = true;
                        continue 2;
                    }
                }
            }
            if (!empty($Turnos)) {
                for ($c = 0; $c < count($Turnos); $c++) {
                    if ($Turnos[$c] != $idTurnoEx) {
                        echo "<font color='red'>Los turnos deben ser los mismos para todos los usuarios. </font>";
                        $archivoError = true;
                        continue 2;
                    }
                }
            }
            $Campanias[$ca] = $idCampanaEx;
            $Turnos[$ca] = $idTurnoEx;
            $ca++;
        }
    }
}
echo "</tbody>";
echo "</table>";
echo "<br/>";
fclose($handle);

if ($archivoError) {
    echo "<font color='red'>Verifique su archivo en Excel</font>";
    echo "<br/><br/>";
} else {
    if ($error == 0) {
        echo "<input type='hidden' name='fecha' id='fecha' value='$fecha' />";
        echo "<input type='hidden' name='idCampania' id='idCampania' value='$idCampanaUs' />";
        echo "<input type='hidden' name='idTurno' id='idTurno' value='$idTurnoUS' />";
        echo "<input type='hidden' value='$i' name='exito' id='exito' />";
        echo "<input type='button' id='cargarBase' name='cargarBase' onclick='cargarLista()' value='Registrar' class='boton'/>";
    }
}
?>
<input type='button' id='cancelar' name='cancelar' onclick='cambiarContenidos("viajes/cargaListaAsistencia.php", "Carga Lista Asistencia");' value="Cancelar" class="boton"/>
<?php

echo "</form>";
?>