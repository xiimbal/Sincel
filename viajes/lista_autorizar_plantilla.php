<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/AutorizarPlantilla.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "viajes/lista_autorizar_plantilla.php";
$pagina_lista = "viajes/lista_actualizar_plantilla.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $pagina_lista);
$controler = "WEB-INF/Controllers/Viajes/Controller_PlantillaUsuario.php";
$cabeceras = array("Asistencia", "Nombre", "Domicilio", "Destino", "Comentario", "ss", "ss");


$idPlantilla = "";
$estatus = "";
$asistencia = "";
$idUsuario = "";
$idDomicilio = "";
$idArea = "";
$comentario = "";
$mostrar = 0;
$fecha = "";
$hora = "";
$tipoe = 0;
$activo = "checked='checked'";
$asiste = "0";
$where = "";
$postid = false;
$idpost = 0;
$getidactu = false;
$idgetactu = 0;
$disabled_asiscom = "";
$disabled_asiscom2 = "";
$disabled_camtur = "";
$notas = 0;
$agregarUsuarios = false;
$filtroCampania = "";
$filtroTurno = "";
$where2= "";

//print_r($_GET);
//print_r($_POST);

$filtroCampania2 = "";
if (isset($_POST['CampaniaFiltro2']) && $_POST['CampaniaFiltro2'] > 0) {
    $filtroCampania2 = $_POST['CampaniaFiltro2'];
    $where2 = " WHERE cd.IdCampania='$filtroCampania2' ";
}

$filtroTurno2 = "";
if (isset($_POST['TurnoFiltro2']) && $_POST['TurnoFiltro2'] > 0) {
    $filtroTurno2 = $_POST['TurnoFiltro2'];
    if ($where2 == "") {
        $where2 .= " WHERE cd.IdTurno='$filtroTurno2' ";
    } else {
        $where2 .= " AND cd.IdTurno='$filtroTurno2' ";
    }
}
if (isset($_POST['mostrar'])) {
    $mostrar = 1;
}
?>

<!DOCTYPE html>
<html lang = "es">
    <head>
        <?php
        if ((isset($_POST['idActualizarP']) && $_POST['idActualizarP'] != 0 && $_POST['idActualizarP'] != "")) {
            $_GET['idactu'] = $_POST['idActualizarP'];
        }
        if ((isset($_POST['id']) && $_POST['id'] != 0 && $_POST['id'] != "") || isset($_GET['idactu']) && $_GET['idactu'] != "") {

            if (isset($_GET['idactu']) && $_GET['idactu'] != "") {
                $autorizarp = new AutorizarPlantilla();
                $autorizarp->getRegistroById($_GET['idactu']);
                $estatus = $autorizarp->getEstatus();
            }
            if ($estatus == 1) {
                echo "<script type='text/javascript' language='javascript' src='resources/js/paginas/viajes/lista_autorizar_plantilla.js'></script>";
            } else {
                echo "<script type='text/javascript' language='javascript' src='resources/js/paginas/viajes/lista_actualizar_plantilla.js'></script>";
                $agregarUsuarios = true;
            }
        } else {
            echo "<script type='text/javascript' language='javascript' src='resources/js/paginas/viajes/lista_generar_plantilla.js'></script>";
        }
        
        ?>
    </head>
    <body>
        <div class="principal"> 
            <?php
            if (isset($_POST['id']) && $_POST['id'] != 0 && $_POST['id'] != "") {
                $autorizarp = new AutorizarPlantilla();
                $autorizarp->getRegistroById($_POST['id']);
                $idPlantilla = $autorizarp->getIdPlantilla();
                $filtroCampania = $autorizarp->getIdCampania();
                $filtroTurno = $autorizarp->getIdTurno();
                $fecha = $autorizarp->getFecha();
                $hora = $autorizarp->getHora();
                $tipoe = $autorizarp->getTipoEvento();
                $estatus = $autorizarp->getEstatus();
                $mostrar = 1;
                if ($autorizarp->getActivo() == "0") {
                    $activo = "";
                }
                $postid = true;
                $idpost = $idPlantilla;
                $disabled_camtur = 'disabled';
            } else {
                if (isset($_GET['idactu']) && $_GET['idactu'] != "") {
                    $getidactu = true;
                    $idgetactu = $_GET['idactu'];
                    $autorizarp = new AutorizarPlantilla();
                    $autorizarp->getRegistroById($_GET['idactu']);
                    $idPlantilla = $autorizarp->getIdPlantilla();
                    $filtroCampania = $autorizarp->getIdCampania();
                    $filtroTurno = $autorizarp->getIdTurno();
                    $fecha = $autorizarp->getFecha();
                    $hora = $autorizarp->getHora();
                    $tipoe = $autorizarp->getTipoEvento();
                    $estatus = $autorizarp->getEstatus();
                    $mostrar = 1;
                    if ($autorizarp->getActivo() == "0") {
                        $activo = "";
                    }
                    $disabled_camtur = 'disabled';
                }
            }
            ?>


            <br/><br/> 
            <form id="formAutoPlantilla" name="formAutoPlantilla" action="/" method="POST"> 
                <table style="width: 95%;">
                    <tr>
                        <td>Campaña</td>
                        <td>
                            <select id="CampaniaFiltro" name="CampaniaFiltro" <?php echo $disabled_camtur; ?>>
                                <option value="0">Seleccione una Campaña</option>
                                <?php
                                $catalogo = new Catalogo();
                                $queryCampania = $catalogo->getListaAlta("c_area", "Descripcion");
                                while ($rs = mysql_fetch_array($queryCampania)) {
                                    $s = "";
                                    if ($filtroCampania != "" && $filtroCampania == $rs['IdArea']) {
                                        $s = "selected";
                                    }
                                    if (($rs['ClaveCentroCosto']) != NULL || ($rs['ClaveCentroCosto']) != "") {
                                        echo "<option value='" . $rs['IdArea'] . "' $s>" . $rs['Descripcion'] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td>Turno</td>
                        <td>
                            <select id="TurnoFiltro" name="TurnoFiltro" <?php echo $disabled_camtur; ?>>
                                <option value="0">Seleccione un Turno</option>
                                <?php
                                $catalogo = new Catalogo();
                                $queryTurno = $catalogo->getListaAlta("c_turno", "descripcion");
                                while ($rs = mysql_fetch_array($queryTurno)) {
                                    $s = "";
                                    if ($filtroTurno != "" && $filtroTurno == $rs['idTurno'])
                                        $s = "selected";
                                    echo "<option value='" . $rs['idTurno'] . "' $s>" . $rs['descripcion'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td>

                            <?php
                            if (( $postid && $idPlantilla != "") || ($getidactu && $idPlantilla != "")) {
                                echo "";
                            } else {
                                ?>
                                <?php /* <input type="button" id="mostrar_componentes" name="mostrar_componentes" value="Generar Plantilla" class="button" 
                                  onclick="mostrarCampaniaTurno('<?php echo $same_page ?>', 'CampaniaFiltro', 'TurnoFiltro');"/> */ ?>

                                <input type="submit" name="submit" class="boton" value="<?php echo "Generar Plantilla"; ?>" />  
                                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                                            return false;"/>
                                <input type='hidden' id='txtfecha' name='txtfecha' value='0000-00-00'/> 
                                <input type='hidden' id='hora' name='hora' value='00'/> 
                                <input type='hidden' id='minutos' name='minutos' value='00'/> 
                                <input type='hidden' id='tipo_evento' name='tipo_evento' value='0'/> 
                                <input type='hidden' id='activo' name='activo' value='on'/> 
                                <?php
                                echo "<input type='hidden' id='idPlantilla' name='idPlantilla' value='" . $idPlantilla . "'/> ";
                                echo "<input type='hidden' id='newPlantilla' name='newPlantilla' value='1'/> ";
                            }
                            ?>
                        </td>
                    </tr> 
                </table>
                <?php if ($mostrar == 1) { ?>
                    <br/>
                    <table style="width: 95%;">
                        <tr>
                            <?php
                            if ($estatus == 1 || $estatus == 2) {
                                if ($fecha == 0000 - 00 - 00) {
                                    $fecha = "";
                                }
                                ?>
                                <td><label for="txtfecha">Fecha:</label><span style="color: red">*</span></td><td><input type="text" id="txtfecha" name="txtfecha" value="<?php echo $fecha; ?>"/></td>
                                <td>Hora</td>
                                <td>
                                    <div id="hora" name="hora">
                                        <select id="hora" name="hora" style="max-width: 250px;">
                                            <?php
                                            echo "<option value='00' >00</option>";
                                            $aux = 0;
                                            for ($i = 1; $i < 24; $i++) {
                                                if ($i < 10) {
                                                    $aux = "0" . $i;
                                                } else {
                                                    $aux = "" . $i;
                                                }
                                                if (strcmp($aux, substr($hora, 0, 2)) == 0) {
                                                    echo "<option value=\"" . $aux . "\" selected=\"selected\" >" . $aux . "</option>";
                                                } else {
                                                    echo "<option value=\"" . $aux . "\" >" . $aux . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                        <select id="minutos" name="minutos" style="max-width: 250px;">
                                            <?php
                                            echo "<option value='00' >00</option>";
                                            $aux = 0;
                                            for ($i = 15; $i < 60; $i = $i + 15) {
                                                $aux = "" . $i;
                                                if (strcmp($aux, substr($hora, 3, 2)) == 0) {
                                                    echo "<option value=\"" . $aux . "\" selected=\"selected\" >" . $aux . "</option>";
                                                } else {
                                                    echo "<option value=\"" . $aux . "\" >" . $aux . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </td> 
                                <td>Tipo de evento:</td>
                                <td><?php
                                    echo "<select id='tipo_evento' name='tipo_evento'>";
                                    $s = "";
                                    if ($tipoe == 0) {
                                        $s = "selected = 'selected'";
                                    }
                                    echo "<option value='0' $s>Seleccione un tipo</option>";
                                    $s = "";
                                    if ($tipoe == 1) {
                                        $s = "selected = 'selected'";
                                    }
                                    echo "<option value='1' $s>Aforo</option>";
                                    $s = "";
                                    if ($tipoe == 2) {
                                        $s = "selected = 'selected'";
                                    }
                                    echo "<option value='2' $s>Desaforo</option>";
                                    ?>  
                                </td>
                                <?php
                            } else {
                                echo "<input type='hidden' id='txtfecha' name='txtfecha' value='0000-00-00'/>";
                                echo "<input type='hidden' id='hora' name='hora' value='00'/>";
                                echo "<input type='hidden' id='minutos' name='minutos' value='00'/>";
                                echo "<input type='hidden' id='tipo_evento' name='tipo_evento' value='0'/> ";
                            }
                            ?>
                            <td>
                                <input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo
                            </td>
                        </tr>
                    </table>
                    <?php if ($agregarUsuarios && $getidactu && $idgetactu != 0 && $estatus == 0) { ?>
                        <fieldset>
                            <legend>Agregar Empleados a Plantilla</legend> 
                            <table>
                                <tr>
                                    <td>Campaña</td>
                                    <td>
                                        <select class="filtro" id="CampaniaFiltro2" name="CampaniaFiltro2" onchange="verUsuario('viajes/lista_autorizar_plantilla.php');">
                                            <option value="0">Seleccione una Opción</option>
                                            <?php
                                            $catalogo = new Catalogo();
                                            $queryCampania = $catalogo->getListaAlta("c_area", "Descripcion");
                                            while ($rs = mysql_fetch_array($queryCampania)) {
                                                $s = "";
                                                if ($filtroCampania2 != "" && $filtroCampania2 == $rs['IdArea']) {
                                                    $s = "selected";
                                                }
                                                if (($rs['ClaveCentroCosto']) != NULL || ($rs['ClaveCentroCosto']) != "") {
                                                    echo "<option value='" . $rs['IdArea'] . "' $s>" . $rs['Descripcion'] . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td>Turno</td>
                                    <td>
                                        <select class="filtro" id="TurnoFiltro2" name="TurnoFiltro2" onchange="verUsuario('viajes/lista_autorizar_plantilla.php');">
                                            <option value="0">Seleccione un Turno</option>
                                            <?php
                                            $catalogo = new Catalogo();
                                            $queryTurno = $catalogo->getListaAlta("c_turno", "descripcion");
                                            while ($rs = mysql_fetch_array($queryTurno)) {
                                                $s = "";
                                                if ($filtroTurno2 != "" && $filtroTurno2 == $rs['idTurno'])
                                                    $s = "selected";
                                                echo "<option value='" . $rs['idTurno'] . "' $s>" . $rs['descripcion'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td>Empleados</td>
                                    <td>
                                        <select id="UsuarioFiltro2" name="UsuarioFiltro2[]" multiple="multiple" class="multiselect">
                                            <?php
                                            //<option value="0">Seleccione un Empleado</option>
                                            $catalogo = new Catalogo();
                                            //$queryU = $catalogo->getListaAlta("c_usuario", "Nombre");
                                            $queryU = $catalogo->obtenerLista("SELECT * FROM c_usuario cu INNER JOIN c_domicilio_usturno cd ON cd.IdUsuario=cu.IdUsuario ".$where2." ORDER BY Nombre;");
                                            while ($rs = mysql_fetch_array($queryU)) {
                                                $s = "";
                                                if ($filtroUsuario2 != "" && $filtroUsuario2 == $rs['IdUsuario']) {
                                                    $s = "selected";
                                                }
                                                if ($rs['IdPuesto'] == 100) {
                                                    echo "<option value='" . $rs['IdUsuario'] . "' $s>" . $rs['Nombre'] . "" . $rs['ApellidoPaterno'] . "" . $rs['ApellidoMaterno'] . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
        <?php echo "<input type='hidden' id='IdPlantillaUsuarios' name='IdPlantillaUsuarios' value='" . $idPlantilla . "'/> "; ?>
                                    </td>
                                    <td>
                                        <input type="button" id="agregarUsuario" name="agregarUsuario" value="Agregar Empleado" class="button" 
                                               onclick="agregarUsuarios('<?php echo $same_page ?>', '<?php echo $idPlantilla ?>');"/>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
        <?php $cabeceras = array("Asistencia", "Nombre", "Domicilio", "Destino", "Comentario", " ", "ss", "ss");
    } ?>
                    <br/>
                    <table id="tAlmacen" class="tabla_datos">
                        <thead>
                            <tr>
                                <?php
                                for ($i = 0; $i < (count($cabeceras) - 2); $i++) {
                                    echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                                }
                                //echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $catalogo = new Catalogo();
                            if (($postid && $idPlantilla != "") || ($getidactu && $idPlantilla != "")) {
                                $query = $catalogo->obtenerLista("SELECT kp.idUsuario AS idUsuario,kpa.idK_Plantilla_asistencia AS idKPA,kpa.IdTicket AS idTicket, kpa.Asistencia AS Asistencia, CONCAT(cu.Nombre,' ',cu.ApellidoPaterno,' ', cu.ApellidoMaterno) AS Nombre, 
                                                            CONCAT(cd.Calle,' #',cd.NoExterior,' ',cd.Colonia,' ',cd.Delegacion,' CP:',cd.CodigoPostal) AS Domicilio, 
                                                            ca.Descripcion AS Destino, kpa.Comentario AS Comentario FROM k_plantilla AS kp LEFT JOIN c_plantilla AS cp ON kp.idPlantilla=cp.idPlantilla JOIN
                                                            k_plantilla_asistencia AS kpa ON kp.idK_Plantilla=kpa.idK_Plantilla JOIN 
                                                            c_domicilio_usturno AS cd ON kp.idUsuario=cd.IdUsuario JOIN 
                                                            c_usuario AS cu ON kp.idUsuario=cu.IdUsuario JOIN c_area AS ca ON ca.IdArea=cp.idCampania JOIN c_turno
                                                            AS ct ON cp.idTurno=ct.idTurno
                                                            WHERE cp.idPlantilla= '" . $idPlantilla . "' 
                                                            ORDER BY cu.Nombre ASC");
                            } else {
                                $query = $catalogo->obtenerLista("SELECT cd.IdUsuario AS idUsuario, CONCAT(cu.Nombre,' ', cu.ApellidoPaterno,' ', cu.ApellidoMaterno) AS Nombre, 
                                                            CONCAT(cd.Calle,' #',cd.NoExterior,' ',cd.Colonia,' ',cd.Delegacion,' CP:',cd.CodigoPostal) AS Domicilio, 
                                                            ca.Descripcion AS Destino  FROM c_domicilio_usturno AS cd
                                                            LEFT JOIN c_usuario AS cu ON cd.idUsuario=cu.IdUsuario JOIN c_area AS ca ON ca.IdArea=cd.IdCampania JOIN c_turno
                                                            AS ct ON cd.IdTurno=ct.idTurno
                                                            $where AND cu.IdPuesto=100
                                                            ORDER BY cu.Nombre ASC");
                            }
                            while ($rs1 = mysql_fetch_array($query)) {
                                if ($rs1['idTicket'] > 0 || $rs1['idTicket'] != "") {
                                    $catalogo2 = new Catalogo();
                                    $result = $catalogo2->obtenerLista("SELECT IdNotaTicket FROM c_notaticket WHERE IdTicket = " . $rs1['idTicket'] . " AND IdEstatusAtencion = 51;");
                                    if (mysql_num_rows($result) > 0) {
                                        $notas++;
                                    }
                                }
                               
                                if ($postid == 1 && $idpost > 0) {
                                    //if($rs1['idTicket']>0 && $rs1['idTicket']!=NULL && $rs1['idTicket']!=""){
                                    //$disabled_asiscom2='disabled';
                                    //if($estatus==2){$disabled_asiscom='disabled';}
                                    //}else {
                                    if ($estatus == 2) {
                                        $disabled_asiscom2 = 'disabled';
                                        $disabled_asiscom = 'disabled';
                                    } else {
                                        $disabled_asiscom2 = "";
                                    }
                                    //}
                                } else {
                                    if ($getidactu == 1 && $idgetactu > 0) {
                                        if ($estatus == 1 || $estatus == 2) {
                                            $disabled_asiscom2 = 'disabled';
                                            $disabled_asiscom = 'disabled';
                                        }
                                    }
                                }



                                echo "<tr>";
                                ?>
                            <td width="2%" align="center" scope="row">
                                <?php
                                if ($postid && $idPlantilla != "" || ($getidactu && $idPlantilla != "")) {
                                    if ($rs1['Asistencia'] == "" || $rs1['Asistencia'] == 0) {
                                        $asiste = "0";
                                    } else {
                                        $asiste = "checked='checked'";
                                    }
                                }
                                ?>

                                <input type="checkbox" name="asistencia[]" id="asistencia[]" <?php echo $asiste; ?> value="<?php echo $rs1['idUsuario']; ?>" <?php echo $disabled_asiscom2; ?>/>
                            </td>


                            <?php
                            echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs1['Nombre'] . "</td>";
                            echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs1['Domicilio'] . "</td>";
                            echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs1['Destino'] . "</td>";
                            if ((isset($_POST['id']) && $_POST['id'] != "") || (isset($_GET['idactu']) && $_GET['idactu'] != "")) {
                                $comentario = $rs1['Comentario'];
                            }
                            ?>
                            <td width="2%" align="center" scope="row"><input type="text" id="comentario[]" name="comentario[]" cols="60" value="<?php echo $comentario; ?>" <?php echo $disabled_asiscom; ?>></td>
                            <?php if ($agregarUsuarios && $getidactu && $idgetactu != 0 && $estatus == 0) { 
                                $autorizarPlantilla = new AutorizarPlantilla();
                                $autorizarPlantilla->setIdPlantilla($idPlantilla);
                                $autorizarPlantilla->setIdUsuario($rs1['idUsuario']);
                                //if ($autorizarPlantilla->UsuarioAgregado()) { ?>
                            <td width="2%" align='center' scope='row'> 
                                   <?php if ($autorizarPlantilla->UsuarioAgregado()) { if ($permisos_grid->getBaja()) { ?>
                                    <a href='#' onclick='eliminarRegistro("<?php echo $controler . "?idelim=" . $rs1['idKPA']; ?>", "<?php echo "viajes/lista_autorizar_plantilla.php?idactu=".$idPlantilla; ?>");
                                                return false;'><img src="resources/images/Erase.png"/></a> 
                                   <?php } } ?>
                            </td>
                                <?php }?>
                            <?php
                            echo "<input type='hidden' id='idUsuario[]' name='idUsuario[]' value='" . $rs1['idUsuario'] . "'/> ";
                            if ($disabled_asiscom2 != "") {
                                if ($asiste != "0") {
                                    echo "<input type='hidden' id='asistencia[]' name='asistencia[]' value='" . $rs1['idUsuario'] . "'/> ";
                                }
                            }
                            if ($disabled_asiscom != "") {

                                echo "<input type='hidden' id='comentario[]' name='comentario[]' value='" . $comentario . "'/> ";
                            }
                            /* if ($rs['Activo'] == 1) {
                              echo "<td align='center' scope='row'>Activo</td>";
                              } else {
                              echo "<td align='center' scope='row'>Inactivo</td>";
                              } */
                            ?>
                            <?php
                            echo "</tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                    <br/><br/>
                    <?php
                    echo "<input type='hidden' id='estatus' name='estatus' value='" . $estatus . "'/> ";
                    if ($disabled_camtur != "") {
                        echo "<input type='hidden' id='CampaniaFiltro' name='CampaniaFiltro' value='" . $filtroCampania . "'/> ";
                        echo "<input type='hidden' id='TurnoFiltro' name='TurnoFiltro' value='" . $filtroTurno . "'/> ";
                    }
                    if ($getidactu && $idgetactu != 0 && $estatus == 0) {
                        $guardar = "Actualizar";
                        echo "<input type='hidden' id='idPlantillaA' name='idPlantillaA' value='" . $idPlantilla . "'/> ";
                    } else {
                        if ($getidactu && $idgetactu != 0 && $estatus == 1) {
                            $guardar = "Autorizar";
                            echo "<input type='hidden' id='idPlantillaA' name='idPlantillaA' value='" . $idPlantilla . "'/> ";
                        } else {
                            if ($getidactu && $idgetactu != 0 && $estatus == 2) {
                                $guardar = "Desautorizar";
                                echo "<input type='hidden' id='idPlantillaA' name='idPlantillaA' value='" . $idPlantilla . "'/> ";
                            } else {
                                if (isset($_POST['id']) && ($_POST['id']) != "") {
                                    $guardar = "Guardar";
                                    echo "<input type='hidden' id='idPlantilla' name='idPlantilla' value='" . $idPlantilla . "'/> ";
                                } else {
                                    $guardar = "Guardar";
                                    echo "<input type='hidden' id='idPlantilla' name='idPlantilla' value='" . $idPlantilla . "'/> ";
                                }
                            }
                        }
                    }

                    if ($notas == 0) {
                        ?>
                        <input type="submit" name="submit" class="boton" value="<?php echo $guardar; ?>" />     <?php } ?>            
                    <input type="submit" class="boton" value="Cancelar" onclick="cancelar();
                                return false;"/>    
    <?php
}
?>
            </form>
        </div>
    </body>
</html>            
